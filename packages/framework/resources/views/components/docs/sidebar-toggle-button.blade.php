<button 
    id="sidebar-toggle" 
    title="Toggle sidebar" 
    aria-label="Toggle sidebar navigation menu" 
    @click="sidebarOpen = ! sidebarOpen" 
    :class="{'[&>span:first-child]:opacity-0 [&>span:nth-child(2)]:rotate-45 [&>span:nth-child(3)]:-rotate-45 [&>span:last-child]:opacity-0': sidebarOpen}"
    class="relative inline-block w-8 h-8"
>
    <span class="block w-5 h-[2.375px] bg-black dark:bg-white absolute left-[5.5px] top-[9px] transition-all duration-300 ease-out" role="presentation"></span>
    <span class="block w-5 h-[2.375px] bg-black dark:bg-white absolute left-[5.5px] top-[15px] transition-all duration-300 ease-out origin-center" role="presentation"></span>
    <span class="block w-5 h-[2.375px] bg-black dark:bg-white absolute left-[5.5px] top-[15px] transition-all duration-300 ease-out origin-center" role="presentation"></span>
    <span class="block w-5 h-[2.375px] bg-black dark:bg-white absolute left-[5.5px] top-[21px] transition-all duration-300 ease-out" role="presentation"></span>
</button>