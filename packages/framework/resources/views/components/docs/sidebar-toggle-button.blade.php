<button 
    id="sidebar-toggle" 
    title="Toggle sidebar" 
    aria-label="Toggle sidebar navigation menu" 
    @click="sidebarOpen = ! sidebarOpen" 
    :class="{'[&>span:first-child]:opacity-0 [&>span:nth-child(2)]:rotate-45 [&>span:nth-child(3)]:-rotate-45 [&>span:last-child]:opacity-0': sidebarOpen}"
    class="relative inline-block w-8 h-8 hover:text-gray-700 dark:text-gray-200"
>
    <span class="block w-5 h-0.5 bg-current absolute left-1.5 top-1 transition-all duration-300 ease-out" role="presentation"></span>
    <span class="block w-5 h-0.5 bg-current absolute left-1.5 top-2.5 transition-all duration-300 ease-out origin-center" role="presentation"></span>
    <span class="block w-5 h-0.5 bg-current absolute left-1.5 top-2.5 transition-all duration-300 ease-out origin-center" role="presentation"></span>
    <span class="block w-5 h-0.5 bg-current absolute left-1.5 top-4 transition-all duration-300 ease-out" role="presentation"></span>
</button>