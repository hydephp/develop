<button id="searchMenuButton" onclick="toggleSearchMenu()" aria-label="Toggle search menu">
	Search 
	<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" role="presentation"><path d="M0 0h24v24H0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
</button>
<button id="searchMenuButtonMobile" onclick="toggleSearchMenu()" aria-label="Toggle search menu">
	<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" role="presentation"><path d="M0 0h24v24H0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
</button>
@push('scripts')
	
<dialog id="searchMenu" class="prose dark:prose-invert bg-gray-100 dark:bg-gray-800 fixed">
	@include('hyde::components.docs.search-input')
	<footer>
		<small>
			Press <code><kbd title="Forward slash">/</kbd></code> to open search window.
			Use <code><kbd title="Escape key">esc</kbd></code> to close.
		</small>
	</footer>
</dialog>

<script>
	
if (typeof HTMLDialogElement === 'function') {
	const searchMenu = document.getElementById('searchMenu');

	function toggleSearchMenu() {
		if (searchMenu.hasAttribute('open')) {
			closeSearchMenu();
		} else {
			openSearchMenu();
		}
	}

	function closeSearchMenu() {
		searchMenu.removeAttribute('open');

		document.getElementById('searchMenuBackdrop').remove();
		document.getElementById('searchMenuCloseButton').remove();

		document.getElementById('searchMenuButton').style.visibility = 'visible';
	}

	function openSearchMenu() {
		searchMenu.setAttribute('open', '');

		createBackdrop();
		createCloseButton();
		document.getElementById('searchMenuButton').style.visibility = 'hidden';

		document.getElementById('search-input').focus();

		function createBackdrop() {
			const backdrop = document.createElement('div');
			backdrop.id = 'searchMenuBackdrop';
			backdrop.classList.add('backdrop', 'active');
			backdrop.addEventListener('click', () => {
				closeSearchMenu();
			});
			document.body.appendChild(backdrop);
		}

		function createCloseButton() {
			const closeButton = document.createElement('button');
			closeButton.id = 'searchMenuCloseButton';
			closeButton.classList.add('fixed');
			closeButton.setAttribute('aria-label', 'Close search menu');
			closeButton.addEventListener('click', () => {
				closeSearchMenu();
			});
			closeButton.innerHTML = `<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
					<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
				</svg>`;

			document.body.appendChild(closeButton);
		}
	}

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && searchMenu.hasAttribute('open')) {
			closeSearchMenu();
		}
	});

	document.addEventListener('keypress', (e) => {
		if (e.key === '/' && !searchMenu.hasAttribute('open')) {
			e.preventDefault();
			openSearchMenu();
		}
	});

	} else {
		// The browser does not support the <dialog> element
		document.getElementById('searchMenu').remove();
		
		function toggleSearchMenu() {
			window.location.href = 'search.html';
		}
	}
</script>
@endpush