<?php

namespace Lib\Command;

use Lib\Command\Parameters\ParameterDescription;
use Lib\Command\Parameters\ParameterDescriptionInterface;
use Lib\Command\Parameters\ParametersExtractorInterface;
use Lib\Command\Parameters\ValuePresenceMod;

class CommandRunner
{
    /**
     * @param array<string, CommandInterface> $commandList
     */
    public function __construct(private CommandNameExtractorInterface $commandNameExtractor, private readonly ParametersExtractorInterface $parametersExtractor, private array $commandList = [])
    {
    }

    /**
     * @param CommandInterface[] $commandList
     */
    public function addCommandList(array $commandList): static
    {
        foreach ($commandList as $command) {
            $this->commandList[$command::getName()] = $command;
        }

        return $this;
    }

    public function run(): void
    {
        $commandName = $this->commandNameExtractor->extract();

        $nextCommandContext = new CommandContext($commandName);

        while ($nextCommandContext !== NULL) {
            $command = $this->commandList[$nextCommandContext->name]
                ?? throw new \Exception("Unknown command `{$nextCommandContext->name}`");

            try {
                $parametersDescriptionList = $command->getParametersDescription();

                array_unshift($parametersDescriptionList, (new ParameterDescription("help", "Вывести описание команды и её параметров"))->setShortName('h'));

                $parameters = $nextCommandContext->parametersCollection
                    ?? $this->parametersExtractor->extract($parametersDescriptionList);
            } catch (\Throwable $exception) {
                echo "{$command->getName()}\n";
                foreach ($command->getParametersDescription() as $parameter) {
                    echo "{$parameter->getName()}";
                    echo " " . match ($parameter->getValuePresenceMod()) {
                            ValuePresenceMod::FROM_REST => "без аргумента",
                            ValuePresenceMod::NO_VALUE => "без значения",
                            ValuePresenceMod::OPTIONAL => "значение не обязательно",
                            ValuePresenceMod::REQUIRED => "значение обязательно",
                        };
                    echo " - {$parameter->getDescription()}\n";
                }
                throw $exception;
            }

            if ($parameters->has('help')) {
                echo implode("\n", [
                    $commandName,
                    $command::getHelpDescription(),
                    ...array_map($this->printParametersDescription(...), $command->getParametersDescription()),
                    '',
                ]);
                $nextCommandContext = NULL;
            } else {
                $command->setParameters($parameters);
                $nextCommandContext = $command->run();
            }
        }
    }

    public function printKnownCommands(): void
    {
        foreach ($this->commandList as $name => $command) {
            echo "$name - {$command::getHelpDescription()}\n";
        }
    }

    /**
     * @return string[]
     */
    public function getKnownCommandNames(): array
    {
        return array_keys($this->commandList);
    }

    private function printParametersDescription(ParameterDescriptionInterface $description): string
    {
        $shortName = $description->getShortName() ?? 'без короткого имени';
        return "{$description->getName()}($shortName) - {$description->getDescription()}";
    }
}