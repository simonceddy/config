<?php
namespace Eddy\Config;

use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    public const UNSUPPORTED_FILETYPE = 'UNSUPPORTED_FILETYPE';

    private bool $yamlSupport = false;

    public function __construct()
    {
        !class_exists(Yaml::class) ?: $this->yamlSupport = true;
    }

    public function load(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(
                'Could not locate ' . $path
            );
        }

        return new Config($this->loadValues($path));
    }
    
    protected function pathToKey(string $path)
    {
        $hasExt = strrpos($path, ".");

        if (is_int($hasExt)) {
            $path = substr($path, 0, $hasExt);
        }

        return basename($path);
    }

    protected function loadFile(string $path)
    {
        $ext = substr(strrchr($path, "."), 1);
        // dd($ext);
        if ($ext === 'yml' || $ext === 'yaml' && $this->yamlSupport) {
            return Yaml::parseFile($path);
        }

        switch ($ext) {
            case 'php':
                return SafeInclude::file($path);
            case 'json':
                return json_decode(file_get_contents($path), true);
            // case 'yaml':
            // case 'yml':
            //     return Yaml::parseFile($path);
            default:
                return static::UNSUPPORTED_FILETYPE;
        }
    }

    protected function loadDir(string $path)
    {
        $files = scandir($path);

        $values = [];

        foreach ($files as $name) {
            if ($name !== '.' && $name !== '..') {
                $result = is_dir(
                    $full = $path . DIRECTORY_SEPARATOR . $name
                ) ? $this->loadDir($full) : $this->loadFile($full);
                // load file
                if ($result !== static::UNSUPPORTED_FILETYPE) {
                    $values[$this->pathToKey($name)] = $result;
                }
            }
        }

        return $values;
    }

    protected function loadPath(string $path)
    {
        if (is_dir($path)) {
            return $this->loadDir($path);
        } elseif (file_exists($path)) {
            return $this->loadFile($path);
        }
        return null;
    }

    protected function loadValues($paths = [])
    {
        $values = [];

        if (is_string($paths)) {
            $values = $this->loadPath($paths);
        } elseif (is_array($paths)) {
            foreach ($paths as $path) {
                $values = array_merge_recursive($values, $this->loadPath($path));
            }
        }

        return $values;
    }
}
