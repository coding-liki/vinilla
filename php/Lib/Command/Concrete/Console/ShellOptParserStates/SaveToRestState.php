<?php

namespace Lib\Command\Concrete\Console\ShellOptParserStates;

use Lib\Command\Concrete\Console\ShellOptParserContext;
use Lib\StateMachine\ContextInterface;
use Lib\StateMachine\StateInterface;

class SaveToRestState extends AbstractState
{
    public function run(ContextInterface|null|ShellOptParserContext $context): ?string
    {
        parent::run($context);

        $nextRestArgument = array_shift($context->workingArgv);

        $context->foundRestArguments[] = $nextRestArgument;

        return CheckArgumentTypeState::getName();
    }
}