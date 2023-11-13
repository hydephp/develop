function initLiveEdit() {
    function getArticle() {
        let article = document.querySelector('#content > article');

        if (article === null) {
            // If no article element is found the user may have a custom template, so we cannot know which element to edit.
            throw new Error('No article element found, cannot live edit. If you are using a custom template, please make sure to include an article element in the #content container.');
        }

        return article;
    }

    function switchToEditor() {
        function hasEditorBeenSetUp() {
            return document.querySelector('#live-edit-container') === null;
        }

        function setupEditor() {
            //
        }

        function showEditor() {
            //
        }

        if (! hasEditorBeenSetUp()) {
            setupEditor();
        } else {
            showEditor();
        }
    }

    const article = getArticle();

    article.addEventListener('dblclick', switchToEditor);
}
