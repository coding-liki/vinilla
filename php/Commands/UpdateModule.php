<?php

namespace Commands;

use Commands\Traits\ModuleHelpers;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Command\Parameters\ParameterDescription;
use Lib\Command\Parameters\ValuePresenceMod;

class UpdateModule extends AbstractCommand
{
    use ModuleHelpers;

    public static function getName(): string
    {
        return 'update';
    }

    public function run(): ?CommandContext
    {

        $moduleList = $this->getParameter('module_list', []);

        foreach ($moduleList as $module) {
            $this->updateModule($module);
        }
        if (!empty($moduleList)) {
            $this->postInstallProcess();
        }
        return NULL;
    }

    public function getParametersDescription(): array
    {
        return [
            (new ParameterDescription("module_list", "Список модулей для обновления", acceptMultiple: true,))->setValuePresenceMod(ValuePresenceMod::FROM_REST),
        ];
    }
}