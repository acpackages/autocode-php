<?php

if ($argc < 2) {
    die("Missing file path argument\n");
}

$filePath = $argv[1];

$handle = fopen("php://stdin", "r");

if (!$handle) {
    die("Failed to open STDIN\n");
}

while (true) {
    $content = fgets($handle);
    if ($content === false) {
        continue;
    }

    if (trim($content) === 'exit') {
        break;
    }

    file_put_contents($filePath, $content, FILE_APPEND | LOCK_EX);
}

fclose($handle);
