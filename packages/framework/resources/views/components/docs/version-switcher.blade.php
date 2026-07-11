@php
    /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */
    $switcherVersions = \Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions::all();
    $switcherCurrentVersion = $sidebar->version;
    $switcherCurrentPage = \Hyde\Support\Facades\Render::getPage();
@endphp

@if($switcherCurrentVersion !== null && $switcherVersions->count() > 1)
    <div id="docs-version-switcher" x-data="{ versionSwitcherOpen: false }" @click.outside="versionSwitcherOpen = false" class="relative px-4 pb-3">
        <button id="docs-version-switcher-button" @click="versionSwitcherOpen = ! versionSwitcherOpen" :aria-expanded="versionSwitcherOpen"
                aria-haspopup="listbox" aria-label="Switch documentation version"
                class="w-full flex items-center justify-between rounded-sm text-sm leading-normal bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 py-1.5 px-3 transition-colors duration-150">
            <span>Version {{ $switcherCurrentVersion->name }}</span>
            <svg class="w-4 h-4 opacity-75 transition-transform duration-150" :class="versionSwitcherOpen ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <ul x-cloak x-show="versionSwitcherOpen" role="listbox" aria-labelledby="docs-version-switcher-button"
            class="absolute left-4 right-4 z-40 mt-1 rounded-sm shadow-lg bg-white dark:bg-gray-700 py-1 text-sm">
            @foreach($switcherVersions as $switcherVersion)
                @php
                    $switcherRoute = $switcherCurrentPage !== null
                        ? (\Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions::getEquivalentRoute($switcherCurrentPage, $switcherVersion) ?? $switcherVersion->home())
                        : $switcherVersion->home();
                @endphp
                <li role="option" aria-selected="{{ $switcherVersion->name === $switcherCurrentVersion->name ? 'true' : 'false' }}">
                    @if($switcherVersion->name === $switcherCurrentVersion->name)
                        <span aria-current="true" class="block py-1 px-3 font-medium opacity-75">{{ $switcherVersion->name }}</span>
                    @elseif($switcherRoute !== null)
                        <a href="{{ $switcherRoute }}" class="block py-1 px-3 hover:bg-gray-100 dark:hover:bg-gray-600">{{ $switcherVersion->name }}</a>
                    @else
                        <span class="block py-1 px-3 opacity-50">{{ $switcherVersion->name }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
