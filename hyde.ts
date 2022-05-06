/**
 * Core Scripts for the HydePHP Frontend
 *
 * @package     HydePHP - HydeFront
 * @version     v1.7.x (HydeFront)
 * @author      Caen De Silva
 */

const mainNavigationLinks: HTMLElement = document.getElementById("main-navigation-links");
const openMainNavigationMenuIcon: HTMLElement = document.getElementById("open-main-navigation-menu-icon");
const closeMainNavigationMenuIcon: HTMLElement = document.getElementById("close-main-navigation-menu-icon");
/**
 * @deprecated
 */
const themeToggleButton: HTMLElement = document.getElementById("theme-toggle-button");
const navigationToggleButton: HTMLElement = document.getElementById("navigation-toggle-button");

let navigationOpen: boolean = false;

const themeToggleButtons: NodeListOf<HTMLElement> = document.querySelectorAll(".theme-toggle-button");


function toggleNavigation(): void {
    if (navigationOpen) {
        hideNavigation();
    } else {
        showNavigation();
    }
}

function showNavigation(): void {
    mainNavigationLinks.classList.remove("hidden");
    openMainNavigationMenuIcon.style.display = "none";
    closeMainNavigationMenuIcon.style.display = "block";

    navigationOpen = true;
}

function hideNavigation(): void {
    mainNavigationLinks.classList.add("hidden");
    openMainNavigationMenuIcon.style.display = "block";
    closeMainNavigationMenuIcon.style.display = "none";
    navigationOpen = false;
}

// Handle the theme switching

function toggleTheme(): void {
    if (isSelectedThemeDark()) {
        setThemeToLight();
    } else {
        setThemeToDark();
    }

    function isSelectedThemeDark(): boolean {
        return localStorage.getItem('color-theme') === 'dark' || !('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function setThemeToDark(): void {
        document.documentElement.classList.add("dark");
        localStorage.setItem('color-theme', 'dark');
    }

    function setThemeToLight(): void {
        document.documentElement.classList.remove("dark");
        localStorage.setItem('color-theme', 'light');
    }
}

// Register onclick event listener for themeToggleButtons
themeToggleButtons.forEach((button) => {
    button.addEventListener("click", toggleTheme);
});

// Register onclick event listener for navigation toggle button if it exists
if (navigationToggleButton) {
    navigationToggleButton.onclick = toggleNavigation;
}


/**
 * Lagrafo Frontend Scripts
 * @version v0.2.0-beta
 */

let sidebarOpen:boolean = false;

const sidebarToggleButton = document.getElementById('sidebar-toggle') as HTMLButtonElement;
const sidebar = document.getElementById('sidebar') as HTMLDivElement;
const backdrop:HTMLDivElement = document.createElement('div');

function toggleSidebar() {
    sidebarOpen ? closeSidebar() : openSidebar();

    function openSidebar() {
        sidebarOpen = true;
        sidebar.classList.add('active');
        sidebarToggleButton.classList.add('active');
        createBackdropElement();
    }

    function closeSidebar() {
        sidebarOpen = false;
        sidebar.classList.remove('active');
        sidebarToggleButton.classList.remove('active');
        removeBackdropElement();
    }

    function createBackdropElement() {
        backdrop.id = 'sidebar-backdrop';
        backdrop.title = 'Click to close sidebar';
        backdrop.classList.add('backdrop');
        backdrop.classList.add('active');

        backdrop.addEventListener('click', closeSidebar);
        document.body.appendChild(backdrop);

        document.getElementById('content').classList.add('sidebar-active');
    }

    function removeBackdropElement() {
        if (backdrop.parentNode) {
            backdrop.parentNode.removeChild(backdrop);
        }
        document.getElementById('content').classList.remove('sidebar-active');

    }

}

// On click of sidebar toggle button
sidebarToggleButton.addEventListener('click', function () {
    toggleSidebar();
});

// If sidebar is open, close it on escape key press
document.addEventListener('keydown', function (e) {
    if (sidebarOpen && e.key === 'Escape') {
        toggleSidebar();
    }
});
