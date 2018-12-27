<?php

$ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
$server_ip = exec('hostname -I');

$config = json_decode(file_get_contents(
    __DIR__ . '/../config.json'
));

$page = 'main';
if ( $config->whitelist_enabled ) {
    require 'whitelist.php';
}


$templateDir = __DIR__ . '/../src/templates/';
$templatePath = $templateDir . $page . '.php';

if ( file_exists($templatePath) ) {
	ob_start();
    /** @noinspection PhpIncludeInspection */
    require $templatePath;
	$content = ob_get_clean();
}

require $templateDir . 'header.php';
echo $content;
require $templateDir . 'footer.php';
