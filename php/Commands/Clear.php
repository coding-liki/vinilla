<?php

namespace Commands;

use Commands\Traits\FoldersHelper;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;

class Clear extends AbstractCommand
{
    use FoldersHelper;

    public static function getHelpDescription(): string
    {
        return "Удаляет всё из папки vendor проекта в текущей директории";
    }

    public function run(): ?CommandContext
    {
        echo "Deleting all vendors\n";
        $this->deleteDir("./vendor");
        echo "All vendors have been deleted\n";
        return NULL;
    }
}