<?php

namespace Lib\Command\Parameters;

readonly class Parameter implements ParameterInterface
{
    public function __construct(private ParameterDescriptionInterface $description, private mixed $value)
    {
    }

    public function getName(): string
    {
        return $this->description->getName();
    }

    public function getDescription(): string
    {
        return $this->description->getDescription();
    }

    public function getShortName(): ?string
    {
        return $this->description->getShortName();
    }

    public function acceptMultiple(): bool
    {
        return $this->description->acceptMultiple();
    }

    public function getValuePresenceMod(): ValuePresenceMod
    {
        return $this->description->getValuePresenceMod();
    }

    public function getValue(): string|int|bool|float
    {
        return match ($this->getValueType()) {
            ValueType::INT => (int)$this->value,
            ValueType::FLOAT => (float)$this->value,
            ValueType::BOOLEAN => (bool)$this->value,
            ValueType::STRING => "$this->value",
        };
    }

    public function getValueType(): ValueType
    {
        return $this->description->getValueType();
    }
}