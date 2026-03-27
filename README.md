<p align="center">
    <h1 align="center">Laravel API Doc</h1>
    <p align="center">
        A highly configurable, beautiful, and interactive API documentation generator for Laravel.
    </p>
    <p align="center">
        <a href="https://packagist.org/packages/php-nl/laravel-api-doc"><img src="https://img.shields.io/packagist/v/php-nl/laravel-api-doc.svg?style=flat-square" alt="Latest Version on Packagist"></a>
        <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/php-nl/laravel-api-doc.svg?style=flat-square" alt="PHP from Packagist"></a>
        <a href="https://packagist.org/packages/php-nl/laravel-api-doc"><img src="https://img.shields.io/packagist/dt/php-nl/laravel-api-doc.svg?style=flat-square" alt="Total Downloads"></a>
        <a href="https://packagist.org/packages/php-nl/laravel-api-doc"><img src="https://img.shields.io/packagist/l/php-nl/laravel-api-doc.svg?style=flat-square" alt="License"></a>
    </p>
</p>

---

**Laravel API Doc** is an elegant, zero-configuration API documentation package designed to give your Laravel projects a beautiful, interactive dashboard out of the box. 

Inspired by Laravel's clean DX and the capabilities of packages like Scramble and Scribe, it automatically parses your routes, form requests, models, and JSON resources to generate a live, testable API portal exactly when you need it—without requiring you to write endless generic PHPDoc blocks.

## ✨ Features

- **Zero Config Required:** Just install and visit `/docs/api`!
- **Beautiful UI:** A premium, fully responsive 3-column layout built with Livewire and Tailwind CSS.
- **AST Parsing Engine:** Accurately extracts complex validation rules even when your `FormRequest` relies on unresolved route models or unauthenticated users.
- **Smart Model Introspection:** Seamlessly infers parameter types via Eloquent Model casts and database schema. 
- **Automatic JSON Resources:** Safely extracts `JsonResource` and `ResourceCollection` schemas, including **automatic Laravel Pagination wrapping** (detects `.paginate()` and adds `links` and `meta` schemas).
- **Interactive "Try It Out" Panel:** Test any endpoint securely from your browser.
- **Advanced Route Filtering:** Exclude routes via URL patterns, route names, middleware, or directly via PHP Attributes.
- **Code Snippets:** Real-time multi-language snippets (cURL, JavaScript, PHP, Python) for your endpoints.
- **OpenAPI Export:** Export your exact API schema compliant with the OpenAPI 3.1.0 specification.
- **Custom Markdown Pages:** Render extra `.md` documentation files directly in the frontend sidebar.

---

## 📦 Installation

Requires **PHP 8.3+** and **Laravel 11+**.

You can install the package via Composer:

```bash
composer require php-nl/laravel-api-doc
```

Next, publish the configuration file and assets using:

```bash
php artisan vendor:publish --tag="laravel-api-doc-config"
php artisan vendor:publish --tag="laravel-api-doc-assets"
```

## 🚀 Usage

Once installed, simply navigate to the predefined documentation route in your browser:

```text
http://your-app.test/docs/api
```

### Defining Endpoints

The package leverages Laravel's native routing metadata. Standard PHPDoc blocks, `FormRequest` validations, and parameter `Model` bindings are **automatically extracted** to document your API. 

If your controller method returns a `JsonResource` or invokes a `FormRequest`, the package uses **AST Parsing** and **Reflection** to read the structure of your application without actually executing destructive code.

### Customizing Endpoints via Attributes

Sometimes, you want to explicitly document query parameters or body payloads that aren't tied to a `FormRequest`, or you want to group your routes logically. Laravel API Doc provides custom PHP attributes for the ultimate developer experience (DX).

#### Grouping & Naming
Organize your routes in the sidebar using the `#[ApiDoc]` attribute:

```php
use PhpNl\LaravelApiDoc\Attributes\ApiDoc;

#[ApiDoc(group: 'Product Management', name: 'Update Product', description: 'Modify an existing product.')]
public function update(Request $request, Product $product)
{
    // ...
}
```

#### Manual Parameters
If you pull data directly from the Request (e.g., `request('status')`) instead of using a FormRequest, you can document it seamlessly using parameter attributes:

```php
use PhpNl\LaravelApiDoc\Attributes\QueryParam;
use PhpNl\LaravelApiDoc\Attributes\BodyParam;

#[QueryParam('sort', type: 'string', description: 'Field to sort by', enumValues: ['asc', 'desc'])]
#[BodyParam('notify_user', type: 'boolean', required: false, description: 'Send an email notification')]
public function index()
{
    // ...
}
```

#### Unauthenticated Routes
By default, the UI assumes your endpoints are secure (requiring Bearer tokens if `auth` middleware is detected). To mark a route as public (like login or register), use the `#[Unauthenticated]` attribute:

```php
use PhpNl\LaravelApiDoc\Attributes\Unauthenticated;

#[Unauthenticated]
public function login(Request $request)
{
    // ...
}
```

---

## 🚫 Excluding Routes

You might not want your internal or horizon routes showing up in the documentation. Laravel API Doc provides **4 elegant ways** to filter out routes.

### 1. Via Configuration Patterns (URL, Name, Middleware)
Open your `config/laravel-api-doc.php` and define exclusion rules:

```php
'routes' => [
    'include' => ['api/*'],
    
    // Exclude by URL wildcards
    'exclude' => [
        'api/internal/*',
        'api/webhooks/*',
    ],
    
    // Exclude by Route Name
    'exclude_names' => [
        'admin.*',
        'horizon.*',
    ],
    
    // Exclude by assigned Middleware
    'exclude_middleware' => [
        'auth:admin',
    ],
],
```

### 2. Via PHP Attributes
For the ultimate localized DX, you can ignore a specific Controller or Method directly in the code:

```php
use PhpNl\LaravelApiDoc\Attributes\ExcludeFromDocs;

#[ExcludeFromDocs]
class SecretController extends Controller
{
    // ...
}
```

---

## ⚙️ Configuration

You can fully customize the behavior and look of your documentation by modifying `config/laravel-api-doc.php`.

```php
return [
    'ui' => [
        'title' => 'My API Documentation',
        'theme' => [
            'primary_color' => '#3b82f6', // Customize your brand color!
            'background_color' => '#f8fafc',
            'sidebar_width' => '300px',
        ],
        'docs_path' => resource_path('docs/api'), // Where your custom .md files live
    ],
    // ...
];
```

### Global Authentication
Use the **Security & Authentication** panel (found on the dashboard's welcome screen) to securely define your tokens locally. These tokens are placed in your browser's local storage and injected directly into the "Try It Out" playground.

### Custom Markdown Pages
You can write standard `.md` Markdown files (e.g., `Getting-Started.md`, `Authentication.md`) and place them in the `resources/docs/api` directory (configurable via `docs_path`). They will be parsed dynamically and explicitly shown in your API sidebar navigation.

---

## 🛠 Artisan Commands

For production environments or advanced CI/CD pipelines, Laravel API Doc provides several helpful commands:

#### Caching Documentation
Parsing ASTs and reflecting over hundreds of controllers can be heavy. In production, you should cache the schema:

```bash
php artisan api-doc:cache
```
To clear the cache:
```bash
php artisan api-doc:clear
```

#### JSON Response Generation
Want to show real JSON responses instead of just schema types? Run the response generator command. It will execute safe GET endpoints locally to capture **live representations** of your API returns.

```bash
php artisan api-doc:generate-responses
```

#### OpenAPI Export
Export your documentation to an OpenAPI 3.1.0 compatible `openapi.json` file. You can view your schema live at `/docs/api/openapi.json` or generate it via CLI:

```bash
php artisan api-doc:openapi > openapi.json
```

---

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
