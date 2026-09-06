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
    <ul id="language-switcher" aria-label="Language" {{ $attributes->merge(['class' => 'flex gap-3']) }}>
        @foreach($languageSwitcherLinks as $language => $href)
            @php
                $languageSwitcherLabel = \Hyde\Facades\Localization::label($language);

                // A language showing its own code reads better upcased, a named one does not.
                $languageSwitcherCase = $languageSwitcherLabel === $language ? 'uppercase' : '';
            @endphp
            <li>
                @if($language === $languageSwitcherCurrent)
                    <span aria-current="true" lang="{{ $language }}"
                          class="text-sm font-medium {{ $languageSwitcherCase }} text-gray-700 dark:text-gray-200">{{ $languageSwitcherLabel }}</span>
                @else
                    <a href="{{ $href }}" lang="{{ $language }}" hreflang="{{ $language }}"
                       class="text-sm {{ $languageSwitcherCase }} text-gray-700 hover:text-gray-900 dark:text-gray-100 dark:hover:text-white">{{ $languageSwitcherLabel }}</a>
                @endif
            </li>
        @endforeach
    </ul>
@endif
