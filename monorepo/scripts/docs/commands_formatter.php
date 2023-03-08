<?php

const OUT_DIR = __DIR__.'/../../../docs/_data/commands';

$autoloader = require __DIR__.'/../../../vendor/autoload.php';

$contents = shell_exec('cd ../../../ && php hyde list --format=json --env=production');

$timeStart = microtime(true);

$list = (json_decode($contents, true));

$list['application']['name'] = 'HydeCLI';


array_map('unlink', glob(OUT_DIR.'/*.md'));

@mkdir(OUT_DIR);


foreach ($list['commands'] as $index => $command) {
    if ($command['hidden']
        || $command['name'] === 'list'
        || $command['name'] === 'help'
        || $command['name'] === 'torchlight:install'
        || str_starts_with($command['name'], '_')) {
        echo 'Skipping '.$command['name']."\n";
        unset($list['commands'][$index]);
    } else {
        echo 'Processing '.$command['name']."\n";

        // remove default options
        $default = ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env'];

        foreach ($command['definition']['options'] as $optionIndex => $option) {
            if (in_array($optionIndex, $default)) {
                unset($command['definition']['options'][$optionIndex]);
            }
        }

        $list['commands'][$index] = $command;
    }
}

$list['commands'] = array_values($list['commands']);
file_put_contents('commands.json', json_encode($list, JSON_PRETTY_PRINT));


foreach ($list['commands'] as $command) {
    $matter = (new \Hyde\Framework\Actions\ConvertsArrayToFrontMatter())->execute($command, \Symfony\Component\Yaml\Yaml::DUMP_OBJECT_AS_MAP);
    $markdown = $command['help'];


    $id = str_replace(':', '-', $command['name']);
    file_put_contents(OUT_DIR.'/'.$id.'.md', "{$matter}\n{$markdown}\n");
}

echo 'Done in '.round((microtime(true) - $timeStart) * 1000).'ms'."\n";
