<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Routing\Route;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use PhpNl\LaravelApiDoc\Data\Response;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\Node;
use ReflectionMethod;

final readonly class AstControllerExtractor implements Extractor
{
    /**
     * @param Route $route
     * @param Endpoint $endpoint
     * @return void
     */
    public function extract(Route $route, Endpoint $endpoint): void
    {
        $action = $route->getAction();

        if (!isset($action['controller']) || !is_string($action['controller'])) {
            return;
        }

        if (str_contains($action['controller'], '@')) {
            [$controller, $method] = explode('@', $action['controller']);
        } else {
            $controller = $action['controller'];
            $method = '__invoke';
        }

        if (!method_exists($controller, $method)) {
            return;
        }

        try {
            $reflection = new ReflectionMethod($controller, $method);
            $fileName = $reflection->getFileName();

            if (!$fileName || !file_exists($fileName)) {
                return;
            }

            $code = file_get_contents($fileName);
            if ($code === false) {
                return;
            }

            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $ast = $parser->parse($code);

            if (!$ast) {
                return;
            }

            $traverser = new \PhpParser\NodeTraverser();
            $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
            $ast = $traverser->traverse($ast);

            // Find the class method node
            $nodeFinder = new NodeFinder();
            /** @var Node\Stmt\ClassMethod|null $methodNode */
            $methodNode = $nodeFinder->findFirst($ast, function (Node $node) use ($method) {
                return $node instanceof Node\Stmt\ClassMethod && $node->name->toString() === $method;
            });

            if (!$methodNode instanceof Node\Stmt\ClassMethod) {
                return;
            }

            $this->extractInlineValidation($methodNode, $endpoint);
            $this->extractAbortCalls($methodNode, $endpoint);
            $this->extractJsonResources($methodNode, $endpoint);

        } catch (\Throwable) {
            // Ignore parse errors silently to not interrupt extraction
        }
    }

    /**
     * @param Node\Stmt\ClassMethod $methodNode
     * @param Endpoint $endpoint
     * @return void
     */
    private function extractInlineValidation(Node\Stmt\ClassMethod $methodNode, Endpoint $endpoint): void
    {
        $nodeFinder = new NodeFinder();
        
        /** @var Node\Expr\MethodCall[] $methodCalls */
        $methodCalls = $nodeFinder->findInstanceOf($methodNode, Node\Expr\MethodCall::class);

        foreach ($methodCalls as $call) {
            if ($call->name instanceof Node\Identifier && $call->name->toString() === 'validate') {
                if (count($call->getArgs()) > 0) {
                    $arg = $call->getArgs()[0]->value;
                    if ($arg instanceof Node\Expr\Array_) {
                        $this->parseValidationArray($arg, $endpoint);
                    }
                }
            }
        }
    }

    /**
     * @param Node\Expr\Array_ $arrayNode
     * @param Endpoint $endpoint
     * @return void
     */
    private function parseValidationArray(Node\Expr\Array_ $arrayNode, Endpoint $endpoint): void
    {
        if (!is_array($arrayNode->items)) {
            return;
        }

        foreach ($arrayNode->items as $item) {
            if (!$item instanceof Node\Expr\ArrayItem || !$item->key instanceof Node\Scalar\String_) {
                continue;
            }

            $paramName = $item->key->value;
            $rules = [];
            $enumValues = null;

            if ($item->value instanceof Node\Scalar\String_) {
                $rules = explode('|', $item->value->value);
            } elseif ($item->value instanceof Node\Expr\Array_ && is_array($item->value->items)) {
                foreach ($item->value->items as $ruleItem) {
                    if ($ruleItem && $ruleItem->value instanceof Node\Scalar\String_) {
                        $rules[] = $ruleItem->value->value;
                    }
                }
            }

            foreach ($rules as $idx => $r) {
                if (str_starts_with($r, 'enum:')) {
                    $enumClass = substr($r, 5);
                    if (function_exists('enum_exists') && enum_exists($enumClass)) {
                        $enumValues = array_map(fn($case) => $case->value ?? $case->name, $enumClass::cases());
                        $rules[$idx] = 'enum:' . implode(',', $enumValues);
                        
                        \PhpNl\LaravelApiDoc\Extraction\SchemaRegistry::register(class_basename($enumClass), [
                            'type' => 'string',
                            'enum' => $enumValues,
                        ]);
                    }
                } elseif (str_starts_with($r, 'in:')) {
                    $enumValues = explode(',', substr($r, 3));
                }
            }

            if (!empty($rules)) {
                $exists = false;
                foreach ($endpoint->parameters as $p) {
                    if ($p->name === $paramName) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $isRequired = in_array('required', $rules, true);
                    $endpoint->addParameter(new Parameter(
                        name: $paramName,
                        type: $this->mapRuleToType($rules),
                        required: $isRequired,
                        description: 'Validation rules: ' . implode('|', $rules),
                        in: in_array('GET', $endpoint->methods, true) ? 'query' : 'body',
                        rules: $rules,
                        enumValues: $enumValues
                    ));
                }
            }
        }
    }

    /**
     * @param array<int, string> $rules
     * @return string
     */
    private function mapRuleToType(array $rules): string
    {
        if (in_array('numeric', $rules, true) || in_array('integer', $rules, true)) {
            return 'integer';
        }
        if (in_array('boolean', $rules, true)) {
            return 'boolean';
        }
        if (in_array('array', $rules, true)) {
            return 'array';
        }
        if (in_array('file', $rules, true) || in_array('image', $rules, true)) {
            return 'file';
        }
        return 'string';
    }

    /**
     * @param Node\Stmt\ClassMethod $methodNode
     * @param Endpoint $endpoint
     * @return void
     */
    private function extractAbortCalls(Node\Stmt\ClassMethod $methodNode, Endpoint $endpoint): void
    {
        $nodeFinder = new NodeFinder();
        
        /** @var Node\Expr\FuncCall[] $funcCalls */
        $funcCalls = $nodeFinder->findInstanceOf($methodNode, Node\Expr\FuncCall::class);

        foreach ($funcCalls as $call) {
            if ($call->name instanceof Node\Name && ltrim($call->name->toString(), '\\') === 'abort') {
                if (count($call->getArgs()) > 0) {
                    $codeNode = $call->getArgs()[0]->value;
                    if ($codeNode instanceof Node\Scalar\Int_) {
                        $status = $codeNode->value;
                        
                        $description = match ($status) {
                            401 => 'Unauthorized',
                            403 => 'Forbidden',
                            404 => 'Not Found',
                            422 => 'Unprocessable Entity',
                            default => 'Error response',
                        };

                        if (count($call->getArgs()) > 1) {
                            $descNode = $call->getArgs()[1]->value;
                            if ($descNode instanceof Node\Scalar\String_) {
                                $description = $descNode->value;
                            }
                        }

                        $endpoint->addResponse(new Response(
                            status: $status,
                            description: $description,
                        ));
                    }
                }
            }
        }
    }

    /**
     * @param Node\Stmt\ClassMethod $methodNode
     * @param Endpoint $endpoint
     * @return void
     */
    private function extractJsonResources(Node\Stmt\ClassMethod $methodNode, Endpoint $endpoint): void
    {
        $nodeFinder = new NodeFinder();

        /** @var Node\Expr\StaticCall[] $staticCalls */
        $staticCalls = $nodeFinder->findInstanceOf($methodNode, Node\Expr\StaticCall::class);

        foreach ($staticCalls as $call) {
            if ($call->class instanceof Node\Name && $call->name instanceof Node\Identifier) {
                $methodName = $call->name->toString();
                if (in_array($methodName, ['make', 'collection'], true)) {
                    $className = $call->class->toString();
                    
                    if ($className === 'self' || $className === 'static') {
                        continue;
                    }

                    // To avoid dependency loops, we can use the same extraction logic from JsonResourceExtractor
                    $extractor = new JsonResourceExtractor();
                    
                    if (!str_contains($className, '\\')) {
                        // Let's assume it was successfully resolved by the NameResolver if it had a use statement.
                        // If it's a built-in class or an unresolved relative class in a global namespace, fallback.
                        $className = "App\\Http\\Resources\\" . $className;
                    }
                    
                    if (!class_exists($className)) {
                        // Check if we can find it in another way or just skip
                        continue;
                    }
                    
                    if (is_subclass_of($className, \Illuminate\Http\Resources\Json\JsonResource::class)) {
                        $reflection = new ReflectionMethod($extractor, 'extractSchema');
                        $reflection->setAccessible(true);
                        $schema = $reflection->invoke($extractor, $className);
                        
                        if ($methodName === 'collection') {
                            $schema = [
                                'type' => 'object',
                                'properties' => [
                                    'data' => [
                                        'type' => 'array',
                                        'items' => $schema,
                                    ]
                                ]
                            ];
                        }
                        
                        $endpoint->addResponse(new Response(
                            status: 200,
                            description: "Successful response returning {$className}",
                            schema: $schema
                        ));
                    }
                }
            }
        }
        
        // Also check for 'new Resource()'
        /** @var Node\Expr\New_[] $newCalls */
        $newCalls = $nodeFinder->findInstanceOf($methodNode, Node\Expr\New_::class);
        foreach ($newCalls as $call) {
            if ($call->class instanceof Node\Name) {
                $className = $call->class->toString();
                
                if (!str_contains($className, '\\')) {
                    $className = "App\\Http\\Resources\\" . $className;
                }
                
                if (!class_exists($className)) {
                    continue;
                }
                
                if (is_subclass_of($className, \Illuminate\Http\Resources\Json\JsonResource::class)) {
                    $extractor = new JsonResourceExtractor();
                    $reflection = new ReflectionMethod($extractor, 'extractSchema');
                    $reflection->setAccessible(true);
                    $schema = $reflection->invoke($extractor, $className);
                    
                    $endpoint->addResponse(new Response(
                        status: 200,
                        description: "Successful response returning {$className}",
                        schema: $schema
                    ));
                }
            }
        }
    }
}
