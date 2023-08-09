<?php
namespace Eddy\Config;

class Config implements \ArrayAccess, \JsonSerializable
{
    public function __construct(protected ? array $values = [])
    {}

    protected function resolveRecursively(string $key, array $values)
    {
        $bits = explode('.', $key);

        foreach ($bits as $bit) {
            $values = $this->resolveFrom($bit, $values);

            if (!is_array($values)) {
                return $values;
            }
        }

        return $values;
    }

    protected function resolveFrom(
        string $key,
        array $values = []
    ) {
        !empty($values) ?: $values = &$this->values;

        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        if (strpos($key, '.') !== false) {
            return $this->resolveRecursively($key, $values);
        }

        return null;
    }

    /**
     * Get the value for the given key from config
     *
     * @param string|null $key The key to retrieve the value for. Can use dot notation to access
     * nested keys e.g. get('my.nested.key')
     *
     * @return mixed
     */
    public function get(string $key = null): mixed
    {
        if ($key === null) {
            return $this->values;
        }
        // resolve nested keys
        return $this->resolveFrom($key);
    }

    /**
     * Check if the given key exists in config
     *
     * @param string $key The key to check. Can use dot notation to check nested keys e.g.
     * has('my.nested.key')
     *
     * @return bool Note: currently returns false if a key is set but has a null value. I will
     * hopefully change this soon.
     */
    public function has(string $key): bool
    {
        // TODO check keys properly
        return $this->resolveFrom($key) !== null;
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }
    
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        // does nothing
    }

    public function offsetUnset($offset): void
    {
        // does nothing
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize($serialized): void
    {
        $this->values = unserialize($serialized);
    }

    /**
     * Get all config as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->values;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Static factory method for creating a Config object using the contents of a file or directory.
     *
     * @param string $path The path to the config file or directory.
     * @param bool $mutable Default to false for immutable config objects. If true it will return a
     * MutableConfig object.
     *
     * @return Config|MutableConfig
     */
    public static function fromPath(string $path, bool $mutable = false)
    {
        return ConfigFactory::fromPath($path, $mutable);
    }
}
