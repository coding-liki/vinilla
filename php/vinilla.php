<?php

require_once __DIR__ . "/include.php";

const BINS_FOLDER = CURRENT_WORKING_DIR . "/vendor/.bin/";

const BINS_JSON_FILE_NAME = "bins.json";

/**
 * Устанавливаем модуль с помощью git
 *
 * @param [type] $module_url
 *
 * @return void
 */
function installModule($module_url, $updating = false)
{
    static $depth = 0;
    $depth++;
    $module = Cache::$fullNameIndex[$module_url] ?? Cache::$urlIndex[$module_url] ?? new Module($module_url);

    if (!$module->initialised) {
        echo "Module is not known!\nTry to update Cache\n vinilla_php update\n";
        exit(1);
    }

    /** Проверим зависимости */
    $dependencies = $module->getDependencies();
    foreach ($dependencies as $dependency) {
        installModule($dependency, $updating);
    }

    if ($module->isInstalled()) {
        echo "Module `" . $module->getFullName() . "` is already installed\n";
        $module->loadLocalVersion();
        if ($module->local_version !== $module->settings['version']) {
            echo "Module `" . $module->getFullName() . "` can be updated\nRun 'vinilla_php update " . $module->getFullName() . "'\n";
        }

        return;
    }

    echo "Installing `" . $module->getFullName() . "`\n";

    $current_working_dir = CURRENT_WORKING_DIR;

    $cwd = getcwd();
    chdir(TMP_DIR);

    if (is_dir("./$module->git_name")) {
        chdir($module->git_name);
        exec("git pull");
    } else {
        gitFetchModule($module->url, TMP_DIR);
        chdir("./$module->git_name");
    }

    if (!is_file("./" . SETTINGS_FILE)) {
        echo "Not Vinilla module\n";
        exit(1);
    }

    $vendor = $module->vendor;

    if ($vendor === "") {
        echo "vendor is not set in module settings\n";
        exit(1);
    }

    $install_module_name = $module->name;

    $vendor_dir = checkVendorFolder($vendor, CURRENT_WORKING_DIR);

    chdir($vendor_dir);

    $old_settings = [];
    if (checkCreateFolder("./$install_module_name")) {
        chdir("./$install_module_name");
        if (is_file("./" . SETTINGS_FILE)) {
            $old_settings = json_decode(file_get_contents("./" . SETTINGS_FILE), true);
        } else {
            echo "Was Not Vinilla module\nReinstall as Vinilla module (Y/N)?\n";
            $answer = readline();
            if (in_array($answer, ["N", "n", "н", "Н", "No", "no", "Нет", "нет"])) {
                exit(1);
            }
        }
    }

    $module_name = $module->git_name;
    if (!isset($old_settings['version']) || ($old_settings['version'] < $module->settings['version'] && $updating)) {
        recursive_copy(TMP_DIR . "/$module_name", "$vendor_dir/$install_module_name");
        echo "copy complete!\n\n";
        $module->runScripts();

        updateProjectDependencies($current_working_dir, [$module->getFullName()]);
    } else if ($old_settings['version'] < $module->settings['version']) {
        echo "module can be updated\nPlease run \n**********************\nvinilla_php update $vendor/$install_module_name\n**********************\n";
    } else {
        echo "You have the newest version of $vendor/$install_module_name\n";
    }
    if ($depth === 1) {
        postInstallProcess();
    }
    chdir($cwd);
}

/**
 * @param string $current_working_dir
 * @param array $addDependencies
 * @param array $removeDependencies
 */
function updateProjectDependencies(string $current_working_dir, array $addDependencies = [], array $removeDependencies = [])
{
    $theCwd = getcwd();
    chdir($current_working_dir);
    $settings = file_get_contents(SETTINGS_FILE);
    $project = new Module(json_decode($settings, true));
    $dependencies = $project->getDependencies();
    if (!empty($addDependencies)) {
        array_push($dependencies, ...$addDependencies);
    }

    $dependencies = array_values(array_unique(array_diff($dependencies, $removeDependencies)));
    $project->setDependencies($dependencies);
    saveSettings($project->settings);
    chdir($theCwd);
}

function printPackageInfo()
{
    chdir(CURRENT_WORKING_DIR);
    if (file_exists(SETTINGS_FILE)) {
        $settings = file_get_contents(SETTINGS_FILE);
        $module = new Module(json_decode($settings, true));
        print_r($module->settings);
    } else {
        echo "Проинициализируйте проект!!!\n";
        echoHelp();
    }

    exit(0);
}

function checkAndInstallDependencies(Module $module)
{
    $dependencies = $module->getDependencies();
    foreach ($dependencies as $dependency) {
        installModule($dependency);
    }
}

function uninstallModule($module_name)
{
    checkRootPath();
    $module = Cache::$fullNameIndex[$module_name] ?? Cache::$urlIndex[$module_name] ?? new Module($module_name);
    echo $module->getFullName() . " uninstalling\n";
    if ($module->isInstalled()) {
        deleteDir("./vendor/" . $module->getFullName());
        $current_working_dir = CURRENT_WORKING_DIR;

        updateProjectDependencies($current_working_dir, [], [$module->getFullName()]);
    } else {
        echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install " . $module->getFullName() . "\n**********************\n";
    }
}

function updateModule($module_name)
{
    checkRootPath();
    $module = Cache::$fullNameIndex[$module_name] ?? Cache::$urlIndex[$module_name] ?? new Module($module_name);
    if ($module->isInstalled()) {
        if (is_file("./vendor/" . $module->getFullName() . "/" . SETTINGS_FILE)) {
            chdir("./vendor/" . $module->getFullName());
            exec("git pull");
        } else {
            echo "It is not Vinilla module!!!\nPlease run \n**********************\nvinilla_php install " . $module->getFullName() . "\n**********************\n";
        }
    } else {
        echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install " . $module->getFullName() . "\n**********************\n";
    }
}

function initialiseProject()
{
    chdir(CURRENT_WORKING_DIR);
    echo "\nstartInit\n";
    if (file_exists(SETTINGS_FILE)) {
        $settings = file_get_contents(SETTINGS_FILE);
        $module = new Module(json_decode($settings, true, 512, JSON_THROW_ON_ERROR));
        checkAndInstallDependencies($module);
        postInstallProcess();
    } else {
        $settings = [];
        echo "Введите название проекта: ";
        $settings['name'] = readline();
        if (empty($settings['name'])) {
            $settings['name'] = basename(CURRENT_WORKING_DIR);
            echo sprintf("Название проекта выбрано на основание текущей папки проекта - %s\n", $settings['name']);
        }
        echo "Введите вендора проекта: ";
        $settings['vendor'] = readline();
        echo "Введите описание проекта: ";
        $settings['description'] = readline();
        echo "Введите адрес репозитория для проекта: ";
        $settings['repo_url'] = readline();
        saveSettings($settings);
    }
}

function postInstallProcess()
{
    chdir(CURRENT_WORKING_DIR);
    $settings = file_get_contents(SETTINGS_FILE);
    $rootModule = new Module(json_decode($settings, true));
    runPostInstallDependencyScripts($rootModule);
    updateBinsSettings($rootModule);
}

function updateBinsSettings(Module $module): array
{
    static $depth = 0;
    $depth++;

    $currentDepth = $depth;

    $bins = $module->bins;
    foreach ($module->getDependencies() as $dependency) {
        $moduleDependency = Cache::$fullNameIndex[$dependency] ?? Cache::$urlIndex[$dependency] ?? new Module($dependency);
        $bins += updateBinsSettings($moduleDependency);
    }

    if ($currentDepth > 1) {
        return $bins;
    }

    checkCreateFolder(BINS_FOLDER);
    file_put_contents(BINS_FOLDER . BINS_JSON_FILE_NAME, json_encode($bins));

    return [];
}

function runPostInstallDependencyScripts(Module $module)
{
    foreach ($module->getDependencies() as $dependency) {
        $moduleDependency = Cache::$fullNameIndex[$dependency] ?? Cache::$urlIndex[$dependency] ?? new Module($dependency);
        if (isset($moduleDependency->settings['after_full_install_script'])) {
            $scriptPath = CURRENT_WORKING_DIR . "/vendor/" . $dependency . '/' . $moduleDependency->settings['after_full_install_script'];
            if (str_ends_with($scriptPath, '.php')) {
                include_once $scriptPath;
            }
        }
    }
}

function saveSettings(array $settings)
{
    file_put_contents(SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function clearVendors()
{
    deleteDir("./vendor");
    echo "Deleting all vendors\n";
}

function selfUpdate()
{
    $interpreter = "php";
    $install_dir = str_replace("/php", "", __DIR__);
    chdir(TMP_DIR);
    gitFetchModule("https://github.com/coding-liki/vinilla.git", "./");
    chdir("vinilla");
    exec("./install.sh -t $interpreter -f $install_dir");
    echo "Updated successfully";
}

checkTmpFolder();
$command = "";
$one_commands = [
    'help',
    'print',
    'init',
    'update',
    'load',
    'clear',
    'self-update',
    'upgrade',
    'bins',
    'list'
];

if ($argc > 1) {
    $command = $argv[1];
}
function echoHelp()
{
    echo "Для установки зависимостей текущего проекта либо инициализации пустого файла конфигурации используйте `vinilla_php init`\n";
    echo "Для вывода информации о текущем модуле используйте `vinilla_php print`\n";
    echo "Для установки модуля используйте `vinilla_php install MODULE_URL`\n";
    echo "Для удаления модуля используйте `vinilla_php uninstall MODULE_URL`\n";
    echo "Для обновления модуля используйте `vinilla_php update MODULE_URL`\n";
    echo "Для обновления всех модулей используйте `vinilla_php upgrade`\n";
    echo "Для обновления кэша используйте `vinilla_php update`\n";
    echo "Для обновления Vinilla Packet Manager используйте `vinilla_php self-update`\n";
    exit(1);
}


function tryExecute(string $command, array $arguments)
{
    $binFolder = BINS_FOLDER;

    checkCreateFolder($binFolder);

    chdir($binFolder);

    try {
        $binsConfiguration = json_decode(file_get_contents(BINS_JSON_FILE_NAME), true);
        if (!isset($binsConfiguration[$command])) {
            throw new RuntimeException("No execute configuration\n");
        }
        $commandConfig = $binsConfiguration[$command];

        $resultCommand = $commandConfig['path'] . " " . implode(" ", $commandConfig['prefix'] ?? []);
        array_shift($arguments);
        array_shift($arguments);
        chdir(CURRENT_WORKING_DIR);
        system($resultCommand . " " . implode(" ", $arguments), $code);
        if ($code !== 0) {
            throw new RuntimeException("Result code is $code");
        }
    } catch (Throwable $t) {
        echo "Can not execute $command\n $t\n";
    }
}

for ($i = 2; $i <= $argc; $i++) {
    $break = false;
    switch ($command) {
        case "install":
            if ($i < $argc)
                installModule($argv[$i]);
            break;
        case "uninstall":
            if ($i < $argc)
                uninstallModule($argv[$i]);
            break;
        case "update":
            if ($i < $argc)
                updateModule($argv[$i]);
            break;
        case "getcache":
            Cache::updateCache();
            break;
        default:
            if (!in_array($command, $one_commands, true)) {
                tryExecute($command, $argv);
                $break = true;
            }
    }
    if ($break) {
        exit(0);
    }
}

if ($argc < 3 && !in_array($command, $one_commands, true)) {
    echoHelp();
}
function upgrade()
{
    chdir(CURRENT_WORKING_DIR);
    echo "\nstartUpgrade\n";
    if (file_exists(SETTINGS_FILE)) {
        $settings = file_get_contents(SETTINGS_FILE);
        $module = new Module(json_decode($settings, true));
        $dependencies = $module->getDependencies();
        foreach ($dependencies as $dependency) {
            updateModule($dependency);
        }

        runPostInstallDependencyScripts($module);
    }
}

function showBins()
{
    global $one_commands;
    $bins = json_decode(file_get_contents(BINS_FOLDER . BINS_JSON_FILE_NAME), true);

    $binNames = array_keys($bins);

    array_push($binNames, ...$one_commands);
    array_push($binNames, ...[
        'install',
        'uninstall',
        'update',
    ]);
    echo implode(" ", $binNames);
}

function listPackages()
{
    $packages = array_keys(Cache::$fullNameIndex);

    echo implode(" ", $packages);
}

if ($argc < 3) {
    switch ($command) {
        case 'help':
            echoHelp();
            break;
        case 'print':
            printPackageInfo();
            break;
        case "init":
            initialiseProject();
            break;
        case "update":
            Cache::updateCache();
            break;
        case "self-update":
            selfUpdate();
            break;
        case "clear":
            clearVendors();
            break;
        case "load":
            Cache::loadCache();
            break;
        case 'upgrade':
            upgrade();
            break;
        case 'bins':
            showBins();
            break;
        case  'list':
            listPackages();
            break;
    }
}
// print_r($options);
