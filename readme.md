# Config

Basic read-only config class with file loader.

## Installation

This library can be installed with composer:

```sh
composer require simoneddy/config
```

## Dependencies

- PHP >=8.0
- symfony/yaml (optional for yaml support)

## Usage

The Config class contains a static factory method that accepts a path to config.

```php
require 'vendor/autoload.php';

$config = \Eddy\Config\Config::fromPath(__DIR__ . '/config');
```

Internally, this factory method uses the ConfigLoader class to scan the provided path and load all [supported filetypes](#supported-filetypes).

Unsupported files will be ignored.

You can also use the Config class constructor and provide your config values as the argument:

```php
$config = new \Eddy\Config\Config([
    'someKey' => 'someValue'
]);
```

## Supported Filetypes

- PHP
- JSON
- YAML (requires symfony/yaml as a peer dependency)
