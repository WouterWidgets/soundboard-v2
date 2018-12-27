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


$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?: 'media-upload';


$response = (object)[
    'action' => $action,
];

$temp = 'temp/' . md5($ip) . '.temp';
$tempPath = __DIR__ . '/' . $temp;

switch ($action) {

    case 'media-upload':

        @unlink($tempPath);
        if ( isset($_FILES['file']) && isset($_FILES['file']['tmp_name']) ) {
            move_uploaded_file($_FILES['file']['tmp_name'], $tempPath);
        }

        $response->preview = $temp;

        break;

    case 'youtube-download':

        $videoID = filter_input(INPUT_POST, 'videoID', FILTER_SANITIZE_STRING) ?: '';
        $videoID = get_youtube_video_ID($videoID);
        $response->videoID = $videoID;

        @unlink($tempPath);
        $cmd = 'youtube-dl -f 140 "' . $videoID . '" -o "' . $tempPath . '"';
        exec($cmd);

        $response->preview = $temp;
        $response->success = file_exists($tempPath);

        break;

    case 'crop':

        $start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_STRING) ?: '';
        $end = filter_input(INPUT_POST, 'end', FILTER_SANITIZE_STRING) ?: '';

        $dir = filter_input(INPUT_POST, 'dir', FILTER_SANITIZE_STRING) ?: 'files';
        $dir = rtrim(str_replace('..', '', $dir), '/');
        if ( strpos($dir, 'files') !== 0 ) {
            $dir = 'files';
        }

        $filename = filter_input(INPUT_POST, 'filename', FILTER_SANITIZE_STRING) ?: md5(time());
        $filename = preg_replace("/[^a-z0-9\_\-\.]/i", '', basename($filename));

        $filePath = __DIR__ . '/' . $dir . '/' . $filename . '.mp3';

        $videoID = filter_input(INPUT_POST, 'videoID', FILTER_SANITIZE_STRING) ?: '';
        $youtubeImageId = filter_input(INPUT_POST, 'youtubeImageId', FILTER_SANITIZE_STRING) ?: '';

        if ( $videoID ) {
            $videoID = get_youtube_video_ID($videoID);

            if ($youtubeImageId && $youtubeImageId !== 'CUSTOM') {
                file_put_contents(
                    str_replace('.mp3', '.jpg', $filePath),
                    file_get_contents('https://img.youtube.com/vi/' . $videoID . '/' . $youtubeImageId . '.jpg')
                );
            }
        }

        if ( !$youtubeImageId || $youtubeImageId === 'CUSTOM' ) {
            if (isset($_FILES['image']) && isset($_FILES['image']['tmp_name'])) {
                move_uploaded_file($_FILES['image']['tmp_name'], str_replace('.mp3', '.jpg', $filePath));
            }
        }

        $cmd = 'ffmpeg -y -i "' . $tempPath . '" ';

        if ($start) {
            $cmd .= '-ss ' . $start . ' ';
        }
        if ($end) {
            $cmd .= '-to ' . $end . ' ';
        }

        $cmd .= '-codec:a libmp3lame "' . $filePath . '"';

        exec($cmd);

        $response->output = $dir . '/' . $filename . '.mp3';
        $response->cmd = $cmd;

        break;

}

header('Content-Type: application/json');
echo json_encode($response);

