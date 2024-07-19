<?php

namespace Lib\Command;

use Lib\Command\Parameters\ParameterDescriptionInterface;
use Lib\Command\Parameters\ParametersCollection;

interface CommandInterface
{
    public function setParameters(ParametersCollection $parameters);

    public function run(): ?CommandContext;

    public static function getName(): string;

    public static function getHelpDescription(): string;

    /**
     * @return ParameterDescriptionInterface[]
     */
    public function getParametersDescription(): array;
}