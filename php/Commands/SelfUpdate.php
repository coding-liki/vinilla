<?php

namespace Commands;

use Commands\Traits\ModuleHelpers;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;

class SelfUpdate extends AbstractCommand
{
    use ModuleHelpers;
    public static function getHelpDescription(): string
    {
        return "Обновляет vinilla_php на последнюю версию из репозитория";
    }
    public function run(): ?CommandContext
    {
        $interpreter = "php";
        $install_dir = str_replace("/php", "", VINILLA_INSTALLATION_DIR);
        chdir(TMP_DIR);
        $this->gitFetchModule("https://github.com/coding-liki/vinilla.git", "./");
        chdir("vinilla");
        exec("./install.sh -t $interpreter -f $install_dir");
        echo "Updated successfully";
        return NULL;
    }
}