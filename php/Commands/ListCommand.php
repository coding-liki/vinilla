<?php

namespace Commands;

use Lib\Cache;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;
use Lib\Command\Parameters\ParameterDescription;
use Lib\Command\Parameters\ValuePresenceMod;
use Lib\Module;

class ListCommand extends AbstractCommand
{

    public static function getName(): string
    {
        return 'list';
    }

    public static function getHelpDescription(): string
    {
        return "Выводит список доступных или установленных пакетов";
    }

    public function run(): ?CommandContext
    {

        if ($this->hasParameter('compact')) {
            $separator = $this->getParameter('separator', ' ');

            $packages = array_keys(Cache::$fullNameIndex);

            if ($this->hasParameter('only-installed')) {
                $packages = array_keys(array_filter(Cache::$fullNameIndex, fn(Module $module) => $module->isInstalled()));
            }

            echo implode($separator, $packages);
        } else {
            $packages = array_map(fn(Module $module) => [$module->name, $module->local_version, $module->settings['description'] ?? "Без описания"], Cache::$fullNameIndex);

            foreach (Cache::$fullNameIndex as $name => $package) {
                if ($this->hasParameter('only-installed') && $package->isInstalled() === MODULE_NOT_INSTALLED) {
                    continue;
                }
                echo "$name\n";
                echo "\tЛокальная Версия: {$package->local_version}\n";
                $description = $package->settings['description'] ?: 'Без описания';
                echo "\tОписание: {$description}\n";
                echo "\t" . match ($package->isInstalled()) {
                        MODULE_INSTALLED_AND_VINILLA => "модуль установлен",
                        MODULE_NOT_INSTALLED => "модуль не установлен"
                    } . "\n";
            }
        }
        return NULL;
    }

    public function getParametersDescription(): array
    {
        return [
            (new ParameterDescription("compact", "Вывести компактный список пакетов через разделитель"))->setShortName('c'),
            (new ParameterDescription("separator", "Разделитель, используемый при компактном выводе", valuePresenceMod: ValuePresenceMod::REQUIRED))->setShortName('s'),
            (new ParameterDescription("only-installed", "Вывести только установленные пакеты", valuePresenceMod: ValuePresenceMod::REQUIRED))->setShortName('i')
        ];
    }
}