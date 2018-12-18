<?php
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$isLocal = in_array($ip, ['127.0.0.1', '::1']);
?>

<!DOCTYPE html>
<html lang="en" class="<?=($isLocal ? 'local' : 'remote');?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,height=device-height,user-scalable=no">
    <title>Soundboard</title>
    <link rel="stylesheet" href="css/main.min.css">
	<script>
		// noinspection JSAnnotator
		const IS_LOCAL = <?=($isLocal ? 'true' : 'false');?>;
	</script>
</head>
<body>