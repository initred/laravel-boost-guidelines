# Contributing

Thank you for considering contributing to Laravel Boost Guidelines!

## Development Setup

1. Fork and clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Run tests:
   ```bash
   composer test
   ```

## Pull Request Process

1. Create a feature branch from `main`
2. Make your changes
3. Add tests for new functionality
4. Ensure all tests pass: `composer test`
5. Run static analysis: `composer analyse`
6. Update documentation if needed
7. Submit a pull request

## Coding Standards

- Follow PSR-12 coding standards
- Use strict types in all PHP files
- Write meaningful commit messages
- Add PHPDoc blocks for public methods

## Adding New Guidelines

1. Create a new `.blade.php` file in `.ai/guidelines/`
2. Follow the directory structure: `category/version/name.blade.php`
3. If the guideline has dependencies, add them to `$requirements` in `InstallGuidelinesCommand.php`
4. Add a test to verify the guideline exists

## Reporting Issues

- Use GitHub Issues for bug reports and feature requests
- Include steps to reproduce for bugs
- Provide context for feature requests

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
