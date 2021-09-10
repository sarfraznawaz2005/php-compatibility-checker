<?php

require __DIR__ . '/vendor/autoload.php';

if (isset($_POST['submit'])) {

    define('DS', DIRECTORY_SEPARATOR);
    define('NL', PHP_EOL);

    $root = $_POST['root'];
    $version = $_POST['testVersion'];
    $patterns = $_POST['patterns'];
    $listFiles = isset($_POST['list_files']);
    $showWarnings = isset($_POST['show_warnings']);
    $reportCode = isset($_POST['report_code']) ? '--report=code' : '';
    $folders = getAllFoldersWithCorrectedPaths($root, $_POST['folders'] ?: '.');
    $excludedSnifs = parseExcludedSniffs($_POST['excludedSnifs']);

    $args = '-ps' . ($listFiles ? 'v' : '') . ($showWarnings ? 'w' : 'n');
    $phpcs = '.' . DS . 'vendor' . DS . 'bin' . DS . 'phpcs';
    $phpCCStandardPath = '.' . DS . 'vendor' . DS . 'phpcompatibility' . DS . 'php-compatibility' . DS . 'PHPCompatibility';

    $command = "$phpcs $args $folders -d memory_limit=-1
                $reportCode
                --extensions=php
                --parallel=2
                --standard=$phpCCStandardPath
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
    //exit($command);

    header('X-Accel-Buffering: no');
    ini_set('output_buffering', '0');
    ob_end_flush();
    ob_implicit_flush();

    // output
    $proc = popen($command, 'r');

    echo '<style>body {background: #f9f9fa; font-size: 13px; color:#000; line-height: 150%;}"></style>' . NL;
    echo '<pre>' . NL;

    while (!feof($proc)) {
        $response = fixResponse(fread($proc, 4096));

        if ($response) {
            echo $response;
            echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>' . NL;
            @flush();
        }
    }

    echo '<script>setTimeout(function (){window.scrollTo(0, document.body.scrollHeight)}, 500)</script>' . NL;
    echo "--FINISHED--" . NL;
    echo '</pre>' . NL;
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
    $response = preg_replace('/(Changing into directory.+)/', '', $response);
    $response = preg_replace('/(\[PHP =>.+)/', '', $response);
    $response = preg_replace('/(\(0 errors.+)/', '', $response);
    $response = preg_replace('/(Creating file list.+)/', "$1<br>", $response);
    $response = preg_replace('/\r\n/', '', $response);

    // some highlightings
    $response = preg_replace('/(>> .+)/', "<span style='color: red;'>$1</span>", $response);
    $response = preg_replace('/(Processing.+)/', "<span style='color: blue;'>$1</span>", $response);
    $response = preg_replace('/(FILE:.+)/', "<br><strong style='background: yellow; padding: 3px;'>$1</strong>", $response);
    $response = preg_replace('/(ERROR(S)?)/', "<span style='color: red;'>$1</span>", $response);

    return trim($response);
}
