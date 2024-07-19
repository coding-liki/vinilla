<?php

namespace Commands;

use Commands\Traits\ModuleHelpers;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Command\Parameters\ParameterDescription;
use Lib\Command\Parameters\ValuePresenceMod;

class Install extends AbstractCommand
{
    use ModuleHelpers;

    public function run(): ?CommandContext
    {

        $moduleList = $this->getParameter('module_list', []);

        foreach ($moduleList as $module) {
            $this->installModule($module);
        }
        if (!empty($moduleList)) {
            $this->postInstallProcess();
        }
        return NULL;
    }

    public function getParametersDescription(): array
    {
        return [
            (new ParameterDescription("module_list", "Список модулей для установки", acceptMultiple: true,))->setValuePresenceMod(ValuePresenceMod::FROM_REST),
        ];
    }
}