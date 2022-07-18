/**
 * Provides a search window modal for HydePHP documentation pages.
 *
 * @package     HydePHP - HydeFront/HydeSearchWindow
 * @author      Caen De Silva
 */

let HTMLDialogElement;
let toggleSearchMenu;
if (typeof HTMLDialogElement !== 'function') {
    // If the browser does not support the <dialog> element we'll redirect to the search page.
    document.getElementById('searchMenu').remove();

    toggleSearchMenu = () => {
        window.location.href = 'search.html';
    };
} else {
    const searchMenu = document.getElementById('searchMenu');

    toggleSearchMenu = () => {
        if (searchMenu.hasAttribute('open')) {
            closeSearchMenu();
        } else {
            openSearchMenu();
        }
    };

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

}
