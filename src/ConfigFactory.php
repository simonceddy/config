<?php
namespace Eddy\Config;

class ConfigFactory
{
    public function create(array $values = [], bool $mutable = false)
    {
        return !$mutable ? new Config($values) : new MutableConfig($values);
    }

    public function createMutable(array $values)
    {
        return $this->create($values, true);
    }

    public function createImmutable(array $values)
    {
        return $this->create($values, false);
    }

    public static function fromPath(string $path, bool $mutable = false)
    {
        return (new self())->create((new PathLoader())->load($path), $mutable);
    } 
}
