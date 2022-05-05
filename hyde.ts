/**
 * Core Scripts for the HydePHP Frontend
 *
 * @package     HydePHP - HydeFront
 * @version     v1.6.x (HydeFront)
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
const sidebarToggleButton: HTMLElement = document.getElementById("sidebar-toggle-button");

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

// Handle the documentation page sidebar (@deprecated in favour of Lagrafo)

let sidebarOpen: boolean = screen.width >= 768;

const sidebar: HTMLElement = document.getElementById("documentation-sidebar");
const backdrop: HTMLElement = document.getElementById("sidebar-backdrop");

/**
 * @deprecated use Lagrafo instead
 */
const toggleButtons: NodeListOf<HTMLElement> = document.querySelectorAll(".sidebar-button-wrapper");

/**
 * @deprecated use Lagrafo instead
 */
function toggleSidebar(): void {
    if (sidebarOpen) {
        hideSidebar();
    } else {
        showSidebar();
    }
}

/**
 * @deprecated use Lagrafo instead
 */
function showSidebar(): void {
    sidebar.classList.remove("hidden");
    sidebar.classList.add("flex");
    backdrop.classList.remove("hidden");
    document.getElementById("app").style.overflow = "hidden";

    toggleButtons.forEach((button) => {
        button.classList.remove("open");
        button.classList.add("closed");
    });

    sidebarOpen = true;
}

/**
 * @deprecated use Lagrafo instead
 */
function hideSidebar(): void {
    sidebar.classList.add("hidden");
    sidebar.classList.remove("flex");
    backdrop.classList.add("hidden");
    document.getElementById("app").style.overflow = null;

    toggleButtons.forEach((button) => {
        button.classList.add("open");
        button.classList.remove("closed");
    });

    sidebarOpen = false;
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

// Register onclick event listener for sidebar toggle button if it exists
if (sidebarToggleButton) {
    sidebarToggleButton.onclick = toggleSidebar;
}
