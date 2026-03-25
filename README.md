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

**Laravel API Doc** is an elegant, zero-configuration API documentation package designed to give your Laravel projects a beautiful, Stoplight Elements-inspired interactive dashboard out of the box. 

It automatically parses your routes, form requests, and models to generate a live, testable documentation portal exactly when you need it.

## ✨ Features

- **Beautiful UI:** A premium, fully responsive 3-column layout built with Livewire and Tailwind CSS.
- **Interactive "Try It Out" Panel:** Test any endpoint directly from your browser.
- **Code Snippets:** Real-time multi-language snippets (cURL, JavaScript, PHP, Python) for your endpoints.
- **Smart Model Introspection:** Seamlessly infers parameter types via Model casts and DB schema, requiring no docblocks!
- **Global Authentication:** Configure Bearer Tokens, Basic Auth, or API Keys directly from the dashboard.
- **OpenAPI Export:** Export your exact API schema compliant with OpenAPI 3.1.0 specification.
- **API Versioning:** Support version toggles across your endpoints out of the box.
- **Custom Markdown Pages:** Render extra `.md` documentation files directly in the frontend sidebar.
- **Real Responses Simulation:** Safely generate real-world JSON response payloads automatically.
- **Webhooks Documentation:** First-class support for declaring outgoing Webhook payloads.
- **Auto-Discovery:** Automatically detects endpoints, methods, and route groups.
- **Zero Config Required:** Just install and visit `/docs/api`!

## 📦 Installation

You can install the package via composer:

```bash
composer require php-nl/laravel-api-doc
```

Next, you can publish the configuration file and assets using:

```bash
php artisan vendor:publish --provider="PhpNl\LaravelApiDoc\LaravelApiDocServiceProvider"
```

## 🚀 Usage

Once installed, simply navigate to the predefined documentation route in your browser:

```text
http://your-app.test/docs/api
```

### Defining Endpoints

The package leverages Laravel's native routing metadata. Standard PHPDoc blocks, `FormRequest` validations, and parameter `Model` bindings are automatically extracted to document your API—no extra packages or plugins needed!

### Global Authentication

When interacting with private APIs, you don't need to manually enter tokens for every request. 
Use the **Security & Authentication** panel (found on the dashboard's welcome screen) to securely define your tokens locally.

### OpenAPI Export

You can export your documentation to an OpenAPI 3.1.0 compatible `openapi.json` file.
You can view your schema live at `/docs/api/openapi.json` or generate it via CLI:

```bash
php artisan api-doc:openapi
```

### Generating Real Responses

Want to show real JSON responses instead of just schema types? Run the response generator command. It will execute safe GET endpoints to capture live representations of your API returns.

```bash
php artisan api-doc:generate-responses
```

### Custom Markdown Pages

You can write standard `.md` Markdown files (e.g. `Getting-Started.md`, `Authentication.md`) and place them in the `resources/docs/api` directory (configurable via `docs_path`). They will be parsed dynamically and explicitly shown in your API sidebar navigation.

## ⚙️ Configuration

You can fully customize the behavior and look of your documentation by modifying the published configuration file at `config/laravel-api-doc.php`.

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
    'versions' => [
        'enabled' => true,
        'default' => 'v1',
        'list' => [
            'v1' => 'Version 1',
            'v2' => 'Version 2',
        ],
    ],
    'webhooks' => [
        // 'order.created' => \App\Http\Resources\OrderResource::class,
    ],
    // ...
];
```

## 🛠 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
