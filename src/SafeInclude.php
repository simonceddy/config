<?php
namespace Eddy\Config;

final class SafeInclude
{
    public static function file(string $path)
    {
        return noScopeSafeInclude($path);
    }
}

function noScopeSafeInclude(string $path) {
    return include $path;
}
