<?php

namespace Lib\Command\Concrete\Console;

use Lib\Command\Concrete\Console\ShellOptParserStates\CheckArgumentTypeState;
use Lib\Command\Concrete\Console\ShellOptParserStates\InitState;
use Lib\Command\Concrete\Console\ShellOptParserStates\ParseLongArgumentNameState;
use Lib\Command\Concrete\Console\ShellOptParserStates\ParseArgumentValueState;
use Lib\Command\Concrete\Console\ShellOptParserStates\ParseShortArgumentNameState;
use Lib\Command\Concrete\Console\ShellOptParserStates\StartParseLongArgumentState;
use Lib\Command\Concrete\Console\ShellOptParserStates\StartParseShortArgumentState;
use Lib\Command\Concrete\Console\ShellOptParserStates\SaveToRestState;
use Lib\Command\Parameters\Parameter;
use Lib\Command\Parameters\ParameterDescription;
use Lib\Command\Parameters\ParameterDescriptionInterface;
use Lib\Command\Parameters\ParametersCollection;
use Lib\Command\Parameters\ParametersExtractorInterface;
use Lib\Command\Parameters\ValuePresenceMod;
use Lib\StateMachine\StateMachine;

class ParametersExtractor implements ParametersExtractorInterface
{
    /** @var array<string, ParameterDescriptionInterface> */
    private array $nameToDescriptionMap = [];
    /** @var ParameterDescriptionInterface[] */
    private array $fromRestDescriptionList = [];

    /**
     * @inheritdoc
     */
    public function extract(array $parametersDescriptionList): ParametersCollection
    {
        global $argv;
        foreach ($parametersDescriptionList as $parametersDescription) {
            $this->nameToDescriptionMap[$parametersDescription->getName()] = $parametersDescription;

            if ($parametersDescription->getShortName()) {
                $this->nameToDescriptionMap[$parametersDescription->getShortName()] = $parametersDescription;
            }
            if ($parametersDescription->getValuePresenceMod() === ValuePresenceMod::FROM_REST) {
                $this->fromRestDescriptionList[] = $parametersDescription;
            }
        }

        $parserContext = new ShellOptParserContext(
            array_slice($argv, 2),
            $this->indexParametersDescriptionList($parametersDescriptionList),
        );

        (new StateMachine([
            new InitState(),
            new CheckArgumentTypeState(),
            new StartParseLongArgumentState(),
            new StartParseShortArgumentState(),
            new SaveToRestState(),
            new ParseLongArgumentNameState(),
            new ParseArgumentValueState(),
            new ParseShortArgumentNameState(),
        ]))->runFromState(InitState::getName(), $parserContext);

        $optionsFromShell = $parserContext->foundArguments;
        $restArguments = $parserContext->foundRestArguments;

        $parametersCollection = new ParametersCollection();

        if (count($restArguments) < count($this->fromRestDescriptionList)) {
            throw new \Exception("Not enough arguments!");
        }

        $this->processOptionsFromShell($optionsFromShell, $parametersCollection);

        $this->processOptionsFromRest($restArguments, $parametersCollection);

        return $parametersCollection;
    }


    public function processOptionsFromShell(array|false $optionsFromShell, ParametersCollection $parametersCollection): void
    {
        foreach ($optionsFromShell as $name => $optionValue) {
            $description = $this->nameToDescriptionMap[$name];

            if (is_array($optionValue)) {
                if (!$description->acceptMultiple()) {
                    throw new \Exception("Argument '$name' does not accept multiple values!");
                }
                foreach ($optionValue as $value) {
                    $parametersCollection->addParameter(new Parameter($description, $value));
                }
            } else {
                $parametersCollection->addParameter(new Parameter($description, $optionValue));
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function processOptionsFromRest(array $restArguments, ParametersCollection $parametersCollection): void
    {
        foreach ($this->fromRestDescriptionList as $description) {
            if ($description->acceptMultiple()) {
                while (!empty($restArguments)) {
                    $nextArgument = array_shift($restArguments);
                    $parametersCollection->addParameter(new Parameter($description, $nextArgument));
                }
                continue;
            }
            if (empty($restArguments)) {
                throw new \Exception("Argument with multiple options need to be last");
            }
            $nextArgument = array_shift($restArguments);

            $parametersCollection->addParameter(new Parameter($description, $nextArgument));
        }
    }

    /**
     * @param ParameterDescription[] $parametersDescriptionList
     * @return array<string, ParameterDescription>
     */
    private function indexParametersDescriptionList(array $parametersDescriptionList): array
    {
        $list = [];

        foreach ($parametersDescriptionList as $parametersDescription) {
            $list[$parametersDescription->getName()] = $parametersDescription;
            if ($parametersDescription->getShortName() !== NULL) {
                $list[$parametersDescription->getShortName()] = $parametersDescription;
            }
        }

        return $list;
    }
}