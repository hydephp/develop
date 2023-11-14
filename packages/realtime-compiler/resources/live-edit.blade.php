<div id="__realtime-compiler-live-edit-insert">
    <!-- The live editor insert is not saved to your static site -->
    @php
        /** @var \Hyde\Pages\Concerns\BaseMarkdownPage $page */
        $markdown = $page->markdown()->body();
    @endphp
    <style>{!! $styles !!}</style>
    <template id="live-edit-template">
        <section id="live-edit-container" @class([
                'page-type-'.\Illuminate\Support\Str::kebab(class_basename($page::class)),
            ])>
            <form id="liveEditForm" action="/_hyde/live-edit" method="POST">
                <header id="liveEditHeader">
                    <h2>Live Editor</h2>
                    <menu id="liveEditFormActions">
                        <button id="liveEditCancel" type="button">
                            Cancel
                        </button>
                        <button id="liveEditSubmit" type="submit">
                            Save
                        </button>
                    </menu>
                </header>
                <input type="hidden" name="_token" value="{{ $csrfToken }}">
                <input type="hidden" name="pagePath" value="{{ $page->getSourcePath() }}">
                <label for="live-editor" class="sr-only">Edit page contents</label>
                <textarea name="contentInput" id="live-editor" cols="30" rows="20">{{ $markdown }}</textarea>
            </form>
        </section>
    </template>
    <script>{!! $scripts !!}</script>
    <script>initLiveEdit()</script>
</div>
