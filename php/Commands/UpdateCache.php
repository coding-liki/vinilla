<?php

namespace Commands;

use Lib\Cache;
use Lib\Command\AbstractCommand;
use Lib\Command\CommandContext;

class UpdateCache extends AbstractCommand
{
    public static function getHelpDescription(): string
    {
        return "Загружает новый кэш с сервера, если требуется обновление";
    }

    public function run(): ?CommandContext
    {
        Cache::updateCache();

        return NULL;
    }
}