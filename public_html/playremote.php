<?php

$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?: 'audio';
$src = filter_input(INPUT_GET, 'src', FILTER_SANITIZE_STRING);
if ( !$src ) {
    exec('pkill vlc');
    exit;
}

if ( strpos($src, 'http') === 0 ) {
    $path = $src;
} else {
    $path = __DIR__ . '/' . $src;
}


$cmd = 'cvlc "' . $path . '" --play-and-exit --no-video > /dev/null &';

exec($cmd);

header('Content-Length: ' . strlen($cmd));
header('Content-Type: text/plain');
echo $cmd;