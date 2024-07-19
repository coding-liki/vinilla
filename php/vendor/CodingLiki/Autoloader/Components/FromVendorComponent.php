<?php
declare(strict_types=1);

namespace CodingLiki\Autoloader\Components;

use CodingLiki\Autoloader\Interfaces\AutoloaderComponentInterface;

class FromVendorComponent implements AutoloaderComponentInterface
{

    public function load(string $fullClassName): bool
    {
        $nameParts = explode('\\', $fullClassName);

        $fileName = sprintf("%s/../../../../vendor/%s.php", __DIR__, implode('/', $nameParts));
        if (file_exists($fileName)) {
            require_once $fileName;

            return true;
        }

        return false;
    }
}