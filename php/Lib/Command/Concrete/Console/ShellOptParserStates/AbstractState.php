<?php

namespace Lib\Command\Concrete\Console\ShellOptParserStates;

use Lib\Command\Concrete\Console\ShellOptParserContext;
use Lib\StateMachine\ContextInterface;
use Lib\StateMachine\StateInterface;

abstract class AbstractState implements StateInterface
{
    public static function getName(): string
    {
        return static::class;
    }


    /**
     * @param ContextInterface|null|ShellOptParserContext $context
     * @return string|null
     * @throws \Exception
     */
    public function run(ContextInterface|null|ShellOptParserContext $context): ?string
    {
        if (!$context instanceof ShellOptParserContext) {
            throw new \Exception("Invalid context");
        }

        return null;
    }
}