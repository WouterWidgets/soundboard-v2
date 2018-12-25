<?php

if (!in_array($ip, $config->whitelist)) {

    if (isset($page)) {
        $page = 'not-whitelisted';
    } else {
        header('Content-Type: application/json');
        echo json_encode((object)[
            'error' => 'Your IP address ' . $ip . ' is not whitelisted.'
        ]);
        exit;
    }
}
