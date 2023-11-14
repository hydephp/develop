<div id="__realtime-compiler-live-edit-insert">
    <!-- The live editor insert is not saved to your static site -->
    @php
        /** @var \Hyde\Pages\Concerns\BaseMarkdownPage $page */
        $markdown = $page->markdown()->body();
    @endphp
    <style>{!! $styles !!}</style>
    <template id="live-edit-template">
        <section id="live-edit-container" style="margin-top: {{ $page instanceof \Hyde\Pages\DocumentationPage ? '1rem' : '-1rem'}};">
            <form id="liveEditForm" action="/_hyde/live-edit" method="POST">
                <header class="prose dark:prose-invert mb-3">
                    <h2 class="mb-0">Live Editor</h2>
                    <menu>
                        <button id="liveEditCancel" type="button">
                            Cancel
                        </button>
                        <button id="liveEditSubmit" type="submit">
                            Save
                        </button>
                    </menu>
                </header>
                <input type="hidden" name="_token" value="{{ $csrfToken }}">
                <input type="hidden" name="page" value="{{ $page->getSourcePath() }}">
                <label for="live-editor" class="sr-only">Edit page contents</label>
                <textarea name="markdown" id="live-editor" cols="30" rows="20" class="rounded-lg bg-gray-200 dark:bg-gray-800">{{ $markdown }}</textarea>
                <footer class="prose dark:prose-invert">
                    <small>
                        <a id="#liveEditSettingsButton" role="button" href="javascript:liveEditSettings();">
                            Editor preferences
                        </a>
                    </small>
                </footer>
            </form>
        </section>
    </template>
    <script>{!! $scripts !!}</script>
    <script>initLiveEdit()</script>
</div>
