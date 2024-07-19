<?php

namespace Lib\Command\Concrete\Console\ShellOptParserStates;

use Lib\Command\Concrete\Console\ShellOptParserContext;
use Lib\Command\Parameters\ParameterDescription;
use Lib\Command\Parameters\ValuePresenceMod;
use Lib\StateMachine\ContextInterface;

class ParseArgumentValueState extends AbstractState
{
    public function run(ContextInterface|null|ShellOptParserContext $context): ?string
    {
        parent::run($context);

        $description = $context->parameterDescriptionList[$context->currentArgumentName] ?? throw new \Exception("No description");

        return match ($description->getValuePresenceMod()) {
            ValuePresenceMod::NO_VALUE => empty($context->currentArgvPart)
                ? CheckArgumentTypeState::getName()
                : throw new \Exception("Argument '{$context->currentArgumentName}' does not accept value"),
            ValuePresenceMod::OPTIONAL => empty($context->currentArgvPart) ? CheckArgumentTypeState::getName() : $this->parseAndSaveValue($context, $description),
            ValuePresenceMod::REQUIRED => $this->parseAndSaveValue($context, $description),
            ValuePresenceMod::FROM_REST => throw new \Exception("Argument {$context->currentArgumentName} need to be in rest"),
        };
    }

    private function parseAndSaveValue(ShellOptParserContext $context, ParameterDescription $description): string
    {
        $context->currentArgvPart = grapheme_substr($context->currentArgvPart, 1) ?: '';

        if (grapheme_substr($context->currentArgvPart, 0, 1) === '=') {
            $context->currentArgvPart = grapheme_substr($context->currentArgvPart, 1) ?: '';
        }
        if (!empty($context->currentArgvPart)) {
            $value = $context->currentArgvPart;
        } else {
            $value = array_shift($context->workingArgv) ?? '';
            if (grapheme_substr($value, 0, 1) === '=') {
                $value = grapheme_substr($value, 1);
                if (empty($value)) {
                    $value = array_shift($context->workingArgv) ?? '';
                }
            }
        }
        $context->currentArgvPart = '';

        if ($description->acceptMultiple()) {
            $context->foundArguments[$context->currentArgumentName] ??= [];
            $context->foundArguments[$context->currentArgumentName][] = $value;
        } else {
            $context->foundArguments[$context->currentArgumentName] = $value;
        }

        return CheckArgumentTypeState::getName();
    }
}