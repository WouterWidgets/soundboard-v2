<div class="modal" id="settings-modal">

    <h3>Info</h3>

    <div>
        Soundboard IP: <code><?=$server_ip;?></code>
        <br>
        Loaded php.ini file: <code><?=php_ini_loaded_file();?></code>
    </div>

	<div class="hidden-remote">
		<br>
		<button type="button" class="form-button" data-action="exit">Exit soundboard</button>
	</div>

	<div class="hidden-local">
		<h3>Add a clip</h3>

		<button type="button" class="form-button" data-action="upload">
			<span class="fa fa-upload"></span>
			Upload a clip
		</button>

		<button type="button" class="form-button" data-action="youtube">
			<span class="fa fa-youtube"></span>
			Add clip from YouTube
		</button>
	</div>

    <hr>

    <button type="button" class="form-button" data-action="modal:hide">Close</button>
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
		<div class="form-group">
			<div class="width-50">
				<label for="speech-speed">Speed</label>
				<input type="range" min="1" max="100" value="50" id="speech-speed" name="speed">
			</div>
			<div class="width-50">
				<label for="speech-pitch">Pitch</label>
				<input type="range" min="1" max="100" value="50" id="speech-pitch" name="pitch">
			</div>
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

<div class="modal" id="upload-modal">
    <div class="loader-spinner">
        <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
    </div>
    <form action="" enctype="multipart/form-data" onsubmit="uploadModalSubmit();return false">
        <p>Upload a media file</p>
        <div class="form-group">
            <label for="upload-file">File (max <?=ini_get('upload_max_filesize');?>B)</label>
            <input type="file" name="upload-file" id="upload-file" required>
        </div>
        <button type="submit" class="form-button">
            Next
        </button>
        <button type="button" class="form-button" data-action="settings">Cancel</button>
    </form>
</div>

<div class="modal" id="youtube-modal">
    <div class="loader-spinner">
        <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
    </div>

    <form action="" onsubmit="youtubeModalSubmit();return false">

        <div class="message" style="display:none"></div>

        <p>Enter a YouTube video ID or YouTube URL</p>
        <div class="form-group">
            <label for="youtube-video-id">Video ID/URL</label>
            <input type="url" name="youtube-video-id" id="youtube-video-id" autofocus>
        </div>
        <button type="submit" class="form-button">
            Next
        </button>
        <button type="button" class="form-button" data-action="settings">Cancel</button>
    </form>
</div>

<div class="modal" id="crop-modal">
    <div class="loader-spinner">
        <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
    </div>
    <form action="" enctype="multipart/form-data" onsubmit="cropModalSubmit();return false">
        <div class="form-group">
            <label for="crop-dir">Folder</label>
            <input type="text" name="crop-dir" id="crop-dir" value="<?=$dir;?>">
        </div>
        <div class="form-group">
            <label for="crop-filename">File name</label>
            <input type="text" name="crop-filename" id="crop-filename" required>
        </div>

        <div class="form-group width-50">

            <label for="crop-youtube-image-id">Image</label>

            <select name="crop-youtube-image-id" id="crop-youtube-image-id">
                <option value="3">Image 1</option>
                <option value="1">Image 2</option>
                <option value="2">Image 3</option>
                <option value="">No image</option>
                <option value="CUSTOM">Custom image</option>
            </select>

			<br>
			<br>

            <div id="crop-image-upload">
                <div class="form-group">
                    <label for="crop-image-upload-file">File (max <?=ini_get('upload_max_filesize');?>B)</label>
                    <input type="file" name="crop-image-upload-file" id="crop-image-upload-file">
                </div>
                <br>
            </div>
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
