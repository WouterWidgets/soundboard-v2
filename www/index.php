<?php

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