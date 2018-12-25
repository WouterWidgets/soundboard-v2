<?php

$ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

$config = json_decode(file_get_contents(
    __DIR__ . '/../config.json'
));

if ($config->whitelist_enabled) {
    require 'whitelist.php';
}


function get_youtube_video_ID($url)
{
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return $match[1] ?: $url;
}


$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?: 'download';
$videoID = filter_input(INPUT_GET, 'videoID', FILTER_SANITIZE_STRING) ?: '';
$videoID = get_youtube_video_ID($videoID);

$response = (object)[
    'action' => $action,
    'videoID' => $videoID
];

$temp = 'temp/' . md5($ip) . '.m4a';
$tempPath = __DIR__ . '/' . $temp;

switch ($action) {

    case 'download':

        @unlink($tempPath);
        $cmd = 'youtube-dl -f 140 "' . $videoID . '" -o "' . $tempPath . '"';
        exec($cmd);

        $response->preview = $temp;

        break;

    case 'crop':

        $start = filter_input(INPUT_GET, 'start', FILTER_SANITIZE_STRING) ?: '';
        $end = filter_input(INPUT_GET, 'end', FILTER_SANITIZE_STRING) ?: '';

        $dir = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?: 'files';
        $dir = rtrim(str_replace('..', '', $dir), '/');
        if ( strpos($dir, 'files') !== 0 ) {
            $dir = 'files';
        }

        $filename = filter_input(INPUT_GET, 'filename', FILTER_SANITIZE_STRING) ?: md5(time());
        $imageId = filter_input(INPUT_GET, 'imageId', FILTER_SANITIZE_STRING) ?: '3';

        $filePath = __DIR__ . '/' . $dir . '/' . $filename . '.m4a';

        if ($imageId) {
            file_put_contents(
                str_replace('.m4a', '.jpg', $filePath),
                file_get_contents('https://img.youtube.com/vi/' . $videoID . '/'. $imageId .'.jpg')
            );
        }

        $cmd = 'ffmpeg -y -i "' . $tempPath . '" ';

        if ($start) {
            $cmd .= '-ss ' . $start . ' ';
        }
        if ($end) {
            $cmd .= '-to ' . $end . ' ';
        }

        $cmd .= '-c copy "' . $filePath . '"';

        exec($cmd);

        $response->output = $dir . '/' . $filename . '.m4a';

        break;

}

header('Content-Type: application/json');
echo json_encode($response);

