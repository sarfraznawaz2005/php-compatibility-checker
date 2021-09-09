<?php

require __DIR__ . '/vendor/autoload.php';

if (isset($_POST['submit'])) {

    define('DS', DIRECTORY_SEPARATOR);
    define('NL', PHP_EOL);

    $folders = getAllFoldersWithCorrectedPaths($_POST['folders'] ?: '.');
    $version = $_POST['testVersion'];
    $patterns = $_POST['patterns'];
    $listFiles = isset($_POST['list_files']);
    $showWarnings = isset($_POST['show_warnings']);
    $reportCode = isset($_POST['report_code']) ? '--report=code' : '';

    $args = '-ps' . ($listFiles ? 'v' : '') . ($showWarnings ? 'w' : 'n');
    $phpcs = '.' . DS . 'vendor' . DS . 'bin' . DS . 'phpcs';
    $phpCCStandardPath = '.' . DS . 'vendor' . DS . 'phpcompatibility' . DS . 'php-compatibility' . DS . 'PHPCompatibility';

    $command = "$phpcs $args $folders -d memory_limit=-1
                $reportCode
                --no-cache
                --report-width=120
                --extensions=php
                --standard=$phpCCStandardPath
                --runtime-set testVersion $version
                --ignore=$patterns
                ";

    $command = preg_replace('/\s+/', ' ', $command);
    //echo $command . '<hr>';exit;

    header('X-Accel-Buffering: no');
    ini_set('output_buffering', '0');
    ob_end_flush();
    ob_implicit_flush();

    // output
    $proc = popen($command, 'r');

    echo '<style>body {background: #000; font-size: 13px; color:#44D544;}"></style>' . NL;
    echo '<pre>' . NL;

    while (!feof($proc)) {
        echo fread($proc, 4096) . NL;
        echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>' . NL;
        @flush();
    }

    echo '<script>window.onload = function () {window.scrollTo(0, document.body.scrollHeight);}</script>' . NL;
    echo "--FINISHED--" . NL;
    echo '</pre>' . NL;
}

function getAllFoldersWithCorrectedPaths($folders)
{
    // one folder back from root path

    //$rootPath = dirname($_SERVER['DOCUMENT_ROOT']) . DS;
    // use above line instead of below if running from folder other than php checker eg hosting
    $rootPath = '';

    $scanFolders = [];
    $folderPaths = explode(' ', $folders);

    if (is_array($folderPaths)) {
        foreach ($folderPaths as $folder) {
            $scanFolders[] = $rootPath . $folder;
        }

        return implode(' ', $scanFolders);
    }

    return $folders;
}
