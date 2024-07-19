<?php

namespace Lib\Command;

use Lib\Command\Parameters\ParametersCollection;

readonly class CommandContext
{
    public function __construct(
        public string                $name,
        public ?ParametersCollection $parametersCollection = NULL
    )
    {
    }
}