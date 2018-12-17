let $player;
let $files;

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
			$player
				.hide()
				.get(0)
				.pause()
			;
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

function playFile(file) {

	if ( file.type === 'video') {
		$player.show();
	}

	$player
		.attr('src', file.src)
		.get(0)
		.play()
	;

}