<?php

namespace Lib\Command\Concrete\Console\ShellOptParserStates;

use Lib\Command\Concrete\Console\ShellOptParserContext;
use Lib\StateMachine\ContextInterface;
use Lib\StateMachine\StateInterface;

class CheckArgumentTypeState extends AbstractState
{
    public function run(ContextInterface|null|ShellOptParserContext $context): ?string
    {
        parent::run($context);

        $nextStringToCheck = reset($context->workingArgv);
        if ($nextStringToCheck === false) {
            return NULL;
        }

        if (str_starts_with($nextStringToCheck, ShellOptParserContext::LONG_ARGUMENT_PREFIX)) {
            return StartParseLongArgumentState::getName();
        }


        if (str_starts_with($nextStringToCheck, ShellOptParserContext::SHORT_ARGUMENT_PREFIX)) {
            return StartParseShortArgumentState::getName();
        }

        return SaveToRestState::getName();
    }
}