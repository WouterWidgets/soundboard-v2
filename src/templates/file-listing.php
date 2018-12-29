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
            : (is_dir($b) ? 1 : strnatcasecmp($a, $b))
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
            <button class="file clip"
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
