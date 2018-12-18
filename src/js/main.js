let $player;
let $files;

var currentFile;

$(() => {
	$player = $('#player');
	$files = $('#files');

	$player.on('ended', () => {
		$player.hide();
	});

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

function shouldPlayRemote(file) {
	return (
		!IS_LOCAL ||
		!~[
			'audio',
			'video',
			'speech',
		].indexOf((file || currentFile).type)
	);
}

function playFile(file) {

	currentFile = file;

	if ( shouldPlayRemote(file) ) {
		playRemote(file);
		return;
	}

	if ( file.type === 'video') {
		$player.show();
	}

	$player
		.attr('src', file.src)
		.get(0)
		.play()
	;

}

function playRemote(file) {
	$.ajax({
		url: 'playremote.php',
		data: {
			type: file.type,
			src: file.src,
		}
	});
}

function stop() {
	if ( shouldPlayRemote(currentFile) ) {
		stopRemote();
	}
	$player
		.hide()
		.get(0)
		.pause()
	;
}

function stopRemote() {
	playRemote({type: 'stop'});
}

function speak(text) {
	speechSynthesis.speak(new SpeechSynthesisUtterance(text));
}