<?php
define('TIME_START', microtime(true));
const VERSION = 'v1.0.0-rc.4';
const RUNNING_IN_CONSOLE = PHP_SAPI === 'cli';
ob_start();

if (RUNNING_IN_CONSOLE) {
    file_put_contents('php://stdout', 'Generating directory listing... ');
}

// Only the script directory
$path = getcwd();

$pathlabel = file_exists('.dl-pathlabel') ? file_get_contents('.dl-pathlabel') : $path ;

// Full path (when running from server)
//$path = trim(getcwd() . str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['REQUEST_URI']), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

$img = [
    'blank.gif' => 'data:image/gif;base64,R0lGODlhFAAWAKEAAP///8z//wAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAACE4yPqcvtD6OctNqLs968+w+GSQEAOw==',
    'unknown.gif' =>  'data:image/gif;base64,R0lGODlhFAAWAMQAAExMTN3d3bW1tZmZmWZmZv///8zMzHl5ee/v762trb29vWZmZoyMjKWlpdbW1ubm5vf391paWsPDw3Nzc4WFhVRUVP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUUABYALAAAAAAUABYAAAXdoKAoQpmczdAIzmO9r1LMdAEhjpoYLizXM8Qjp2rwYAJg8GEYSIpHS3KmYBwIhMhkYFA0GgnHK0HDmiOVgVptdJFnh51DcYgAAIzEYKJwK20SEWgMAgl8fn8CWREKEgKHFm81EIpYAwEGjxKINAlmDAYFQwoTBgiRQAxYU6KZpaeSM6oErEyPpqh/NbavuWVYDDMQvLixBZ/CxLBKEM3NCAEShrgNNs9CD9kPmAINBNTP2w4OBuUGjnrfpwPQ5BIkJSYpFBG4AwwUB/oT/P1nBAFeYDJHsGC5AKcshAAAOw==',
    'folder.gif' => 'data:image/gif;base64,R0lGODlhFAAWAPYAAFw+H/HDlMOkhbaSbZh6W8LCwqKioubm5n9lS5mZmf/evf/Tpt+zhr29vbGLZL2betfX17e3t3JMJ6CAYO/Kpue6jfnWssWyoKV5TNesgZdrP7+UaWdEIs6lfO/v797e3uG1iXxTKr+Zc4R6b6N2SszMzLKLZPXFla2DWo1xVf/Wre3Ak//bttW0lKurq//hw/jLn/3RpVtDLMOZbrWPaal+UdG1mYRYLOXCnfXIm8mrjKuHY7SUc9uwheW4jK+Macaed6h8UWNCIf/ZtLyWcWtIJOy9jsSddqWEY//jx5p7XLqPY5VvSMWljIBmTPHPrPLHnAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUUAFEALAAAAAAUABYAAAf/gFGCg4SFhoeIiB6Li4mDix+REJORBx6HBzZENA4oNZ9EJZSWl4I2Ok9DqioLFSQ7DQWio1EDFKwxuTkrM0ybJp5BF5EiOFAryCA9HTw6LQqqUBsaog9ND9icNCICz6sBPTcRJQ9J5tDo6bk+HSEuBUQv8uqrC7sZR+7w9Li4RvgDJCRoQANdP3v2wDH7IZAgi4f9YEg8UQHgjoYo6u2imIzBQiRFBmZM2HEZPiADkCjhIHJdxZMdgKBMqSSFkIEkjimLKWLTL5UpEAAwUADDkpMpf+xYCjSoDBnvLmi4EaKqhKtFsmoFIGPEuA8lIrgwkKCs2bMG3ok6AFZWrLdwBN+uDQQAOw==',
    'image2.gif' => 'data:image/gif;base64,R0lGODlhFAAWAOYAAAB0J6u+sr4mJoWFha2trQBrjlpaWubm5jOKUHt7eyaoUfpGRhSUvtbW1p+ypff391NTU1G53PcxMQecOWZmZsTExIygkuUxMTClzQZ8pACLL0ehZYOhrL5JQv9aWnJycrW1tW6LWxiYQu/v73+pt93d3Uqz1oyMjP///8zMzNYrKzKLbDGtWgBxlgCZM/pCQhSJsJK2wxqjSGZmZpmZmaWlpb29vf9mZiOSt/9RUZmZmQCCK1SvbO47OwB1nPY5Oc4pKeEuLjqwYT2lyAyBqIypswCZM/w6Ou0zMwB9KpCjlhagRCOXvgR4nimtUv9NTR+lTP5FRTaMU8lLRHCKWt4pKROMtVKrcD2nygiErf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUUAFoALAAAAAAUABYAAAf/gCA2NiCFBIc1NDUgDQdaj482KCgPlZUjIw2KBCmOkCCWmJgHJSk0ijWdnw8jpCWvDSkVNDaoqloEIze7Hk8vP4MnFTY1NQQNjzUHvDlRPUiHJ6enNRWONSUeHlFHP0EqqMWJHzbXJTk5RxIXQALSh4bk5k8LSEhTISwKG0qFIATytGB70UNFBx5CFEBZIsJCoRoUygks8YMdFSEyJmjUgIAACYjWBB5oQEzfBBcodwDgwIRGxGutUoBQeNKFBgArsFg5YQCEIwKsSlS4smSCBg1JVpjAkSFBzxK4Ko0MIEKDyhURMBDx8QECAagEJj0Y6UAKThMYGDRp0fUrLrExVUEUGYKDSJMCBdpCrQH3QIoYTGBk8NGiAFuve6WWaFABRKITAxJI7upTC40Ri1MQIvB4QOQEXUPSgBz5g2kKqCkYWA2BQoMRWkpVmE172KDbFRo9CgQAOw==',
    'movie.gif' => 'data:image/gif;base64,R0lGODlhFAAWAMQAAFRUVMTExK6urmZmZtbW1ubm5mZmZnt7e729vczMzN7e3lpaWu7u7oSEhLW1tXNzc4yMjP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUUABEALAAAAAAUABYAAAXQ4NGMx2Oe6DMoRXQQSTMsdL0MDxIMhMIcpYfDgdAFAkXTLEEoDJALYiDBpO6Gi0DziQAQmT0CbIDVFlQ0Xa/AKCgI6AViW5QnWIxIWzFAOABzTlBqeG4EfV1mQUIIYG8wBzNZdA4NiycDmQICgJQyNjYDm50FQCqbRDpFDnGBfQ4LAg5HVUeinK5IXo1ijwlkf2atWoVvcWaIdnh6bq+kyYRshs6BpoyOYgklAwDCSJcomUPdW0qgNQAAmQBMhglIRUfyRTpMPoYwVFT5+r0tIQA7',
    'text.gif' => 'data:image/gif;base64,R0lGODlhFAAWAMQAAFxcXNbW1q2trZmZmf///8TExO/v73Nzc+bm5rW1tXl5eaWlpb29vWZmZszMzPf3993d3YWFhWZmZoyMjP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUUABQALAAAAAAUABYAAAXFYMIwSSmcy7AkAUK9L0PMNPEYgSo4LizXMwMip1rwYAlgcDgoFI+UJGFArUIcAwdjsRAEXgIlQYitqgqu8NQ8GBYSp8GBkRbfho63YF5fs6sBb3wUanY3VwkHaIQzf2ZDDIoGjGI1Q4kFk2qOVZeSlJUzCA6YmkCOo6WglQ+je5mrfmYQBa+TCzYPN0IIvQiICw2wC7pCEAEBDsp5cAPCkwMGx3kkJSYpEQAO0BMREQoH4eIHDeUADRAvVwXs7e7tDhCTFCEAOw==',
    'back.gif' => 'data:image/gif;base64,R0lGODlhFAAWAPYAABkLC7y8vHFxccwAAJkAAEVBQfVBQWYAAKejo97e3pkzM4yMjDUfH9MzM6hzc2EoKGZmZkIDA7YAANbW1uVRUebm5tcbG5ZQULUfH+94eMzMzE9PT5mZmfYoKP9aWrW1tTswMDgWFndra40AALoPD+F3dykcHI59fe/v73g4ON8LC+kjI8hJSZWPj4IAACMPD/g4OGNBQeNKSug8PGBTU00kJPBtbcXFxaeMjK2trXwBAZlmZrNERK4AADMAAIN8fP9UVMEJCU47O14AAJ50dMcYGI1hYfIpKfIaGv5LS+AREU4/P/8zM1dXV3IAAPRMTOUwMLR0dFEBAYg2NnRTU/9mZnt7e/QkJKQAAP1CQjsrK/V1dVEsLFUxMYeHh6kFBWYzM7IqKsQQEIyGhmFNTVJMTCgPD8AAANsXF0I1NXxtbekLC/89PekODvE6OllQUK6KisMICNQMDAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUUAHMALAAAAAAUABYAAAf/gHOCg4SFhoYcHDcVjCiHhhMFMWUtEwmMj4MoGlRhD0s5lo6Zmx9CKxhdlZeZc4xjKVckL6ujh4ycDUhfDAiWpAkaHBdHKgdlixWFlyi4H0YwShIhvgmanAuXljkOBtJSEIuCFTc7U1Y36dwlT2hnOmkfE67PFApNImpqURlV7RIutMgjB2cLEAM8WMg46MHDDDE9DoAIhYCIDX9Z2GjcCKXIFwIRwuXKmLGDSVQQsYxwQivAhBZgmJxUIadmD5UrAYgM9oOLhTUDJAjFOcSHCQEIXOLqGUcHgBdQn4KAkEhpM54AwC3wsjVpDqWsXE244cVMk68fXKYTZUusMEUaC+Jqw2ToKlu6hgIBADs=',
];

function run() {
    global $path;
    foreach (glob($path . DIRECTORY_SEPARATOR .'*') as $file) {
		// ignore dotfiles
		if (substr(basename($file), 0, 1) === '.') {
			continue;
		}
        makeRow($file);
    }
}

function makeRow($file) {
    global $img;
    $filename = basename($file) . (is_dir($file) ? '/' : '');
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $size = is_dir($file) ? ' - ' : formatFileSize(filesize($file));
    $date = date('Y-m-d H:i', filemtime($file));
    $time = date('c', filemtime($file));

    $icon = $img['unknown.gif'];
    $alt = '[   ]';
    if (is_dir($file)) {
        $icon = $img['folder.gif'];
        $alt = '[DIR]';
    }
    if (is_file($file)) {
        // if image
        if (in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'bmp'])) {
            $icon = $img['image2.gif'];
            $alt = '[IMG]';
        }
        // if video
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'wma', 'm4a', 'mp4', 'avi', 'mkv', 'wmv', 'mpg', 'mpeg', 'mov', 'flv', '3gp', 'm4v'])) {
            $icon = $img['movie.gif'];
            $alt = '[VID]';
        }
        // if text
        if (in_array($ext, ['txt', 'md', 'markdown', 'php', 'html', 'css', 'js', 'json', 'xml', 'ini', 'sql', 'log', 'htaccess', 'htpasswd', 'htgroup', 'htdigest', 'sh', 'bat', 'cmd', 'c', 'cpp', 'h', 'hpp', 'java', 'py', 'pl', 'rb', 'sh', 'sql', 'yml', 'yaml', 'json', 'toml', 'xml', 'csv', 'tsv', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'pdf', 'epub', 'mobi'])) {
            $icon = $img['text.gif'];
            $alt = '[TXT]';
        }
    }
    $row = <<<HTML
<tr>
<td valign="top"><img src="$icon" alt="$alt"></td>
<td><a href="$filename">$filename</a></td>
<td align="right"><time datetime="$time">$date</time></td>
<td align="right">$size</td>
<td>&nbsp;</td>
</tr>
HTML;
    echo '        '. str_replace(PHP_EOL, '', $row) . PHP_EOL;
}

function formatFileSize($int) {
    if ($int < 1024) {
        return $int . ' B';
    } elseif ($int < 1048576) {
        return round($int / 1024, 2) . ' kB';
    } elseif ($int < 1073741824) {
        return round($int / 1048576, 2) . ' MB';
    } else {
        return round($int / 1073741824, 2) . ' GB';
    }
}

function getAddress() {
    $version = VERSION;
    $os = PHP_OS;
    $php = PHP_VERSION;
    $date = date('Y-m-d H:i:s T');
    $time = date('c');
    $processingTime = number_format((microtime(true) - TIME_START) * 1000, 2);
    return <<<HTML
directory-listing.php/$version <small>($os) PHP/$php compiled at <time datetime="$time">$date</time> in {$processingTime}ms</small>
HTML;
}

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 3.2 Final//EN'>
<html lang="en"><head><title>Index of <?php echo(htmlspecialchars($pathlabel))?></title><meta name="robots" content="noindex"></head><body>
<h1>Index of <?php echo(htmlspecialchars($pathlabel))?></h1>
<table>
    <thead><tr><th valign="top"><img src="<?php echo $img['blank.gif'] ?>" alt="[ICO]"><th>Name</th><th>Last modified</th><th>Size</th><th>Description</th></tr><tr><th colspan="5"><hr></th></tr></thead>
    <tbody>
        <tr><td valign="top"><img src="<?php echo $img['back.gif'] ?>" alt="[PARENTDIR]"></td><td><a href="../">Parent Directory</a></td><td>&nbsp;</td><td align="right"> - </td><td>&nbsp;</td></tr>
<?php run() ?>
    </tbody>
    <tfoot><tr><th colspan="5"><hr></th></tr></tfoot>
</table>
<address><?php echo getAddress() ?></address>
</body></html>
<?php
file_put_contents('index.html', (RUNNING_IN_CONSOLE ? ob_get_clean() : ob_get_flush()));

if (RUNNING_IN_CONSOLE) {
    file_put_contents('php://stdout', 'Done! Finished in ' . number_format((microtime(true) - TIME_START) * 1000, 2) .'ms.' . PHP_EOL);
}
