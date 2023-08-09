<?php
namespace Eddy\Config;

class MutableConfig extends Config
{
    private function isArrayWithKey($value, string $key): bool
    {
        return array_key_exists($key, $value) && is_array($value[$key]);
    }

    protected function setNestedValue(string $key, $value, bool $overwrite = false)
    {
        $bits = explode('.', $key);

        if (count($bits) === 1) {
            $this->values[$key] = $value;
        } else {
            $last = array_pop($bits);
            $first = array_shift($bits);

            $endResult = [$last => $value];
            $bits = array_reverse($bits);

    
            foreach ($bits as $bit) {
                $endResult = [$bit => $endResult];
            }

            if (array_key_exists($first, $this->values)
                && is_array($this->values[$first])
                && !$overwrite
            ) {
                $endResult = array_merge_recursive($this->values[$first], $endResult);
            }

            $this->values[$first] = $endResult;
                // dd($endResult);
        }

    }

    protected function recursivelyUnset(string $key)
    {
        $bits = explode('.', $key);
        
        if (count($bits) === 1) {
            unset($this->values[$key]);
        } else {
            $last = array_pop($bits);
            $parentKey = implode('.', $bits);
            $parent = $this->get($parentKey);
            if ($parent && $parent[$last]) {
                unset($parent[$last]);
                $this->setNestedValue($parentKey, $parent, true);
            }
        }
    }

    /**
     * Sets a key => value pair. Keys can use dot notation to nest items. Nested values will be
     * merged with the existing structure. To overwrite a key instead of merging, use the
     * overwrite method.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value = null)
    {
        $this->setNestedValue($key, $value);
    }

    /**
     * Sets a key => value pair, overwriting already set keys instead of merging. Keys can use dot
     * notation to specify nesting.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function overwrite(string $key, $value = null)
    {
        $this->setNestedValue($key, $value, true);
    }

    /**
     * Remove the given key and its value from config. Keys can use dot notation to unset nested
     * keys.
     *
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key)
    {
        $this->recursivelyUnset($key);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    public static function fromPath(string $path, bool $mutable = false)
    {
        return parent::fromPath($path, true);
    }
}
