<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Support;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeFinder;
use ReflectionClass;
use Throwable;

final class AstJsonResourceParser
{
    /**
     * Parse the toArray method of a JsonResource to extract its schema properties.
     *
     * @param string $className
     * @return array<string, mixed>|null Returns the properties array, or null if parsing fails/is empty.
     */
    public function parse(string $className): ?array
    {
        if (!class_exists($className)) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($className);
            if (!$reflection->hasMethod('toArray')) {
                return null;
            }

            $method = $reflection->getMethod('toArray');
            $fileName = $method->getFileName();

            if (!$fileName || !file_exists($fileName)) {
                return null;
            }

            $source = file_get_contents($fileName);

            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $ast = $parser->parse($source);

            $nameResolver = new \PhpParser\NodeVisitor\NameResolver();
            $traverser = new NodeTraverser();
            $traverser->addVisitor($nameResolver);
            $ast = $traverser->traverse($ast);

            $nodeFinder = new NodeFinder();

            // Find the toArray method
            /** @var ClassMethod|null $toArrayMethod */
            $toArrayMethod = $nodeFinder->findFirst($ast, function (Node $node) {
                return $node instanceof ClassMethod && $node->name->toString() === 'toArray';
            });

            if (!$toArrayMethod) {
                return null;
            }

            // Map variables assigned before return
            $variables = [];
            foreach ((array) $toArrayMethod->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Expression && $stmt->expr instanceof Node\Expr\Assign) {
                    $assign = $stmt->expr;
                    if ($assign->var instanceof Node\Expr\Variable && is_string($assign->var->name)) {
                        $variables[$assign->var->name] = $assign->expr;
                    }
                }
            }

            // Find the return statement inside toArray
            /** @var Return_|null $returnStmt */
            $returnStmt = $nodeFinder->findFirst((array) $toArrayMethod->stmts, function (Node $node) {
                return $node instanceof Return_;
            });

            if (!$returnStmt || !$returnStmt->expr instanceof Array_) {
                return null;
            }

            $properties = $this->parseArrayNode($returnStmt->expr, $variables);

            return empty($properties) ? null : $properties;
            
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Determine property type from an ArrayItem value.
     *
     * @param Node\Expr $valueNode
     * @param array<string, Node\Expr> $variables
     * @return array<string, mixed>
     */
    private function determineTypeFromNode(Node\Expr $valueNode, array $variables): array
    {
        // Resolve variable
        if ($valueNode instanceof Node\Expr\Variable && is_string($valueNode->name) && isset($variables[$valueNode->name])) {
            $valueNode = $variables[$valueNode->name];
        }

        // Handle `new XResource(...)`
        if ($valueNode instanceof Node\Expr\New_) {
            $classNode = $valueNode->class;
            if ($classNode instanceof Node\Name) {
                $dependencyClass = $classNode->toString();
                if (class_exists($dependencyClass) && is_subclass_of($dependencyClass, \Illuminate\Http\Resources\Json\JsonResource::class)) {
                    $extractor = new \PhpNl\LaravelApiDoc\Extraction\Extractors\JsonResourceExtractor();
                    $reflection = new \ReflectionMethod($extractor, 'extractSchema');
                    $reflection->setAccessible(true);
                    $schema = $reflection->invoke($extractor, $dependencyClass);

                    return [
                        'type' => 'object',
                        'resource' => $dependencyClass,
                        'properties' => $schema['properties'] ?? null,
                    ];
                }
            }
        }

        if ($valueNode instanceof StaticCall) {
            $classNode = $valueNode->class;
            $methodName = $valueNode->name instanceof Node\Identifier ? $valueNode->name->toString() : '';

            if ($classNode instanceof Node\Name && in_array($methodName, ['make', 'collection'])) {
                $dependencyClass = $classNode->toString();

                // It could be self or static
                if (in_array(strtolower($dependencyClass), ['self', 'static'])) {
                    return [
                        'type' => $methodName === 'collection' ? 'array' : 'object',
                        'description' => 'Recursive relationship',
                    ];
                }

                // If it's a valid class string, we trigger schema extraction for it
                if (class_exists($dependencyClass) && is_subclass_of($dependencyClass, \Illuminate\Http\Resources\Json\JsonResource::class)) {
                    
                    // We extract it globally so it's registered
                    $extractor = new \PhpNl\LaravelApiDoc\Extraction\Extractors\JsonResourceExtractor();
                    $reflection = new \ReflectionMethod($extractor, 'extractSchema');
                    $reflection->setAccessible(true);
                    $schema = $reflection->invoke($extractor, $dependencyClass);

                    if ($methodName === 'collection') {
                        return [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'resource' => $dependencyClass,
                                'properties' => $schema['properties'] ?? null,
                            ]
                        ];
                    }

                    return [
                        'type' => 'object',
                        'resource' => $dependencyClass,
                        'properties' => $schema['properties'] ?? null,
                    ];
                }
            }
        }

        if ($valueNode instanceof Node\Scalar\String_) {
            return ['type' => 'string', 'example' => $valueNode->value];
        }

        if ($valueNode instanceof Node\Scalar\LNumber || $valueNode instanceof Node\Scalar\DNumber) {
            return ['type' => 'number', 'example' => $valueNode->value];
        }

        if ($valueNode instanceof Node\Expr\ConstFetch) {
            $name = strtolower($valueNode->name->toString());
            if (in_array($name, ['true', 'false'])) {
                return ['type' => 'boolean', 'example' => $name === 'true'];
            }
            if ($name === 'null') {
                return ['type' => 'string', 'example' => null];
            }
        }

        if ($valueNode instanceof Array_) {
            $props = $this->parseArrayNode($valueNode, $variables);
            if (empty($props)) {
                return ['type' => 'array', 'items' => ['type' => 'string']];
            }
            // Check if it's a list or associative array
            $isList = true;
            foreach ($valueNode->items as $idx => $item) {
                if ($item->key !== null) {
                    $isList = false;
                    break;
                }
            }

            if ($isList) {
                // If it's a list, the items type is the type of the first element
                $firstProp = reset($props);
                return [
                    'type' => 'array',
                    'items' => $firstProp ?: ['type' => 'string']
                ];
            }

            return [
                'type' => 'object',
                'properties' => $props,
            ];
        }

        // Default fallback for dynamic variables, property fetches, method calls, etc.
        return ['type' => 'string'];
    }

    /**
     * Recursively parse an Array_ node into schema properties.
     *
     * @param Array_ $arrayNode
     * @param array<string, Node\Expr> $variables
     * @return array<string, mixed>
     */
    private function parseArrayNode(Array_ $arrayNode, array $variables): array
    {
        $properties = [];

        foreach ($arrayNode->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }

            // Key
            $key = null;
            if ($item->key instanceof Node\Scalar\String_) {
                $key = $item->key->value;
            }

            // Value
            $typeSchema = $this->determineTypeFromNode($item->value, $variables);

            if ($key !== null) {
                $properties[$key] = $typeSchema;
            } else {
                // List array item
                $properties[] = $typeSchema;
            }
        }

        return $properties;
    }
}
