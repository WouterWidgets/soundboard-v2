<?php

$ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

$config = json_decode(file_get_contents(
    __DIR__ . '/../config.json'
));

if ( $config->whitelist_enabled ) {
    require 'whitelist.php';
}

$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING) ?: 'audio';
$src = filter_input(INPUT_POST, 'src', FILTER_SANITIZE_STRING);

$src = urldecode($src);

if ( !$src || $config->player->stop_before_play ) {
    exec('pkill vlc');
    if ( !$src ) {
        exit;
    }
}

if ( strpos($src, 'http') === 0 ) {
    $path = $src;
} else {
    $path = __DIR__ . '/' . $src;
}

if ( $config->log_enabled ) {
    $log = date('Y-m-d H:i:s') . ' ' . $ip . ' Playing ' . $path . PHP_EOL;
    file_put_contents(__DIR__ . '/../log/soundboard.log', $log, FILE_APPEND);
}

$cmd = 'cvlc "' . $path . '" --play-and-exit --no-video > /dev/null &';

exec($cmd);

header('Content-Type: application/json');
echo json_encode((object)[
    'cmd' => $cmd
]);
