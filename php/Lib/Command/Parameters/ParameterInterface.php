<?php

namespace Lib\Command\Parameters;

interface ParameterInterface extends ParameterDescriptionInterface
{
    public function getValue(): mixed;
}