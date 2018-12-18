<video id="player" controls></video>

<div id="files">

    <?php

    $baseDir = __DIR__ . '/../../public_html/';
    $dir = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?: 'files';
    $dirPath = $baseDir . $dir;

    if ($dir !== 'files') {
        ?>
		<button class="file folder"
				onclick="location.href='?dir=<?=dirname($dir);?>'"
		>
			<span class="fa fa-2x fa-level-up"></span>
		</button>
        <?php
    }

    $files = scandir($dirPath);

    foreach ($files as $file) {
        if (strpos($file, '.') === 0) {
            continue;
        }

        $filePath = $dirPath . '/' . $file;
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (is_dir($filePath)) {
            ?>
			<button class="file folder"
					onclick="location.href='?dir=<?= $dir . '/' . $file; ?>'"
			>
				<span class="fa fa-2x fa-folder"></span>
				<span class="label">
					<?=str_replace('_', ' ', $file);?>
				</span>
			</button>
            <?php

        } elseif (!in_array($extension, ['mp3', 'wav', 'mp4'])) {
            continue;

        } else {

            $type = 'audio';
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            if (in_array($extension, ['mp4'])) {
                $type = 'video';
            }
            $image = str_replace($extension, 'jpg', $file);
            if (!file_exists($dirPath . '/' . $image)) {
                $image = str_replace($extension, 'webp', $file);
                if (!file_exists($dirPath . '/' . $image)) {
                    $image = '';
                }
            }

            ?>
			<button class="file"
					style="background-image:url('<?= $image ? ($dir . '/' . rawurlencode($image)) : ''; ?>')"
					data-type="<?= $type; ?>"
					data-src="<?= $dir . '/' . rawurlencode($file); ?>"
			></button>
            <?php
        }
    }
    ?>

</div>

<div id="nav">
	<button class="nav" onclick="location.href='?dir=files'">
		<span class="fa fa-home"></span>
	</button>

	<button class="nav" data-action="scroll:up">
		<span class="fa fa-arrow-up"></span>
	</button>
	<button class="nav" data-action="scroll:down">
		<span class="fa fa-arrow-down"></span>
	</button>

	<button class="nav xhidden-local" data-action="speak">
		<span class="fa fa-bullhorn"></span>
	</button>

	<button class="nav xhidden-local" data-action="url">
		<span class="fa fa-link"></span>
	</button>

	<button class="nav" data-action="player:stop">
		<span class="fa fa-stop"></span>
	</button>

</div>