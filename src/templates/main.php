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

	<button class="nav xhidden-local" data-action="link">
		<span class="fa fa-link"></span>
	</button>

	<button class="nav" data-action="player:stop">
		<span class="fa fa-stop"></span>
	</button>

</div>

<video id="player" controls></video>

<div class="modal" id="speech-modal">
	<form action="" onsubmit="speechModalSubmit();return false">
		<div class="form-group">
			<label for="speech-text">Text</label>
			<textarea name="text" id="speech-text" cols="30" rows="2"></textarea>
		</div>
		<div class="form-group">
			<label for="speech-lang">Language</label>
			<select name="lang" id="speech-lang">
				<option value="en-US">English (US)</option>
				<option value="en-GB">English (UK)</option>
				<option value="de-DE">German</option>
				<option value="fr-FR">French</option>
				<option value="nl-NL">Dutch</option>
				<option value="nl-BE">Vlamish</option>
			</select>
		</div>
		<div class="form-group width-50">
			<label for="speech-speed">Speed</label>
			<input type="range" min="1" max="100" value="50" id="speech-speed" name="speed">
		</div>
		<div class="form-group width-50">
			<label for="speech-pitch">Pitch</label>
			<input type="range" min="1" max="100" value="50" id="speech-pitch" name="pitch">
		</div>
		<button type="submit" class="form-button">Say it</button>
		<button type="button" class="form-button" data-action="modal:hide">Back</button>
	</form>
</div>

<div class="modal" id="link-modal">
	<form action="" onsubmit="linkModalSubmit();return false">
		<p>Enter an audio, video, stream or YouTube URL</p>
		<div class="form-group">
			<label for="link-url">URL</label>
			<input type="url" name="link-url" id="link-url">
		</div>
		<button type="submit" class="form-button">Play</button>
		<button type="button" class="form-button" data-action="modal:hide">Back</button>
	</form>
</div>