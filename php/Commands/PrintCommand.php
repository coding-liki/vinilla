<?php

namespace Commands;

use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Module;

class PrintCommand extends AbstractCommand
{
    public static function getName(): string
    {
        return 'print';
    }

    public static function getHelpDescription(): string
    {
        return "Выводит настройки текущего проекта";
    }

    public function run(): ?CommandContext
    {
        chdir(CURRENT_WORKING_DIR);
        if (file_exists(SETTINGS_FILE)) {
            $settings = file_get_contents(SETTINGS_FILE);
            $module = new Module(json_decode($settings, true));
            print_r($module->settings);
        } else {
            echo "Проинициализируйте проект!!!\n";

            return new CommandContext(Help::getName());
        }

        return null;
    }
}