/**
 * Core Scripts for the HydePHP Frontend
 *
 * @package     HydePHP - HydeFront
 * @version     v1.5.x (HydeFront)
 * @author      Caen De Silva
 */
var mainNavigationLinks = document.getElementById("main-navigation-links");
var openMainNavigationMenuIcon = document.getElementById("open-main-navigation-menu-icon");
var closeMainNavigationMenuIcon = document.getElementById("close-main-navigation-menu-icon");
var themeToggleButton = document.getElementById("theme-toggle-button");
var navigationToggleButton = document.getElementById("navigation-toggle-button");
var sidebarToggleButton = document.getElementById("sidebar-toggle-button");
var navigationOpen = false;
function toggleNavigation() {
    if (navigationOpen) {
        hideNavigation();
    }
    else {
        showNavigation();
    }
}
function showNavigation() {
    mainNavigationLinks.classList.remove("hidden");
    openMainNavigationMenuIcon.style.display = "none";
    closeMainNavigationMenuIcon.style.display = "block";
    navigationOpen = true;
}
function hideNavigation() {
    mainNavigationLinks.classList.add("hidden");
    openMainNavigationMenuIcon.style.display = "block";
    closeMainNavigationMenuIcon.style.display = "none";
    navigationOpen = false;
}
// Handle the documentation page sidebar
var sidebarOpen = screen.width >= 768;
var sidebar = document.getElementById("documentation-sidebar");
var backdrop = document.getElementById("sidebar-backdrop");
var toggleButtons = document.querySelectorAll(".sidebar-button-wrapper");
function toggleSidebar() {
    if (sidebarOpen) {
        hideSidebar();
    }
    else {
        showSidebar();
    }
}
function showSidebar() {
    sidebar.classList.remove("hidden");
    sidebar.classList.add("flex");
    backdrop.classList.remove("hidden");
    document.getElementById("app").style.overflow = "hidden";
    toggleButtons.forEach(function (button) {
        button.classList.remove("open");
        button.classList.add("closed");
    });
    sidebarOpen = true;
}
function hideSidebar() {
    sidebar.classList.add("hidden");
    sidebar.classList.remove("flex");
    backdrop.classList.add("hidden");
    document.getElementById("app").style.overflow = null;
    toggleButtons.forEach(function (button) {
        button.classList.add("open");
        button.classList.remove("closed");
    });
    sidebarOpen = false;
}
// Handle the theme switching
function toggleTheme() {
    if (isSelectedThemeDark()) {
        setThemeToLight();
    }
    else {
        setThemeToDark();
    }
    function isSelectedThemeDark() {
        return localStorage.getItem('color-theme') === 'dark' || !('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    function setThemeToDark() {
        document.documentElement.classList.add("dark");
        localStorage.setItem('color-theme', 'dark');
    }
    function setThemeToLight() {
        document.documentElement.classList.remove("dark");
        localStorage.setItem('color-theme', 'light');
    }
}
// Register onclick event listener for theme toggle button
themeToggleButton.onclick = toggleTheme;
// Register onclick event listener for navigation toggle button if it exists
if (navigationToggleButton) {
    navigationToggleButton.onclick = toggleNavigation;
}
// Register onclick event listener for sidebar toggle button if it exists
if (sidebarToggleButton) {
    sidebarToggleButton.onclick = toggleSidebar;
}
//# sourceMappingURL=hyde.js.map