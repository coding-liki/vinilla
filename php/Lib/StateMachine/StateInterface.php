<?php

namespace Lib\StateMachine;

interface StateInterface
{
    public static function getName(): string;

    public function run(?ContextInterface $context): ?string;
}