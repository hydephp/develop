<div {{ $attributes->class(['py-4 sm:px-6 w-96 max-w-full']) }}>
    <article class="flex flex-col overflow-hidden bg-gray-50 dark:bg-slate-800 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
        @isset($title)
        <header {{ $title->attributes->class(['px-6 pt-4']) }}>
            <span class="text-xl font-bold">
                {{ $title }}
            </span>
        </header>
        @endif
        <section class="px-6 py-4">
            {{ $slot }}
        </section>
        @isset($footer)
        <footer {{ $footer->attributes->class(['px-6 pb-4 mt-2']) }}>
            {{ $footer }}
        </footer>
        @endif
    </article>
</div>