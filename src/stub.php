<?php
spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require 'phar://'. __FILE__ . DIRECTORY_SEPARATOR .  DIRECTORY_SEPARATOR . $fileName;
});

use Components\CURL;
use Components\Li;
use Components\Storage;
use Components\Site;

$config = parse_ini_file('config.ini');

if (empty($argv[1])) {
    $argv[1] = '';
}

switch ($argv[1]) {
    case 'parse':
        //парсим ли
        $storage = new Storage($config['infile'], true);
        $li = new Li($config['url'], $config['minHits']);

        foreach ($li->sites as $site) {
            echo $site."\n";
            $storage->add($site);
        }
        break;
    case 'check':
        //ищем нужные домены среди редиректов
        $storage = new Storage($config['infile']);
        $out = new Storage($config['outfile']);

        foreach ($storage->getList() as $url) {
            if (Site::check($url, $config['domains'])) {
                echo $url."\n";
                $out->add($url);
            }
        }
        break;
    default:
        echo "Hi!\n";
        break;
}

__HALT_COMPILER();
