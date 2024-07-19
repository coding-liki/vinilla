<?php
declare(strict_types=1);

namespace  CodingLiki\Autoloader\Interfaces;

interface AutoloaderComponentInterface
{
    public function load(string $fullClassName): bool;
}