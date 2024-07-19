<?php

namespace Commands;

use Commands\Traits\ModuleHelpers;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Module;

class Init extends AbstractCommand
{
    use ModuleHelpers;

    public static function getHelpDescription(): string
    {
        return "Инициализирует проект в текущей директории";
    }

    public function run(): ?CommandContext
    {
        chdir(CURRENT_WORKING_DIR);
        echo "\nstartInit\n";
        if (file_exists(SETTINGS_FILE)) {
            $settings = file_get_contents(SETTINGS_FILE);
            $module = new Module(json_decode($settings, true, 512, JSON_THROW_ON_ERROR));
            $this->checkAndInstallDependencies($module);
            $this->postInstallProcess();
        } else {
            $settings = [];
            echo "Введите название проекта: ";
            $settings['name'] = readline();
            if (empty($settings['name'])) {
                $settings['name'] = basename(CURRENT_WORKING_DIR);
                echo sprintf("Название проекта выбрано на основание текущей папки проекта - %s\n", $settings['name']);
            }
            echo "Введите вендора проекта: ";
            $settings['vendor'] = readline();
            echo "Введите описание проекта: ";
            $settings['description'] = readline();
            echo "Введите адрес репозитория для проекта: ";
            $settings['repo_url'] = readline();
            $this->saveSettings($settings);
        }

        return NULL;
    }
}