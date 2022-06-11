let HydeRCState = 1; // 0 = offline, 1 = pinging/unknown, 2 = online

const rcControl = document.getElementById('rc-control');
const rcLink = document.getElementById('rc-link');
const rcStatus = document.getElementById('rc-status');
const rcAction = document.getElementById('rc-action');

document.addEventListener('DOMContentLoaded', function () {
	handleRCStateChange();
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

		rcStatus.innerHTML = '<span><span role="presentation" style="color: red; margin-right: 4px;">&bullet;</span>Offline</span>';
		rcAction.innerText = 'Start';
		rcAction.disabled = false;
	} else if (HydeRCState === 2) {
		document.querySelectorAll('.needs-realtime-compiler').forEach(function (button) {
			button.disabled = false;
			button.setAttribute('title', 'View with Realtime Compiler');
		});

		rcStatus.innerHTML = '<span><span role="presentation" style="color: green; margin-right: 4px;">&bullet;</span>Online</span>';
		rcAction.innerText = 'Stop';
		rcAction.disabled = false;
	} else {
		document.querySelectorAll('.needs-realtime-compiler').forEach(function (button) {
			button.disabled = true;
			button.setAttribute('title', 'Pinging Realtime Compiler...');
		});

		rcStatus.innerHTML = '<span><span role="presentation" style="color: gray; margin-right: 4px;">&bullet;</span>Pinging...</span>';
		rcAction.innerText = '...';
		rcAction.disabled = true;
	}	
}