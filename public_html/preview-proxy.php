<?php
$file = filter_input(INPUT_GET, 'file', FILTER_SANITIZE_STRING) ?: '';

$path = __DIR__ . '/' . $file;

if ( file_exists($path) ) {
    header('Content-Type: audio/m4a');
    header('Cache-Control: no-cache');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($path));
    header('Accept-Ranges: bytes');
    header('Content-Disposition: inline; filename="'.basename($path).'"');
    readfile($path);
}
