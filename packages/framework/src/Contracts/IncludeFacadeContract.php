<?php

namespace Hyde\Framework\Contracts;

interface IncludeFacadeContract
{
    public static function path(?string $filename = null): string;

    public static function get(string $filename, ?string $default = null): ?string;

    public static function markdown(string $filename, ?string $default = null): ?string;

    public static function blade(string $filename, ?string $default = null): ?string;
}
