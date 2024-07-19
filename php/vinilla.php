<?php

use Lib\Command\CommandRunner;
use Lib\Command\Concrete\Console;

require_once __DIR__ . "/vendor/CodingLiki/Autoloader/Autoloader.php";

require_once __DIR__ . "/include.php";

const BINS_FOLDER = CURRENT_WORKING_DIR . "/vendor/.bin/";

const BINS_JSON_FILE_NAME = "bins.json";


checkTmpFolder();
$command = "";


function tryExecute(string $command, array $arguments)
{
    $binFolder = BINS_FOLDER;

    checkCreateFolder($binFolder);

    chdir($binFolder);

    $binsConfiguration = file_exists(BINS_JSON_FILE_NAME)
        ? json_decode(file_get_contents(BINS_JSON_FILE_NAME), true)
        : [];

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
}

$commandsRunner = new CommandRunner(new Console\NameExtractor(), new Console\ParametersExtractor());
$commandsRunner->addCommandList([
    new \Commands\Help($commandsRunner),
    new \Commands\UpdateCache(),
    new \Commands\Update(),
    new \Commands\PrintCommand(),
    new \Commands\Init(),
    new \Commands\SelfUpdate(),
    new \Commands\Clear(),
    new \Commands\Upgrade(),
    new \Commands\ListCommand(),
    new \Commands\ShowBins($commandsRunner),
    new \Commands\Install(),
    new \Commands\Uninstall(),
    new \Commands\UpdateModule(),
]);

try {
    $commandsRunner->run();
} catch (Throwable $t) {
    if ($argc > 1) {
        $command = $argv[1];
    }
    try {
        tryExecute($command, $argv);
    } catch (Throwable $t) {
        $commandsRunner->printKnownCommands();

        echo "{$t}";
    }
}
