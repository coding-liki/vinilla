<?php

namespace Lib\Command\Parameters;

class ParametersCollection
{
    /**
     * @param array<string, ParameterInterface|ParameterInterface[]> $parametersList
     */
    public function __construct(private array $parametersList = [])
    {
    }

    public function get(string $name): mixed
    {
        $parameter = $this->parametersList[$name] ?? NULL;

        if (is_array($parameter)) {
            return array_map(fn(ParameterInterface $parameterData) => $parameterData->getValue(), $parameter);
        }

        return $parameter?->getValue() ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->parametersList);
    }

    public function addParameter(ParameterInterface $parameter): static
    {
        $this->parametersList[$parameter->getName()] ??= $parameter->acceptMultiple() ? [] : $parameter;

        if ($parameter->acceptMultiple()) {
            $this->parametersList[$parameter->getName()][] = $parameter;
        }

        return $this;
    }
}