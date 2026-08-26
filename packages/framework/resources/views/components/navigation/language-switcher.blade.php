@php
    /** Links to each language version of the page being rendered, keyed by language. */
    $languageSwitcherPage = \Hyde\Support\Facades\Render::getPage();
    $languageSwitcherCurrent = \Hyde\Facades\Localization::currentLanguage();

    $languageSwitcherLinks = [];

    foreach ($languageSwitcherPage === null ? [] : \Hyde\Facades\Localization::alternates($languageSwitcherPage) as $language => $path) {
        $languageSwitcherLinks[$language] = \Hyde\Hyde::relativeLink(\Hyde\Hyde::formatLink($path));
    }
@endphp

@if(count($languageSwitcherLinks) > 1)
    <ul id="language-switcher" aria-label="Language"
        class="flex mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 md:mt-0 md:ml-4 md:pt-0 md:border-none">
        @foreach($languageSwitcherLinks as $language => $href)
            <li class="mr-3 md:mr-0 md:ml-2">
                @if($language === $languageSwitcherCurrent)
                    <span aria-current="true" lang="{{ $language }}"
                          class="text-sm font-medium uppercase text-gray-700 dark:text-gray-200">{{ $language }}</span>
                @else
                    <a href="{{ $href }}" lang="{{ $language }}" hreflang="{{ $language }}"
                       class="text-sm uppercase text-gray-700 hover:text-gray-900 dark:text-gray-100 dark:hover:text-white">{{ $language }}</a>
                @endif
            </li>
        @endforeach
    </ul>
@endif
