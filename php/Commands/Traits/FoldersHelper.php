<?php

namespace Commands\Traits;

use RuntimeException;

trait FoldersHelper
{
    public function checkVendorFolder(string $vendor, string $work_folder): string
    {
        $this->checkCreateFolder($work_folder . '/vendor/' . $vendor);

        return $work_folder . '/vendor/' . $vendor;
    }

    public function checkCreateFolder(string $folder, int $mode = 0777): bool
    {
        if (!is_dir($folder)) {
            if (!@mkdir($folder, $mode, true) && !is_dir($folder)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $folder));
            }
            return false;
        }

        return true;
    }

    public function recursive_copy($src, $dst): void
    {
        $dir = opendir($src);
        if (!@mkdir($dst) && !is_dir($dst)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dst));
        }
        while (($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursive_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function deleteDir(string $src): void
    {
        if ($this->patchCanBeDeleted($src)) {
            echo "trying to delete not project and not tmp folder `$src`\n";
            exit(1);
        }
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->deleteDir($src . '/' . $file);
                } else {
                    echo "Delete $src/$file\n";
                    unlink($src . '/' . $file);
                }
            }
        }
        closedir($dir);
        rmdir($src);

    }

    public function patchCanBeDeleted(string $src): bool
    {
        $dir_path = explode("/", $src);

        $availableStarting = ["tmp", "vendor", basename(CURRENT_WORKING_DIR)];

        return !in_array($dir_path[1], $availableStarting, true)
            && !in_array($dir_path[0], $availableStarting, true);
    }

    /**
     * Проверяем и создаём, если нет, папку vendor в текущей папке
     */
    public function checkRootPath(): void
    {
        $this->checkCreateFolder(CURRENT_WORKING_DIR . "/vendor");
    }
}