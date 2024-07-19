<?php
declare(strict_types=1);

namespace CodingLiki\Autoloader\Components;

use CodingLiki\Autoloader\Interfaces\AutoloaderComponentInterface;

class FromRootWithoutNamespaceParts implements AutoloaderComponentInterface
{

    public function load(string $fullClassName): bool
    {
        $nameParts = explode('\\', $fullClassName);

        while(count($nameParts) > 0){
            array_shift($nameParts);
            if($this->tryLoad($nameParts)){
                return true;
            }
        }

        return false;
    }

    private function tryLoad(array $nameParts): bool
    {
        $fileName = sprintf("%s/../../../../%s.php", __DIR__, implode('/', $nameParts));
        if (file_exists($fileName)) {
            require_once $fileName;

            return true;
        }

        return false;
    }
}