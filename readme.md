# Config

Basic read-only config class with file loader.

## Installation

This library can be installed with composer:

```sh
composer require simoneddy/config
```

## Dependencies

- PHP >=8.0
- [symfony/yaml](https://symfony.com/doc/current/components/yaml.html) (optional for yaml support)

## Usage

The Config class contains a static factory `fromPath` method that accepts a path to a config file or directory:

```php
require 'vendor/autoload.php';

$config = \Eddy\Config\Config::fromPath(__DIR__ . '/config');
```

Internally, this factory method uses the ConfigLoader class to scan the provided path and load all [supported filetypes](#supported-filetypes).

Unsupported files will be ignored.

If the path is a directory the ConfigLoader will attempt to load values from every supported file the directory contains. This also applies to any subdirectories and their contents.

Files are loaded as key-value pairs, using the base filename (minus extension) as the key, while the file contents is the value.

For example:

```php
// config directory contains the file 'test.php' which returns an array:
// ['isTest' => true]
$config = \Eddy\Config\Config::fromPath(__DIR__ . '/config');

// values can be retrieved with dot notation, where the filename is the parent
// key:
var_dump($config['test.isTrue']);
```

__PLEASE NOTE__ YAML files will be ignored unless the [symfony/yaml](https://symfony.com/doc/current/components/yaml.html) package is also installed.

You can also use the Config class constructor and provide your config values directly as the sole argument:

```php
$config = new \Eddy\Config\Config([
    'someKey' => 'some value',
    'someMoreKeys' => [
        'anotherKey' => 'another value'
    ]
]);
```

Once the config object is created it can be accessed like an array, with dot notation to specify nested keys:

```php
echo $config['someKey']; // 'some value'

// Using dot notation to access nested keys:
var_dump(isset($config['someMoreKeys.anotherKey'])); // true
```

Internally, array access methods use `get($key)` and `has($key)` methods. These methods can also be used directly:

```php
echo $config->get('someKey'); // 'some value'

var_dump($config->has('someMoreKeys.anotherKey')); // true
```

__PLEASE NOTE__ that the Config object is read only. Values cannot be added after the object is instantiated. The ArrayAccess methods `offsetSet` and `offsetUnset` are empty and do nothing:

```php
$config['settingKey'] = 'setting value';

// will be false, as offsetSet is an empty method
var_dump($config->has('settingKey'));

unset($config['someKey']);

// will remain true, as offsetUnset is also empty
var_dump($config->has('someKey'));
```

The config object contains a `toArray` method that returns all config values as an array. The returned array maintains config keys and structure.

## Seriali(s/z)ing

The Config class implements both PHPs Serializable and JsonSerializable interfaces. This is to simplify any caching process that serializes an object, or to assist combining a large config directory into a single file (for whatever reason).

__PLEASE NOTE__ when using serializing, it is recommended that any PHP config files __do not__ contain functions, as this can cause issues.

## Supported Filetypes

The ConfigLoader supports the following filetypes:

- PHP
- JSON
- YAML (requires symfony/yaml as a peer dependency)

The given path can also be a directory containing supported files and subdirectories.
