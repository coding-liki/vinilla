<?php

namespace Lib\Command\Concrete\Console;

use Lib\Command\Parameters\ParameterDescription;
use Lib\StateMachine\ContextInterface;

class ShellOptParserContext implements ContextInterface
{

    public const string SHORT_ARGUMENT_PREFIX = '-';
    public const string LONG_ARGUMENT_PREFIX = '--';

    /**
     * @param string[] $workingArgv
     * @param array<string, ParameterDescription> $parameterDescriptionList
     * @param array<string, mixed> $foundArguments
     * @param string[] $foundRestArguments
     */
    public function __construct(
        public array          $workingArgv,
        readonly public array $parameterDescriptionList,
        public array          $foundArguments = [],
        public array          $foundRestArguments = [],
        public string         $currentArgvPart = '',
        public string         $currentArgumentName = '',
    )
    {
    }
}