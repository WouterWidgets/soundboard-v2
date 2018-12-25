<?php

$ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

$config = json_decode(file_get_contents(
    __DIR__ . '/../config.json'
));

if ($config->whitelist_enabled) {
    require 'whitelist.php';
}


function get_youtube_video_ID($youtube_video_url)
{
    $pattern =
        '%                 
    (?:youtube                    # Match any youtube url www or no www , https or no https
    (?:-nocookie)?\.com/          # allows for the nocookie version too.
    (?:[^/]+/.+/                  # Once we have that, find the slashes
    |(?:v|e(?:mbed)?)/|.*[?&]v=)  # Check if its a video or if embed 
    |youtu\.be/)                  # Allow short URLs
    ([^"&?/ ]{11})                # Once its found check that its 11 chars.
    %i';
    // Checks if it matches a pattern and returns the value
    if (preg_match($pattern, $youtube_video_url, $match)) {
        return $match[1];
    }

    // if no match return false.
    return $youtube_video_url;
}


$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?: 'download';
$videoID = filter_input(INPUT_GET, 'videoID', FILTER_SANITIZE_STRING) ?: '';
$videoID = get_youtube_video_ID($videoID);

$response = (object)[
    'action' => $action,
    'videoID' => $videoID
];

$temp = 'temp/' . md5($ip) . '.m4a';
$tempPath = __DIR__ . '/../' . $temp;

switch ($action) {

    case 'download':

        $cmd = 'youtube-dl -f 140 "' . $videoID . '" -o "' . $tempPath . '"';
        exec($cmd);

        $response->preview = $temp;

        break;

    case 'crop':

//        $start = '00:00:38.00';
//        $end = '00:00:53.00';
        $start = filter_input(INPUT_GET, 'start', FILTER_SANITIZE_STRING) ?: '';
        $end = filter_input(INPUT_GET, 'end', FILTER_SANITIZE_STRING) ?: '';

        $dir = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?: '';
        $filename = filter_input(INPUT_GET, 'filename', FILTER_SANITIZE_STRING) ?: md5(time());

        $filePath = __DIR__ . '/files/' . $dir . '/' . $filename . '.m4a';

        $imgId = '';
        if ($imgId) {
            file_put_contents(
                str_replace('.m4a', '.jpg', $filePath),
                file_get_contents('https://img.youtube.com/vi/' . $videoID . '/0.jpg')
            );
        }

        $cmd = 'ffmpeg -i "' . $tempPath . '" ';

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

