let $files;
let $speechModal;
let $linkModal;

var currentFile;

$(() => {
	$files = $('#files');
	$speechModal = $('#speech-modal');
	$linkModal = $('#link-modal');

	$(document)
		.on('click', 'button.file', fileClick)
		.on('click', '[data-action]', actionClick)
	;
});

function actionClick() {
	let $button = $(this);

	switch ( $button.data('action') ) {

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
