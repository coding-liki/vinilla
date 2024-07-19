<?php
declare(strict_types=1);

namespace CodingLiki\Autoloader\Components;

use CodingLiki\Autoloader\Interfaces\AutoloaderComponentInterface;

class FromVendorWithScrComponent implements AutoloaderComponentInterface
{

    public function load(string $fullClassName): bool
    {
        $nameParts = explode('\\', $fullClassName);

        [$moduleNameParts, $otherNameParts] = $this->splitParts($nameParts);
        $fileName = sprintf("%s/../../../../vendor/%s/src/%s.php", __DIR__, implode('/', $moduleNameParts), implode('/', $otherNameParts));
        if (file_exists($fileName)) {
            require_once $fileName;

            return true;
        }

        return false;
    }

    private function splitParts(array $parts)
    {
        $pathToCheckRoot = __DIR__.'/../../../../vendor/';
        $moduleNameParts = [];

        while($nextPart = array_shift($parts)){
            if(is_dir($pathToCheckRoot.$nextPart)){
                $moduleNameParts[] = $nextPart;
                $pathToCheckRoot .= "$nextPart/"; 
            } else {
                array_unshift($parts, $nextPart);
                break;
            }
        }

        return [$moduleNameParts, $parts]; 
    }
}