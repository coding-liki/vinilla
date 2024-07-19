<?php

namespace Commands;

use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Command\CommandRunner;

class Help extends AbstractCommand
{
    public function __construct(private CommandRunner $runner)
    {
        parent::__construct();
    }

    public static function getHelpDescription(): string
    {
        return "Выводит данную help справку";
    }

    public function run(): ?CommandContext
    {
        $this->runner->printKnownCommands();
        return NULL;
    }
}