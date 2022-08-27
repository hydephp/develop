<button id="sidebar-toggle" title="Toggle sidebar" aria-label="Toggle sidebar navigation menu"
        @click="sidebarOpen = ! sidebarOpen" :class="{'active' : sidebarOpen}">
    <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
    <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
    <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
    <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
</button>