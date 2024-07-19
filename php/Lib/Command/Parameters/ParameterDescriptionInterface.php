<?php

namespace Lib\Command\Parameters;

interface ParameterDescriptionInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getShortName(): ?string;

    public function acceptMultiple(): bool;

    public function getValuePresenceMod(): ValuePresenceMod;
    public function getValueType(): ValueType;
}