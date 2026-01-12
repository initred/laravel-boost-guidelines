# Laravel Boost Guidelines

[![Tests](https://img.shields.io/github/actions/workflow/status/initred/laravel-boost-guidelines/tests.yml?label=tests)](https://github.com/initred/laravel-boost-guidelines/actions/workflows/tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/initred/laravel-boost-guidelines)](https://packagist.org/packages/initred/laravel-boost-guidelines)
[![Total Downloads](https://img.shields.io/packagist/dt/initred/laravel-boost-guidelines)](https://packagist.org/packages/initred/laravel-boost-guidelines)
[![License](https://img.shields.io/packagist/l/initred/laravel-boost-guidelines)](https://packagist.org/packages/initred/laravel-boost-guidelines)

AI guidelines extension for Laravel Boost - Provides Inertia React, shadcn/ui, and Tailwind CSS v4 guidelines.

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
            "@php artisan boost:guidelines --all --force",
            "@php artisan boost:update --ansi"
        ]
    }
}
```

## Available Guidelines

### inertia-react/2/forms

Inertia v2 form handling guidelines (upgrade from v1's `router.post` pattern):

- **`<Form>` Component** (v2.1+): Declarative form handling with built-in state management
- **`useForm` Hook** (v2.0.x): For projects not yet on v2.1
- **shadcn/ui Integration**: Field components with proper error states (`data-invalid`, `aria-invalid`)
- **shadcn/ui Button**: Icon styling best practices (no unnecessary `mr-*` or `size-*` classes)
- **Wayfinder Support**: Type-safe form actions with `.form()` method

### tailwindcss/4/core

Tailwind CSS v4 migration guide:

- **CSS-first Config**: Use `@theme` directive instead of `tailwind.config.js`
- **New Import**: `@import "tailwindcss"` replaces `@tailwind` directives
- **Deprecated Utilities**: `bg-opacity-*` → `bg-black/*`, `flex-shrink-*` → `shrink-*`, etc.
- **Size Utility**: Use `size-*` instead of `w-* h-*` for equal dimensions

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
- Laravel Boost

## License

MIT License. See [LICENSE](LICENSE) for details.
