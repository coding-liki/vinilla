<?php

namespace Lib\Command\Parameters;

class ParameterDescription implements ParameterDescriptionInterface
{


    public function __construct(
        private readonly string  $name,
        private readonly string  $description,
        private ?string          $shortName = NULL,
        private bool             $acceptMultiple = false,
        private ValuePresenceMod $valuePresenceMod = ValuePresenceMod::NO_VALUE,
        private ValueType        $valueType = ValueType::STRING,
    )
    {
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }


    public function acceptMultiple(): bool
    {
        return $this->acceptMultiple;
    }

    public function getValuePresenceMod(): ValuePresenceMod
    {
        return $this->valuePresenceMod;
    }

    public function getValueType(): ValueType
    {
        return $this->valueType;
    }

    public function setShortName(?string $shortName): ParameterDescription
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function setAcceptMultiple(bool $acceptMultiple): ParameterDescription
    {
        $this->acceptMultiple = $acceptMultiple;
        return $this;
    }

    public function setValuePresenceMod(ValuePresenceMod $valuePresenceMod): ParameterDescription
    {
        $this->valuePresenceMod = $valuePresenceMod;
        return $this;
    }

    public function setValueType(ValueType $valueType): ParameterDescription
    {
        $this->valueType = $valueType;
        return $this;
    }
}