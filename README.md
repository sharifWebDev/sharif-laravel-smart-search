````markdown
<p align="center">
<img src="https://via.placeholder.com/1500x500/3B82F6/FFFFFF?text=Laravel+Smart+Search" alt="Laravel Smart Search" width="100%">
</p>

<p align="center">
<a href="https://packagist.org/packages/sharifWebDev/laravel-smart-search"><img src="https://img.shields.io/packagist/v/sharifWebDev/laravel-smart-search" alt="Latest Version"></a>
<a href="https://packagist.org/packages/sharifWebDev/laravel-smart-search"><img src="https://img.shields.io/packagist/dt/sharifWebDev/laravel-smart-search" alt="Total Downloads"></a>
<a href="https://github.com/sharifWebDev/laravel-smart-search/actions"><img src="https://img.shields.io/github/actions/workflow/status/sharifWebDev/laravel-smart-search/tests.yml" alt="Build Status"></a>
<a href="https://packagist.org/packages/sharifWebDev/laravel-smart-search"><img src="https://img.shields.io/packagist/l/sharifWebDev/laravel-smart-search" alt="License"></a>
<a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4" alt="PHP Version"></a>
<a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10.x%2B-FF2D20" alt="Laravel Version"></a>
</p>

# 🚀 Laravel Smart Search

Laravel Smart Search is a **high-performance, intelligent search package** for Laravel Eloquent models. It provides automatic relation discovery, configurable search depth, multiple search modes, and optimized queries for large datasets.

---

## ✨ Key Features

- 🔍 **Smart Relation Discovery** – Automatically searches through model relationships.
- ⚡ **High Performance** – Optimized queries with configurable limits.
- 🎯 **Multiple Search Modes** – `like`, `exact`, `starts_with`, `ends_with`.
- 🔧 **Fully Configurable** – Customize search behavior and priorities.
- 📚 **Priority Columns** – Define columns to prioritize for better results.
- 🛡️ **Type Safe** – Full type hints, PHPStan ready.
- 🧪 **Fully Tested** – Comprehensive test coverage included.
- 🔌 **Laravel Native** – Seamless integration with Eloquent models.

---

## 📦 Installation

### Requirements

- PHP 8.1+
- Laravel 10.x or 11.x

### Via Composer

```bash
composer require sharif/laravel-smart-search
```
````

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Sharif\\LaravelSmartSearch\\SmartSearchServiceProvider" --tag="smart-search-config"
```

---

## 🎯 Quick Start

### 1️⃣ Use the Trait

Add the `SmartSearch` trait to your Eloquent models:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sharif\LaravelSmartSearch\Traits\SmartSearch;

class Product extends Model
{
    use SmartSearch;
}
```

### 2️⃣ Basic Search Usage

```php
// Simple search
$products = Product::applySmartSearch('laptop')->get();

// Paginated search
$products = Product::applySmartSearch($request->input('search'))->paginate(15);

// Search with relations
$products = Product::applySmartSearch('apple')->with('brand', 'categories')->get();
```

---

## 🔧 Configuration

After publishing, edit `config/smart-search.php` to customize behavior:

```php
return [
    'defaults' => [
        'mode' => 'like',
        'deep' => true,
        'max_relation_depth' => 2,
        'search_operator' => 'or',
    ],

    'columns' => [
        'excluded' => [
            'id', 'created_at', 'updated_at', 'deleted_at',
            'password', 'remember_token', 'email_verified_at'
        ],
        'prioritized' => ['name', 'title', 'code', 'email'],
        'max_per_table' => 10,
    ],

    'relations' => [
        'auto_discover' => true,
        'max_depth' => 2,
        'excluded' => ['password', 'secret', 'tokens'],
    ],

    'performance' => [
        'max_join_tables' => 5,
        'chunk_search' => false,
        'chunk_size' => 1000,
    ],
];
```

---

## 💡 Usage Examples

### Basic Search

```php
$users = User::applySmartSearch('john')->get();
$products = Product::applySmartSearch('wireless keyboard')->paginate(20);
```

### Advanced Search with Options

```php
$results = Product::applySmartSearch(
    search: 'laptop',
    columns: ['name', 'sku', 'description'],
    options: [
        'mode' => 'like',
        'deep' => true,
        'max_relation_depth' => 1
    ]
)->get();
```

### Relation Search

```php
$posts = Post::applySmartSearch('laravel tips')->with('author', 'tags', 'comments')->get();
```

### Multiple Search Modes

```php
// Like (default)
$results = Product::applySmartSearch('phone');

// Exact match
$results = User::applySmartSearch('admin@example.com', [], ['mode' => 'exact']);

// Starts with
$results = Product::applySmartSearch('APP', [], ['mode' => 'starts_with']);

// Ends with
$results = Product::applySmartSearch('001', [], ['mode' => 'ends_with']);
```

---

## ⚡ Performance Optimization

- Add **indexes** for frequently searched columns.
- Limit `max_relation_depth` for large datasets.
- Disable `deep` option for simple queries.
- Enable `chunk_search` in config for large tables.

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

---

## 🤝 Contributing

1. Fork the repository.
2. Clone your fork: `git clone https://github.com/your-username/laravel-smart-search`.
3. Install dependencies: `composer install`.
4. Run tests: `composer test`.

See [CONTRIBUTING.md](CONTRIBUTING.md) for more details.

---

## 📄 License

MIT License. See [LICENSE.md](LICENSE.md).

---

<p align="center">
<strong>Laravel Smart Search</strong> - Intelligent search for intelligent applications. 🚀
</p>

<p align="center">
<a href="https://github.com/sharifWebDev/sharif-laravel-smart-search">GitHub</a> •
<a href="https://packagist.org/packages/sharifWebDev/laravel-smart-search">Packagist</a> •
<a href="https://github.com/sharifWebDev/laravel-smart-search/issues">Issues</a> •
<a href="https://github.com/sharifWebDev/laravel-smart-search/discussions">Discussions</a>
</p>
```

---

### ✅ Improvements Made

1. **Removed duplicate keywords and unnecessary sections**.
2. **Fixed broken Markdown**: code blocks, headings, and alignment.
3. **Structured for professional readability**:

   - Introduction → Installation → Usage → Configuration → Performance → Testing → Contributing → License.

4. **Shortened long repeated examples** for clarity.
5. Added proper badges and removed placeholder/misplaced sections.
6. Optional sections like **Roadmap/Benchmarks** were removed for simplicity—you can add them in a separate doc if needed.

---

If you want, I can **also create a ready-to-use `CONTRIBUTING.md` and `CHANGELOG.md`** so the package looks professional and is Packagist-ready.

Do you want me to do that next?
