<?php

if(file_exists('parser.phar')) {
    Phar::unlinkArchive('parser.phar');
}

$phar = new Phar('parser.phar');
$phar->addFile('src/Components/CURL.php', 'Components/CURL.php');
$phar->addFile('src/Components/Li.php', 'Components/Li.php');
$phar->addFile('src/Components/Storage.php', 'Components/Storage.php');
$phar->addFile('src/Components/Site.php', 'Components/Site.php');

$phar->setStub(file_get_contents('src/stub.php'));
