<?php

declare(strict_types=1);

namespace Hyde;

use Hyde\Foundation\HydeKernel;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

use function Safe\file_get_contents;

class HydeHelper
{
    /**
     * Ask for a CLI input value until we pass validation rules
     *
     * @param Command $command
     * @param string $name
     * @param string $message
     * @param array $rules
     * @param array $rules
     * @return mixed $default
     */
    public static function askWithValidation(Command $command, string $name, string $message, Collection|array $rules = [], mixed $default = null)
    {
        if ($rules instanceof Collection) {
            $rules = $rules->toArray();
        }

        $answer    = $command->ask($message, $default);
        $factory   = app(ValidationFactory::class);
        $validator = $factory->make([$name => $answer], [$name => $rules]);

        if ($validator->passes()) {
            return $answer;
        }

        foreach ($validator->errors()->all() as $error) {
            $command->error($error);
        }

        return self::askWithValidation($command, $name, $message, $rules);
    }

    /**
     * Get the available HydeKernel instance.
     *
     * @return \Hyde\Foundation\HydeKernel
     */
    public static function getKernel(): HydeKernel
    {
        return app(HydeKernel::class);
    }

    /**
     * Format the publication type name to a suitable representation for file storage
     *
     * @param string $pubTypeNameRaw
     * @return string
     */
    public static function formatNameForStorage(string $pubTypeNameRaw)
    {
        return Str::camel($pubTypeNameRaw);
    }

    /**
     * Return a collection of all defined publication types, indexed by the
     * @return Collection
     * @throws \Exception
     */
    public static function getPublicationTypes(): Collection
    {
        $root        = base_path();
        $pubTypes    = Collection::create();
        $schemaFiles = glob("$root/*/schema.json", GLOB_BRACE);

        foreach ($schemaFiles as $schemaFile) {
            $fileData = file_get_contents($schemaFile);
            if (!$fileData) {
                throw new \Exception("No data read from [$schemaFile]");
            }

            $schema                         = Collection::create(json_decode($fileData, true));
            $schema->directory              = dirname($schemaFile);
            $schema->schemaFile             = $schemaFile;
            $pubTypes->{$schema->directory} = $schema;
        }

        return $pubTypes;
    }

    /**
     * @param string $pubTypeName
     * @param bool $isRaw
     * @return bool
     * @throws \Exception
     */
    public static function publicationTypeExists(string $pubTypeName, bool $isRaw = true): bool
    {
        if ($isRaw) {
            $pubTypeName = self::formatNameForStorage($pubTypeName);
        }

        return self::getPublicationTypes()->has($pubTypeName);
    }

    /**
     * Remove trailing slashes from the start and end of a string.
     *
     * @param string $string
     * @return string
     */
    public static function unslash(string $string): string
    {
        return trim($string, '/\\');
    }
}
