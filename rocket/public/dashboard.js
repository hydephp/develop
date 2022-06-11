let HydeRCState = 1; // 0 = offline, 1 = pinging/unknown, 2 = online

document.addEventListener('DOMContentLoaded', function () {
	pingRC();
	setInterval(pingRC, 30000);
});

function pingRC() {
	fetch('/api/ping-realtime-compiler')
	.then(response => response.json())
	.then(data => {
		if (data.success === true) {
			HydeRCState = 2;
			handleRCStateChange();
		} else {
			HydeRCState = 0;
			handleRCStateChange();
		}
	});
}

function handleRCStateChange() {
	if (HydeRCState === 0) {
		document.querySelectorAll('.needs-realtime-compiler').forEach(function (button) {
			button.disabled = true;
			button.setAttribute('title', 'Realtime Compiler is not running');
		});
	} else if (HydeRCState === 2) {
		document.querySelectorAll('.needs-realtime-compiler').forEach(function (button) {
			button.disabled = false;
			button.setAttribute('title', 'View with Realtime Compiler');
		});
	} else {
		document.querySelectorAll('.needs-realtime-compiler').forEach(function (button) {
			button.disabled = true;
			button.setAttribute('title', 'Pinging Realtime Compiler...');
		});
	}	
}