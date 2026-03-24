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

It automatically parses your routes, form requests, and parameters to generate a live, testable documentation portal where you and your team can try out endpoints seamlessly.

## ✨ Features

- **Beautiful UI:** A premium, fully responsive 3-column layout built with Livewire and Tailwind CSS.
- **Interactive "Try It Out" Panel:** Test any endpoint directly from your browser.
- **Global Authentication:** Configure Bearer Tokens, Basic Auth, or API Keys directly from the dashboard to authenticate your test requests.
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

You will be greeted by the API Documentation Dashboard. From here you can:
1. Browse through your API routes logically grouped by domain.
2. View the description, parameters, and required payloads for each route.
3. Use the **Security & Authentication** configuration to authenticate globally.
4. Execute real-time requests against your application.

### Defining Endpoints

The package leverages Laravel's native routing metadata and reflection to parse documentation. Standard PHPDoc blocks, FormRequest validations, and route groupings are automatically extracted to document your API.

### Global Authentication

When interacting with private APIs, you don't need to manually enter tokens for every request. 
On the Welcome Screen of the documentation dashboard, use the **Security & Authentication** panel to define yours:
- Bearer Token
- Basic Auth
- API Key (Header or Query Parameter)

Once set, this authentication state is persisted for your session and can be toggled per-endpoint when running test queries.

## ⚙️ Configuration

You can fully customize the look and feel, available themes, and base extraction rules by modifying the published configuration file at `config/laravel-api-doc.php`.

```php
return [
    'ui' => [
        'title' => 'My API Documentation',
        'theme' => [
            'primary_color' => '#3b82f6', // Customize your brand color!
            'background_color' => '#f8fafc',
        ],
    ],
    // ...
];
```

## 🧪 Testing

```bash
composer test
```

## 🛠 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

If you discover any security-related issues, please email directly instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
