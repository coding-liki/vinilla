<?php

namespace Commands;

use Commands\Traits\ModuleHelpers;
use Lib\Cache;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Command\Parameters\ParameterDescription;
use Lib\Command\Parameters\ValuePresenceMod;

class Uninstall extends AbstractCommand
{
    use ModuleHelpers;

    public function run(): ?CommandContext
    {
        $moduleList = $this->getParameter('module_list', []);

        foreach ($moduleList as $module) {
            $this->checkRootPath();
            $module = Cache::$fullNameIndex[$module] ?? Cache::$urlIndex[$module] ?? new Module($module);
            echo $module->getFullName() . " uninstalling\n";
            if ($module->isInstalled()) {
                $this->deleteDir("./vendor/" . $module->getFullName());
                $current_working_dir = CURRENT_WORKING_DIR;

                $this->updateProjectDependencies($current_working_dir, [], [$module->getFullName()]);
            } else {
                echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install " . $module->getFullName() . "\n**********************\n";
            }
        }

        if (!empty($moduleList)) {
            $this->postInstallProcess();
        }

        return NULL;
    }

    public function getParametersDescription(): array
    {
        return [
            (new ParameterDescription("module_list", "Список модулей для удаления", acceptMultiple: true,))->setValuePresenceMod(ValuePresenceMod::FROM_REST),
        ];
    }
}