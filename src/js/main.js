let $files;
let $speechModal;
let $linkModal;
let $youtubeModal;
let $cropModal;

var currentFile;
var currentVideoID;
var currentAudioPreview;
var cropStart;
var cropEnd;

$(() => {
	$files = $('#files');
	$speechModal = $('#speech-modal');
	$linkModal = $('#link-modal');
	$youtubeModal = $('#youtube-modal');
	$cropModal = $('#crop-modal');

	$(document)
		.on('click', 'button.file', fileClick)
		.on('click', '[data-action]', actionClick)
	;
});

function actionClick() {
	let $button = $(this);

	switch ($button.data('action')) {

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
			$linkModal.show();
			break;

		case 'modal:hide':
			$('.modal').hide();
			break;

		case 'speak':
			$speechModal.show();
			break;

		case 'youtube':
			$('#crop-audio-preview').get(0).pause();
			$cropModal.hide();
			$youtubeModal.show();
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

var youtubeCropXHR = null;

function youtubeModalSubmit() {

	let videoID = $('#youtube-video-id').val();

	$youtubeModal.find('.loader-spinner').show();

	youtubeCropXHR && youtubeCropXHR.abort();

	youtubeCropXHR = $.ajax({
		url: 'youtube.php',
		data: {
			action: 'download',
			videoID: videoID,
		},
		success: (response) => {
			$youtubeModal.find('.loader-spinner').hide();

			if (response.preview) {
				currentVideoID = response.videoID;
				currentAudioPreview = response.preview;

				$youtubeModal.hide();
				$cropModal.show();

				initCrop();
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

	let $imageSelect = $('#crop-image-id');
	let $imagePreview = $('#crop-image-preview');
	$imageSelect.off('change').on('change', () => {
		let imageId = $imageSelect.val();
		$imagePreview.html(
			imageId ? `<img src="https://img.youtube.com/vi/${currentVideoID}/${imageId}.jpg">`
				: ''
		);
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

	let videoID = $('#youtube-video-id').val();

	$cropModal.find('.loader-spinner').show();

	youtubeCropXHR && youtubeCropXHR.abort();

	youtubeCropXHR = $.ajax({
		url: 'youtube.php',
		data: {
			action: 'crop',
			videoID: videoID,
			start: cropStart,
			end: cropEnd,
			dir: $('#crop-dir').val(),
			filename: $('#crop-filename').val(),
			imageId: $('#crop-image-id').val(),
		},
		success: () => {
			$cropModal.find('.loader-spinner').show();
			location.reload();
		},
		error: () => {
			$cropModal.find('.loader-spinner').show();
		}
	});
}

String.prototype.toAudioTime = function() {
	return moment.utc(this * 1000).format('00:mm:ss.SS');
};

String.prototype.toMillisecondTime = function() {
	return moment(this, 'hh:mm:ss.SS').diff(moment().startOf('day'), 'milliseconds') / 1000
};
