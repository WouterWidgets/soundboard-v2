let $files;

let $settingsModal;
let $speechModal;
let $linkModal;
let $uploadModal;
let $youtubeModal;
let $cropModal;

var currentFile;
var currentAddType;
var currentVideoID;
var currentAudioPreview;
var cropStart;
var cropEnd;

$(() => {
	$files = $('#files');
	$settingsModal = $('#settings-modal');
	$speechModal = $('#speech-modal');
	$linkModal = $('#link-modal');
	$uploadModal = $('#upload-modal');
	$youtubeModal = $('#youtube-modal');
	$cropModal = $('#crop-modal');

	if ( $('html').hasClass('local') ) {
		$files.find('button[title]').removeAttr('title');
	}

	$(document)
		.on('click', 'button.file', fileClick)
		.on('click', '[data-action]', actionClick)
	;
});

function actionClick() {
	let $button = $(this);
	let $modals = $('.modal');

	switch ($button.data('action')) {

		case 'settings':
			$modals.hide();
			$settingsModal.show();
			break;

		case 'player:stop':
			stop();
			break;

		case 'scroll:up':
			$files.scrollTop(
				$files.scrollTop() -
				320
			);
			break;

		case 'scroll:down':
			$files.scrollTop(
				$files.scrollTop() +
				320
			);
			break;

		case 'link':
			$modals.hide();
			$linkModal.show();
			break;

		case 'modal:hide':
			$modals.hide();
			break;

		case 'speak':
			$modals.hide();
			$speechModal.show();
			break;

		case 'upload':
			$('#crop-audio-preview').get(0).pause();
			$modals.hide();
			$uploadModal.show();
			break;

		case 'youtube':
			$('#crop-audio-preview').get(0).pause();
			$modals.hide();
			$youtubeModal.show();
			break;

		case 'exit':
			window.close();
			$.ajax({
				url: 'playremote.php',
				type: 'POST',
				data: {
					action: 'exit'
				}
			});
			break;
	}
}

function fileClick() {
	let $button = $(this);
	let file = {
		type: $button.data('type'),
		src: $button.data('src'),
	};

	playFile(file);
}

function playFile(file) {

	currentFile = file;

	$.ajax({
		url: 'playremote.php',
		type: 'POST',
		data: {
			type: file.type,
			src: file.src,
		}
	});
}

function stop() {
	playFile({type: 'stop'});
}

function speak(options) {

	options = options || {};

	let params = {
		lang: options.lang || 'en-US',
		text: options.text,
		speed: options.speed || 0.5,
		pitch: options.pitch || 0.5,
		volume: 1
	};

	let uri = 'https://www.google.com/speech-api/v1/synthesize?ie=UTF-8&';

	playFile({
		type: 'speech',
		src: uri + $.param(params)
	});
}

function speechModalSubmit() {

	let options = {
		text: $('#speech-text').val(),
		lang: $('#speech-lang').val(),
		speed: +$('#speech-speed').val() / 100,
		pitch: +$('#speech-pitch').val() / 100,
	};

	speak(options);
}

function linkModalSubmit() {
	playFile({
		type: 'url',
		src: $('#link-url').val()
	});
}

function uploadModalSubmit() {

	currentAddType = 'MEDIA_UPLOAD';

	$uploadModal.find('.loader-spinner').show();

	const files = $('#upload-file').get(0).files;
	const formData = new FormData();
	formData.append('file', files[0]);
	formData.append('action', 'media-upload');

	$.ajax({
		url: 'editor.php',
		type: 'POST',
		processData: false,
		contentType: false,
		data: formData,
		success: (response) => {
			$uploadModal.find('.loader-spinner').hide();

			if (response.preview) {
				currentVideoID = response.videoID;
				currentAudioPreview = response.preview;

				$uploadModal.hide();
				$cropModal.show();

				initCrop();
			}

		},
		error: () => {
			$uploadModal.find('.loader-spinner').hide();
		}
	});

}

function youtubeModalSubmit() {

	currentAddType = 'YOUTUBE';

	let videoID = $('#youtube-video-id').val();

	$youtubeModal.find('.loader-spinner').show();
	$youtubeModal.find('.message').hide();

	$.ajax({
		url: 'editor.php',
		type: 'POST',
		data: {
			action: 'youtube-download',
			videoID: videoID,
		},
		success: (response) => {
			$youtubeModal.find('.loader-spinner').hide();

			if (response.success ) {
				currentVideoID = response.videoID;
				currentAudioPreview = response.preview;

				$youtubeModal.hide();
				$cropModal.show();

				initCrop();
			} else {
				$youtubeModal
					.find('.message')
					.html('Unable to download/convert this video.')
					.show()
				;
			}

		},
		error: () => {
			$youtubeModal.find('.loader-spinner').hide();
		}
	});

}

function initCrop() {

	$cropModal.find('.loader-spinner').show();

	cropStart = 0;
	cropEnd = 0;

	let $imageUpload = $('#crop-image-upload');
	let $imageUploadFile = $('#crop-image-upload-file');
	let $imageYouTubeSelect = $('#crop-youtube-image-id');
	let $imagePreview = $('#crop-image-preview');

	$imageUpload.hide();
	$imageUploadFile.prop('disabled', currentAddType === 'YOUTUBE');
	$imageYouTubeSelect.hide();

	if ( currentAddType === 'YOUTUBE' ) {
		$cropModal.find('.crop-youtube').show();
		$cropModal.find('.crop-upload').hide();

		$imageYouTubeSelect.show();

		$imageYouTubeSelect.off('change').on('change', () => {
			let youtubeImageId = $imageYouTubeSelect.val();

			$imageUploadFile.prop('disabled', youtubeImageId !== 'CUSTOM');

			if ( youtubeImageId === 'CUSTOM' ) {
				$imageUpload.show();
			} else {
				$imagePreview.html(
					youtubeImageId ? `<img src="https://img.youtube.com/vi/${currentVideoID}/${youtubeImageId}.jpg">`
						: ''
				);
				$imageUpload.hide();
			}
		}).trigger('change');
	} else {
		$cropModal.find('.crop-upload').show();
		$cropModal.find('.crop-youtube').hide();
		$imageUpload.show();
	}

	$imageUploadFile.off('change').on('change', () => {
		let file = $imageUploadFile.get(0).files[0];
		if ( file && $imageUpload.is(':visible') ) {
			$imagePreview.html('<img id="crop-upload-image-preview">');
			let reader = new FileReader();
			reader.onload = function(e) {
				$('#crop-upload-image-preview').attr('src', e.target.result);
			};
			reader.readAsDataURL(file);
		}
	}).trigger('change');

	let $start = $('#crop-start');
	let $end = $('#crop-end');
	let $rangeStart = $('#crop-range-start');
	let $rangeEnd = $('#crop-range-end');

	$start.val('00:00:00.00');
	$end.val('00:00:00.00');

	$rangeStart.val(0);
	$rangeEnd.val(0);

	let $audioPreview = $('#crop-audio-preview');
	let audioPreview = $audioPreview.get(0);

	$start.add($end).off('change').on('change', () => {
		$rangeStart
			.val($start.val().toString().toMillisecondTime())
		;
		$rangeEnd
			.val($end.val().toString().toMillisecondTime())
			.trigger('change')
		;
	});

	$rangeStart.add($rangeEnd).off('change').on('change', () => {

		let duration = audioPreview.duration;

		cropStart = +$rangeStart.val();
		cropEnd = +$rangeEnd.val();

		// cropStart = Math.min(duration, cropStart || 0);
		// cropEnd = Math.min(duration, cropEnd || 100000000);
		//
		// cropStart = Math.min(cropStart, cropEnd);
		// cropEnd = Math.max(cropStart, cropEnd);

		$start.val(`${cropStart.toString().toAudioTime()}`);
		$end.val(`${cropEnd.toString().toAudioTime()}`);

		$rangeStart
			.attr('max', duration - 1)
			.val(cropStart)
		;

		$rangeEnd
			.attr('max', duration)
			.val(cropEnd)
		;

		audioPreview.currentTime = cropStart;
		audioPreview.pause();
	});

	$audioPreview.off('loadedmetadata').on('loadedmetadata', () => {

		if ( !cropEnd ) {
			$rangeEnd
				.attr('max', audioPreview.duration)
				.val(audioPreview.duration)
			;
		}

		$rangeStart.trigger('change');

		$cropModal.find('.loader-spinner').hide();
	});

	$audioPreview.off('timeupdate').on('timeupdate', (e) => {
		let t = audioPreview.currentTime;
		t = Math.max(cropStart, Math.min(cropEnd, t));
		if ( t === cropEnd ) {
			audioPreview.pause();
			t = 0;
		}
		if ( t !== audioPreview.currentTime ) {
			audioPreview.currentTime = t;
		}
	});

	$audioPreview.attr('src', `preview-proxy.php?file=${currentAudioPreview}`);
}

function cropModalSubmit() {
	$cropModal.find('.loader-spinner').show();

	const formData = new FormData();

	formData.append('action', 'crop');

	const files = $('#crop-image-upload-file').get(0).files;
	if ( files.length ) {
		formData.append('image', files[0]);
	}

	let videoID;
	if ( currentAddType === 'YOUTUBE' ) {
		videoID = $('#youtube-video-id').val();
	} else {
		videoID = '';
	}

	formData.append('videoID', videoID);
	formData.append('start', cropStart);
	formData.append('end', cropEnd);
	formData.append('dir', $('#crop-dir').val());
	formData.append('filename', $('#crop-filename').val());
	formData.append('youtubeImageId', $('#crop-youtube-image-id:visible').val() || '');

	$.ajax({
		url: 'editor.php',
		type: 'POST',
		processData: false,
		contentType: false,
		data: formData,
		success: () => {
			location.reload();
		},
		error: () => {
			$cropModal.find('.loader-spinner').hide();
		}
	});
}

String.prototype.toAudioTime = function() {
	return moment.utc(this * 1000).format('00:mm:ss.SS');
};

String.prototype.toMillisecondTime = function() {
	return moment(this, 'hh:mm:ss.SS').diff(moment().startOf('day'), 'milliseconds') / 1000
};
