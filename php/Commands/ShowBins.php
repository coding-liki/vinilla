<?php

namespace Commands;

use Commands\Traits\ModuleHelpers;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Command\CommandRunner;

class ShowBins extends AbstractCommand
{
    use ModuleHelpers;

    public function __construct(private readonly CommandRunner $runner)
    {
        parent::__construct();
    }

    public static function getHelpDescription(): string
    {
        return "Выводит список доступных бинарных пакетов для запуска";
    }

    public function run(): ?CommandContext
    {
        $bins = json_decode(file_get_contents(BINS_FOLDER . BINS_JSON_FILE_NAME), true);

        $binNames = array_keys($bins);

        array_push($binNames, ...$this->runner->getKnownCommandNames());
        echo implode(" ", $binNames);
        return NULL;
    }
}