# PHP CS Fixer Constructor Multiline Rule

A PHP CS Fixer rule that automatically enforces multiline constructor arguments for better code readability and consistency across your PHP projects.

## 🎯 The Problem

Long constructor arguments on a single line are hard to read and maintain:

```php
// ❌ Hard to read
public function __construct(private FlatplanTypeRepository $flatplanTypeRepository, private ConcessionRepository $concessionRepository, private CracFilesystem $cracFilesystem) {
    parent::__construct();
}
```

## ✨ The Solution

This rule automatically breaks constructor arguments into readable multiline format:

```php
// ✅ Much more readable
public function __construct(
    private FlatplanTypeRepository $flatplanTypeRepository,
    private ConcessionRepository $concessionRepository,
    private CracFilesystem $cracFilesystem
) {
    parent::__construct();
}
```

## 🚀 Features

- **Automatic detection** of constructors with multiple arguments
- **Smart formatting** with proper indentation
- **PSR-12 compatible** formatting
- **Zero configuration** required
- **Works with PHP 8.0+** constructor property promotion
- **Integrates seamlessly** with existing PHP CS Fixer workflows

## 📦 Installation

```bash
composer require --dev your-username/php-cs-fixer-constructor-multiline
```

## 👉 Usage

Add to your `.php-cs-fixer.php`:

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'YourNamespace/constructor_multiline' => true,
    ])
    ->setFinder($finder);
```

## 🎉 Why This Exists

The PHP ecosystem lacks a simple solution for this common formatting need. While PHP CS Fixer has excellent formatting rules, it only enforces multiline formatting if the code is already broken across lines. This rule fills that gap by automatically detecting and formatting long constructor arguments.

Perfect for teams that want consistent, readable constructor formatting without manual intervention.

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
