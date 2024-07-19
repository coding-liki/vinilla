<?php

namespace Commands;

use Commands\Traits\ModuleHelpers;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Module;

class Upgrade extends AbstractCommand
{
    use ModuleHelpers;

    public static function getHelpDescription(): string
    {
        return "Обновляет все пакеты до актуальной версии текущих веток";
    }

    public function run(): ?CommandContext
    {
        chdir(CURRENT_WORKING_DIR);
        echo "startUpgrade\n";
        if (file_exists(SETTINGS_FILE)) {
            $settings = file_get_contents(SETTINGS_FILE);
            $module = new Module(json_decode($settings, true));
            $dependencies = $module->getDependencies();
            foreach ($dependencies as $dependency) {
                $this->updateModule($dependency);
            }

            $this->runPostInstallDependencyScripts($module);
        }

        return NULL;
    }
}