<?php
header('Content-Type: text/plain; charset=UTF-8');

echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? '') . PHP_EOL;
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . PHP_EOL;
echo "PWD: " . getcwd() . PHP_EOL;
echo "open_basedir: " . (ini_get('open_basedir') ?: '(empty)') . PHP_EOL;
echo "PHP_VERSION: " . PHP_VERSION . PHP_EOL;
