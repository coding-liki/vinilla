<?php

namespace Lib\Command\Concrete\Console;

use Lib\Command\CommandNameExtractorInterface;

class NameExtractor implements CommandNameExtractorInterface
{
    /**
     * @throws \Exception
     */
    public function extract(): string
    {
        global $argv;
        return $argv[1] ?? throw new \Exception('No command name given');
    }
}