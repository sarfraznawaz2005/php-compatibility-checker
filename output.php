<?php

require __DIR__ . '/vendor/autoload.php';

if (isset($_POST['submit'])) {

    define('DS', DIRECTORY_SEPARATOR);
    define('NL', PHP_EOL);

    $type = $_POST['type'];
    $version = $_POST['testVersion'];
    $root = $_POST['root'];
    $folders = getAllFoldersWithCorrectedPaths($root, $_POST['folders'] ?: '.');
    $patterns = $_POST['patterns'];
    $excludedSnifs = parseExcludedSniffs($_POST['excludedSnifs']);
    $listFiles = isset($_POST['list_files']);
    $reportCode = isset($_POST['report_code']) ? '--report=code' : '';
    $showWarnings = isset($_POST['show_warnings']);

    $args = '-ps' . ($listFiles ? 'v' : '') . ($showWarnings ? 'w' : 'n');
    $phpcs = '.' . DS . 'vendor' . DS . 'bin' . DS . 'phpcs';
    $standards = getStandards($type, $version);

    $command = "$phpcs $args $folders -d memory_limit=-1
                $reportCode
                --extensions=php
                --parallel=2
                --standard=$standards
                --runtime-set testVersion $version
                --ignore=$patterns
                --no-cache
                --no-colors
                --report=full
                --report-width=120
                ";

    if ($excludedSnifs) {
        $command .= "--exclude=$excludedSnifs";
    }

    $command = preg_replace('/\s+/', ' ', $command);
    //echo $command;

    header('X-Accel-Buffering: no');
    ini_set('output_buffering', '0');
    ob_end_flush();
    ob_implicit_flush();

    // output
    $proc = popen($command, 'r');

    echo '<style>body {background: #f9f9fa; font-size: 13px; color:#000; line-height: 150%;}"></style>' . NL;
    echo '<pre>' . NL;
    echo "<strong>--STARTED--</strong>" . NL . NL;

    while (!feof($proc)) {
        $response = fixResponse(fread($proc, 4096));

        if ($response) {
            echo $response;
            echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>' . NL;
            @flush();
        }
    }

    echo '<script>setTimeout(function (){window.scrollTo(0, document.body.scrollHeight)}, 1000)</script>' . NL;
    echo "<strong>--FINISHED--</strong>" . NL;
    echo '</pre>' . NL;
}

function getStandards($type, $version)
{
    $path = '.' . DS . 'vendor' . DS;
    $symfonyVersion = (int)str_replace('.', '', $version);

    # order is important
    $standards = [
        'PHPCompatibility' => $path . 'phpcompatibility' . DS . 'php-compatibility' . DS . 'PHPCompatibility',
        'PHPCompatibilitySymfonyPolyfill' => $path . 'phpcompatibility' . DS . 'phpcompatibility-symfony' . DS . 'PHPCompatibilitySymfonyPolyfillPHP',
        'PHPCompatibilityPasswordCompat' => $path . 'phpcompatibility' . DS . 'phpcompatibility-passwordcompat' . DS . 'PHPCompatibilityPasswordCompat',
        'PHPCompatibilityParagonieRandomCompat' => $path . 'phpcompatibility' . DS . 'phpcompatibility-paragonie' . DS . 'PHPCompatibilityParagonieRandomCompat',
        'PHPCompatibilityParagonieSodiumCompat' => $path . 'phpcompatibility' . DS . 'phpcompatibility-paragonie' . DS . 'PHPCompatibilityParagonieSodiumCompat',
        'PHPCompatibilityWP' => $path . 'phpcompatibility' . DS . 'phpcompatibility-wp' . DS . 'PHPCompatibilityWP',
    ];

    if ($type === 'general') {
        unset($standards['PHPCompatibilityWP']);
    }

    if ($symfonyVersion > 80 && $symfonyVersion < 90) {
        // might need to change when symfony has greather than PHP 8 pollyfills
        $standards['PHPCompatibilitySymfonyPolyfill'] .= 80;
    } else {
        $standards['PHPCompatibilitySymfonyPolyfill'] .= $symfonyVersion;
    }

    return implode(',', $standards);
}

function getAllFoldersWithCorrectedPaths($rootPath, $folders)
{
    if ($folders) {
        $scanFolders = [];
        $folderPaths = explode(',', $folders);
        $rootPath = substr($rootPath, -1) === '/' ? $rootPath : $rootPath . '/';

        if (is_array($folderPaths)) {
            foreach ($folderPaths as $folder) {
                $scanFolders[] = $rootPath . trim($folder);
            }

            return implode(' ', $scanFolders);
        }
    }

    return $folders;
}

function parseExcludedSniffs($sniffs)
{
    if ($sniffs) {
        $sniffArray = explode("\n", $sniffs);

        if (is_array($sniffArray)) {
            $value = implode(',', $sniffArray);
            $value = preg_replace('/\s+/', '', $value);

            return trim(rtrim($value, ','));
        }
    }

    return $sniffs;
}

function fixResponse($response)
{
    // do not show ./W/E
    $response = trim(rtrim(ltrim($response, '.EW'), '.EW'));

    // remove un-necessary stuff
    $response = preg_replace('/(DONE.+)/', '', $response);
    $response = preg_replace('/Changing into directory (.+)/', "<br><strong>$1 :</strong>", $response);
    $response = preg_replace('/(\[PHP =>.+)/', '', $response);
    $response = preg_replace('/(\(0 errors.+)/', '', $response);
    $response = preg_replace('/(Creating file list.+)/', "$1<br>", $response);

    // some highlightings
    $response = preg_replace('/(>> .+)/', "<span style='color: red;'>$1</span>", $response);
    $response = preg_replace('/Processing (.+)/', "<span style='color: blue;'>$1</span>", $response);
    $response = preg_replace('/(FILE:.+)/', "<br><strong style='background: #ffff59; padding: 3px;'>$1</strong>", $response);
    $response = preg_replace('/(ERROR(S)?)/', "<span style='color: red;'>$1</span>", $response);

    return trim($response);
}
