<?php

$autoloader = require __DIR__.'/../../../vendor/autoload.php';

$contents = shell_exec('cd ../../../ && php hyde list --format=json --env=production');

$list = (json_decode($contents, true));

$timeStart = microtime(true);


$buffer = '';
$table = <<<'MARKDOWN'
| Command                                 | Description                                                                                  |
|-----------------------------------------|----------------------------------------------------------------------------------------------|

MARKDOWN;

foreach ($list['commands'] as $command) {
    if ($command['hidden']) {
        echo 'Skipping '.$command['name']."\n";
    } else {
        echo 'Processing '.$command['name']."\n";
        makeMarkdown($command);
        makeTable($command);
    }
}

function makeMarkdown(array $command)
{

    $template = <<<MARKDOWN
## [Command Title]

<a name="[Command Anchor]" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

[Command Description]

```bash
php hyde [Command Example]
```

MARKDOWN;


    $name = $command['name'];
    $anchor = str_replace(':', '-', $name);
    $description = $command['description'];
    $help = $command['help'];
    $example = $command['usage'][0];
    $markdown = $template;

    $markdown = str_replace('[Command Name]', $name, $markdown);
    $markdown = str_replace('[Command Anchor]', $anchor, $markdown);
    $markdown = str_replace('[Command Title]', $description, $markdown);
    $markdown = str_replace('[Command Description]', $help, $markdown);
    $markdown = str_replace('[Command Example]', $example, $markdown);


    global $buffer;
    $buffer .= $markdown."\n"."\n";
}


function makeTable(array $command)
{
    $template = '| [`[Command Name]`](#[Command Anchor])  | [Command Description] | ';

    $name = $command['name'];
    $anchor = str_replace(':', '-', $name);
    $description = $command['description'];

    $markdown = $template;

    $markdown = str_replace('[Command Name]', $name, $markdown);
    $markdown = str_replace('[Command Anchor]', $anchor, $markdown);
    $markdown = str_replace('[Command Description]', $description, $markdown);

    global $table;
    $table .= $markdown."\n";


}


file_put_contents(__DIR__.'/commands.md', $table ."\n". $buffer);


$template = <<<MARKDOWN
## [Command Description]

<a name="[Command Name]" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

[Command Description]

```bash
[Command Example]
```

MARKDOWN;



$templateWithOptions = <<<MARKDOWN
## [Command Description]

<a name="[Command Name]" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

[Command Description]

```bash
[Command Example]
```

**Supports the following options:**

```
[Command Options]
```
MARKDOWN;


$optionsTemplate = <<<TXT
[Option]       [Option Description]
TXT;


echo 'Done in '.round((microtime(true) - $timeStart) * 1000 ).'ms'."\n";
