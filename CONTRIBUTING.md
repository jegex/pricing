# Contributing

## Development Setup

```bash
git clone <repo>
cd pricing
composer install
```

## Running Tests

```bash
# All tests
composer test

# With coverage
composer test-coverage
```

## Static Analysis

```bash
composer analyse
```

## Coding Style

```bash
composer lint
```

## Pull Requests

- Write tests for any new functionality
- Ensure PHPStan level 6 passes (`composer analyse`)
- Ensure Pint passes (`composer lint`)
- Update CHANGELOG.md with your changes
