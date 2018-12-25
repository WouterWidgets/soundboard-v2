<?php

$ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
$whitelist = [
	'127.0.0.1',
	'::1',
	'192.168.2.123',
];
if ( !in_array($ip, $whitelist) ) {
	die($ip . ' not whitelisted');
}

$page = 'main';

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
