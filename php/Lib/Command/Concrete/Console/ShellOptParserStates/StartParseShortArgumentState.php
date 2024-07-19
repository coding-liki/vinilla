<?php

namespace Lib\Command\Concrete\Console\ShellOptParserStates;

use Lib\Command\Concrete\Console\ShellOptParserContext;
use Lib\StateMachine\ContextInterface;
use Lib\StateMachine\StateInterface;

class StartParseShortArgumentState extends AbstractState
{
    public function run(ContextInterface|null|ShellOptParserContext $context): ?string
    {
        parent::run($context);

        $nextArgument = array_shift($context->workingArgv);
        $context->currentArgvPart = \grapheme_substr($nextArgument, 1);

        return ParseShortArgumentNameState::getName();
    }
}