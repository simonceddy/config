<?php
require 'vendor/autoload.php';

// Creating MutableConfig
/** @var \Eddy\Config\MutableConfig */
$config = \Eddy\Config\MutableConfig::fromPath(__DIR__ . '/config');

// Set a whole bunch of nested values
$config['parent.nested.again.andAgain.note'] = 'most nested note 1';
$config['parent.nested.again.note'] = 'less nested note';
$config['parent.nested.note'] = 'least nested note';
$config['parent.nested.again.andAgain.note2'] = 'most nested note 2';
$config->overwrite('parent.nested.again', 'time');
// $config['parent'] = 'removed!'; // overwrite parent
$config['here'] = 'there';
dump(isset($config['here']), $config->has('parent'));

// unset($config['parent.nested']);

dd($config);
