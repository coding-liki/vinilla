<?php

namespace Lib\Command\Concrete\Console\ShellOptParserStates;

use Lib\Command\Concrete\Console\ShellOptParserContext;
use Lib\StateMachine\ContextInterface;
use Lib\StateMachine\StateInterface;

class ParseShortArgumentNameState extends AbstractState
{
    public function run(ContextInterface|null|ShellOptParserContext $context): ?string
    {
        parent::run($context);

        $equalPosition = grapheme_stripos($context->currentArgvPart, '=');

        if ($equalPosition === false) {
            $name = $context->currentArgvPart;
            $context->currentArgvPart = '';
        } else {
            $name = \grapheme_substr($context->currentArgvPart, 0, $equalPosition);
            $context->currentArgvPart = \grapheme_substr($context->currentArgvPart,  $equalPosition);
        }


        if (!isset($context->parameterDescriptionList[$name])) {
            throw new \Exception("Parameter '$name' does not have a description");
        }

        $context->currentArgumentName = $name;
        $context->foundArguments[$name] = NULL;

        return ParseArgumentValueState::getName();
    }
}