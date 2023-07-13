<?php
namespace Eddy\Config;

class Config implements \ArrayAccess, \Serializable, \JsonSerializable
{
    public function __construct(private ? array $values = [])
    {}

    private function resolveRecursively(string $key, array $values)
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

    private function resolveFrom(
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

    public function get(string $key = null)
    {
        if ($key === null) {
            return $this->values;
        }
        // resolve nested keys
        return $this->resolveFrom($key);
    }

    public function has(string $key)
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

    public function serialize(): string
    {
        return serialize($this->values);
    }

    public function unserialize($serialized): mixed
    {
        $this->values = unserialize($serialized);
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public static function fromPath(string $path): Config
    {
        return (new ConfigLoader())->load($path);
    }
}
