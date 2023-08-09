# Config

Basic Config class with file loader.

## Changes in 2.0.0

- The ConfigLoader class has been renamed PathLoader
- Using the PathLoader->load method no longer returns a Config object. Instead it now returns an array of values loaded from the filesystem.
- Construction of Config objects removed from PathLoader.
- Added a ConfigFactory class to handle construction when using static factory methods.
- Added a MutableConfig class that extends Config with methods for setting and unsetting values.

## Changes in 1.1

- Can now load CSV files

## Installation

This library can be installed with composer:

```sh
composer require simoneddy/config
```

## Dependencies

- PHP >=8.0
- [symfony/yaml](https://symfony.com/doc/current/components/yaml.html) (optional for yaml support)

## Usage

A Config object can be created by either passing values to the class constructor or by using one of the various factory methods:

```php
require 'vendor/autoload.php';

// Using the Config constructor. This method requires that all config values be supplied as the
// constructors $values argument
$config = new \Eddy\Config\Config($values = []);

// Using the MutableConfig constructor. This method allows config values be supplied as the
// constructors $values argument, but can be modified after construction.
$config = new \Eddy\Config\MutableConfig($values = []);

// Using the Config objects static factory method
$config = \Eddy\Config\Config::fromPath(__DIR__ . '/config');

// Using the MutableConfig objects static factory method
$config = \Eddy\Config\MutableConfig::fromPath(__DIR__ . '/config');

// Using the ConfigFactory directly
$config = \Eddy\Config\ConfigFactory::fromPath(__DIR__ . '/config', $isMutable = false);

// Using a new ConfigFactory to create an immutable Config object
$config = (new \Eddy\Config\ConfigFactory())->createImmutable(__DIR__ . '/config');

// Using a new ConfigFactory to create a mutable MutableConfig object
$config = (new \Eddy\Config\ConfigFactory())->createMutable(__DIR__ . '/config');
```

Internally, this factory method uses the PathLoader class to scan the provided path and load all [supported filetypes](#supported-filetypes) into an array. This array is given to the ConfigFactory which constructs the neccessary config class.

Unsupported files will be ignored.

If the path is a directory the PathLoader will attempt to load values from every supported file the directory contains. This also applies to any subdirectories and their contents.

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

The `Eddy\Config\Config` object is read only and values cannot be modified after the object is instantiated. For mutability use the `Eddy\Confi\MutableConfig` class, which adds set and remove methods, as well as properly implementing `\ArrayAccess::offsetUnset` and `\ArrayAccess::offsetSet`:

```php
// MutableConfig can be modified after construction
$config = new \Eddy\Config\MutableConfig([
    'someKey' => 'some value',
    'someMoreKeys' => [
        'anotherKey' => 'another value'
    ]
]);

// Now offsetSet works
$config['anotherKey'] = 'another value';

// Which is the same as using the set method:
$config->set('andAnother', 'yet another value');
var_drump(isset($config['anotherKey'])); // true

// OffsetUnset works too!
unset($config['someKey']);

// Which is using the remove method:
$config->remove('andAnother');
var_dump($config->has('someKey')); // false
var_dump($config->has('andAnother')); // false
```

Of course, all setting and unsetting can utilise dot notation for nested values:

```php
$config->set('parent.nested.nestedTwice.nestedThrice', 'nested value');

// returns an array: ['nested' => ['nestedTwice' => ['nestedThrice' => 'nested value']]]
var_dump($config->get('parent'));

unset($config['parent.nested.nestedTwice']);

// returns an array: ['nested' => []]
var_dump($config->get('parent'));
```

By design, the set method (and subsequently offsetSet) will merge nested key => value pairs where neccessary. If you want to completely overwrite the parent key you can use the overwrite method:

```php
$config->set('parent.nested.nestedTwice', 'nested value');

// returns an array: ['nested' => ['nestedTwice' => 'nested value']]
var_dump($config->get('parent'));

$config->overwrite('parent.newThing', 'new value');

// returns ['newThing' => 'new value']
// 'nestedTwice' is no longer set as 'parent' has been overwritten
var_dump($config['parent']);
```

The config object contains a `toArray` method that returns all config values as an array. The returned array maintains config keys and structure.

## Seriali(s/z)ing

The Config class implements both PHPs JsonSerializable interfaces and serializing magic methods to PHP 8 standards. No deprecation warnings here!

## Supported Filetypes

The ConfigLoader supports the following filetypes:

- PHP
- JSON
- CSV (parses as an array with the filename as key)
- YAML (requires symfony/yaml as a peer dependency)

The given path can also be a directory containing supported files and subdirectories. The directory structure will be used to determine nesting.
