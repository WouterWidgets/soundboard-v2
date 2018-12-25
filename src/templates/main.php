<script>
	var PLAYER_CONFIG = <?=json_encode($config->player);?>;
</script>
<div id="files">

    <?php

	function getImage($file, $dirPath, $extension = null) {
		if ( !$extension ) {
			$extension = 'folder';
			$file = $file . '.folder';
        }
		$image = str_replace($extension, 'jpg', $file);

        if (!file_exists($dirPath . '/' . $image)) {
            $image = str_replace($extension, 'png', $file);
            if (!file_exists($dirPath . '/' . $image)) {
                $image = str_replace($extension, 'webp', $file);
                if (!file_exists($dirPath . '/' . $image)) {
                    $image = '';
                }
            }
        }
        return $image;
	}

	function getLabel($file, $extension = '') {
		if ( $extension ) {
			$extension = '.' . $extension;
		}
        return str_replace([
            '_', '-', $extension
        ], [
            ' ', ' ', ''
        ], $file);
	}

    $baseDir = __DIR__ . '/../../public_html/';
    $dir = filter_input(INPUT_GET, 'dir', FILTER_SANITIZE_STRING) ?: 'files';
    $dir = rtrim(str_replace('..', '', $dir), '/');
    if ( strpos($dir, 'files') !== 0 ) {
    	$dir = 'files';
	}
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

    $ignored = ['.', '..', '.DS_Store', 'Thumbs.db'];
	$files = array_diff (scandir($dir), $ignored);

	usort($files, function($a, $b) use ($dirPath) {
		$a = $dirPath . '/' . $a;
		$b = $dirPath . '/' . $b;
		return is_dir($a)
			? (is_dir($b) ? strnatcasecmp($a, $b) : -1)
			: (is_dir($b) ? 1 : (
			strcasecmp(pathinfo($a, PATHINFO_EXTENSION), pathinfo($b, PATHINFO_EXTENSION)) == 0
				? strnatcasecmp($a, $b)
				: strcasecmp(pathinfo($a, PATHINFO_EXTENSION), pathinfo($b, PATHINFO_EXTENSION))
			))
		;
	});

    foreach ($files as $file) {
        if (strpos($file, '.') === 0) {
            continue;
        }

        $filePath = $dirPath . '/' . $file;
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (is_dir($filePath)) {

            $image = getImage($file, $dirPath);
            $label = getLabel($file);

            ?>
			<button class="file folder"
					style="background-image:url('<?= $image ? ($dir . '/' . rawurlencode($image)) : ''; ?>')"
					onclick="location.href='?dir=<?= $dir . '/' . $file; ?>'"
					title="<?=addslashes($label);?>"
			>
				<span class="fa fa-2x fa-folder"></span>
				<?php
				if ( !$image ) {
                    ?>
					<span class="label">
						<?=$label;?>
					</span>
                    <?php
                }
				?>
			</button>
            <?php

        } elseif (!in_array($extension, $config->player->extensions)) {
            continue;

        } else {

            $type = 'audio';
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            if (in_array($extension, ['mp4', 'm4v'])) {
                $type = 'video';
            }
            $image = getImage($file, $dirPath, $extension);
            $label = getLabel($file, $extension);

            ?>
			<button class="file"
					style="background-image:url('<?= $image ? ($dir . '/' . rawurlencode($image)) : ''; ?>')"
					data-type="<?= $type; ?>"
					data-src="<?= $dir . '/' . rawurlencode($file); ?>"
					title="<?=addslashes($label);?>"
			>
				<?php
				if ( !$image ) {
                    ?>
					<span class="label">
						<?=$label;?>
					</span>
					<?php
                }
					?>
			</button>
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

	<button class="nav hidden-local" data-action="speak">
		<span class="fa fa-bullhorn"></span>
	</button>

	<button class="nav hidden-local" data-action="link">
		<span class="fa fa-link"></span>
	</button>

	<button class="nav hidden-local" data-action="youtube">
		<span class="fa fa-youtube"></span>
	</button>

	<button class="nav" data-action="player:stop">
		<span class="fa fa-stop"></span>
	</button>

</div>

<div class="modal" id="speech-modal">
	<form action="" onsubmit="speechModalSubmit();return false">
		<div class="form-group">
			<label for="speech-text">Text</label>
			<textarea name="text" id="speech-text" cols="30" rows="2" autofocus></textarea>
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
			<input type="url" name="link-url" id="link-url" autofocus>
		</div>
		<button type="submit" class="form-button">Play</button>
		<button type="button" class="form-button" data-action="modal:hide">Back</button>
	</form>
</div>

<div class="modal" id="youtube-modal">
	<div class="loader-spinner">
		<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
	</div>
	<form action="" onsubmit="youtubeModalSubmit();return false">
		<p>Enter a YouTube video ID or YouTube URL</p>
		<div class="form-group">
			<label for="youtube-video-id">Video ID/URL</label>
			<input type="url" name="youtube-video-id" id="youtube-video-id" autofocus>
		</div>
		<button type="submit" class="form-button">
			Next
		</button>
		<button type="button" class="form-button" data-action="modal:hide">Cancel</button>
	</form>
</div>

<div class="modal" id="crop-modal">
	<div class="loader-spinner">
		<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
	</div>
	<form action="" onsubmit="cropModalSubmit();return false">
		<div class="form-group">
			<label for="crop-dir">Folder</label>
			<input type="text" name="crop-dir" id="crop-dir" value="<?=$dir;?>">
		</div>
		<div class="form-group">
			<label for="crop-filename">File name</label>
			<input type="text" name="crop-filename" id="crop-filename" required>
		</div>

		<div class="form-group width-50">
			<label for="crop-image-id">Image</label>
			<select name="crop-image-id" id="crop-image-id">
				<option value="3">Image 1</option>
				<option value="1">Image 2</option>
				<option value="2">Image 3</option>
				<option value="">No image</option>
			</select>
		</div>
		<div id="crop-image-preview" class="width-50"></div>

		<div class="form-group width-50 clearfix">
			<label for="crop-start">Start time</label>
			<input type="text" name="crop-start" id="crop-start">
		</div>
		<div class="form-group width-50">
			<label for="crop-end">End time</label>
			<input type="text" name="crop-end" id="crop-end">
		</div>

		<br class="clearfix">
		<br class="clearfix">

		<section class="range-slider">
			<input type="range" name="crop-range-start" id="crop-range-start" value="5" min="0" max="10" step="0.01">
			<input type="range" name="crop-range-end" id="crop-range-end" value="5" min="1" max="10" step="0.01">
		</section>

		<br>

		<div class="form-group">
			<audio controls id="crop-audio-preview"></audio>
		</div>

		<button type="submit" class="form-button">Save</button>
		<button type="button" class="form-button" data-action="youtube">Back</button>
	</form>
</div>
