document.addEventListener('DOMContentLoaded', function () {
	pingRC();
	setInterval(pingRC, 30000);
});

function pingRC() {
	fetch('/api/ping-realtime-compiler')
	.then(response => response.json())
	.then(data => {
		if (data.success === true) {
			document.querySelectorAll('.needs-realtime-compiler').forEach(function (button) {
				button.disabled = false;
			});
		} else {
			document.querySelectorAll('.needs-realtime-compiler').forEach(function (button) {
				button.disabled = true;
			});
		}
	});
}