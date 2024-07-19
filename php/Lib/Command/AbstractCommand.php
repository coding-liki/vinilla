<?php

namespace Lib\Command;

use Lib\Command\Parameters\ParametersCollection;

abstract class AbstractCommand implements CommandInterface
{

    protected ParametersCollection $parameters;

    public function __construct()
    {
        $this->parameters = new ParametersCollection();
    }

    public function setParameters(ParametersCollection $parameters): void
    {
        $this->parameters = $parameters;
    }

    public static function getName(): string
    {
        $classData = explode('\\', static::class);

        return self::camelToKebab(array_pop($classData));
    }

    public static function getHelpDescription(): string
    {
        return static::getName();
    }

    public function getParametersDescription(): array
    {
        return [];
    }

    public function getParameter(string $name, mixed $defaultValue = NULL): mixed
    {
        return $this->parameters->get($name) ?? $defaultValue;
    }

    public function hasParameter(string $name): bool
    {
        return $this->parameters->has($name);
    }

    private static function camelToKebab($camelCase): string
    {
        $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/';
        $snakeCase = preg_replace($pattern, '-', $camelCase);
        return strtolower($snakeCase);
    }
}