# Laravel Boost Guidelines

[![Tests](https://img.shields.io/github/actions/workflow/status/initred/laravel-boost-guidelines/tests.yml?label=tests)](https://github.com/initred/laravel-boost-guidelines/actions/workflows/tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/initred/laravel-boost-guidelines)](https://packagist.org/packages/initred/laravel-boost-guidelines)
[![Total Downloads](https://img.shields.io/packagist/dt/initred/laravel-boost-guidelines)](https://packagist.org/packages/initred/laravel-boost-guidelines)
[![License](https://img.shields.io/github/license/initred/laravel-boost-guidelines)](https://github.com/initred/laravel-boost-guidelines/blob/main/LICENSE)

> AI-powered coding guidelines for Laravel + Inertia React + Tailwind CSS v4 + shadcn/ui

Install best-practice guidelines for modern Laravel stack via a simple Artisan command. Works seamlessly with AI coding assistants like Claude, Cursor, and GitHub Copilot.

## Features

- **Inertia React v2 Forms** - Modern `<Form>` component and `useForm` hook patterns
- **Tailwind CSS v4 Migration** - CSS-first config, new utilities, deprecated class replacements
- **shadcn/ui Integration** - Proper error states, accessible form fields, button icon styling
- **Wayfinder Support** - Type-safe form actions with `.form()` method
- **Interactive CLI** - Select specific guidelines or install all at once

## Quick Start

```bash
composer require initred/laravel-boost-guidelines --dev

php artisan boost:guidelines --all
```

## Installation

### From Packagist (Recommended)

```bash
composer require initred/laravel-boost-guidelines --dev
```

The package will be auto-discovered by Laravel.

### For Development

If you want to contribute or test local changes, add the package as a path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-boost-guidelines"
        }
    ]
}
```

Then install:

```bash
composer require initred/laravel-boost-guidelines:@dev --dev
```

After making changes to the package, update it:

```bash
composer update initred/laravel-boost-guidelines
```

## Usage

### Install Guidelines

```bash
# Interactive mode - select which guidelines to install
php artisan boost:guidelines

# Install all guidelines
php artisan boost:guidelines --all

# Install specific category only
php artisan boost:guidelines --tailwindcss
php artisan boost:guidelines --inertia-react

# Install multiple categories
php artisan boost:guidelines --tailwindcss --inertia-react

# Force overwrite existing files
php artisan boost:guidelines --force

# Skip boost:update prompt after installation
php artisan boost:guidelines --all --no-update

# List available guidelines
php artisan boost:guidelines --list
```

### After Installation

Run Laravel Boost's update command to apply the guidelines:

```bash
php artisan boost:update
```

### Automatic Installation (Optional)

Add to your project's `composer.json` scripts to automatically install guidelines on `composer update`:

```json
{
    "scripts": {
        "post-update-cmd": [
            "@php artisan boost:guidelines --all --force --no-update",
            "@php artisan boost:update --ansi"
        ]
    }
}
```

## Available Guidelines

### inertia-react/2/forms

Inertia v2 form handling guidelines (upgrade from v1's `router.post` pattern):

| Feature | Description |
|---------|-------------|
| `<Form>` Component | Declarative form handling with built-in state management (v2.1+) |
| `useForm` Hook | For projects not yet on v2.1 |
| shadcn/ui Integration | Field components with proper error states (`data-invalid`, `aria-invalid`) |
| shadcn/ui Button | Icon styling best practices (no unnecessary `mr-*` or `size-*` classes) |
| Wayfinder Support | Type-safe form actions with `.form()` method |
| React 19 & Compiler | `useRef` argument requirements, ref callback syntax, auto-memoization |

### wayfinder/core

Laravel Wayfinder integration for type-safe routing:

| Feature | Description |
|---------|-------------|
| Named Imports | Tree-shakable controller method imports |
| Route Objects | Functions return `{ url, method }` shaped objects |
| Form Support | `.form()` method for HTML form attributes |
| Query Parameters | `query` and `mergeQuery` options for URL params |
| Inertia Integration | Works with `<Form>` component and `useForm` hook |

### tailwindcss/4/core

Tailwind CSS v4 migration guide:

| Before (v3) | After (v4) |
|-------------|------------|
| `tailwind.config.js` | `@theme` directive in CSS |
| `@tailwind base/components/utilities` | `@import "tailwindcss"` |
| `bg-opacity-50` | `bg-black/50` |
| `flex-shrink-0` | `shrink-0` |
| `w-4 h-4` | `size-4` |

## Running Tests

```bash
git clone https://github.com/initred/laravel-boost-guidelines.git
cd laravel-boost-guidelines
composer install
composer test
```

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x
- [Laravel Boost](https://github.com/nicepkg/laravel-boost)

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

MIT License. See [LICENSE](LICENSE) for details.
