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
        function getLiveEditor() {
            return document.querySelector('#live-edit-container');
        }

        function hasEditorBeenSetUp() {
            return getLiveEditor() !== null;
        }

        function setupEditor() {
            const template = document.getElementById('live-edit-template');
            const editor = document.importNode(template.content, true);
            const article = getArticle();

            article.parentNode.insertBefore(editor, article.nextSibling);

            showEditor();

            document.getElementById('liveEditCancel').addEventListener('click', hideEditor);
        }

        function showEditor() {
            article.style.display = 'none';
            getLiveEditor().style.display = '';
        }

        function hideEditor() {
            article.style.display = '';
            getLiveEditor().style.display = 'none';
        }

        if (hasEditorBeenSetUp()) {
            showEditor();
        } else {
            setupEditor();
        }
    }

    const article = getArticle();

    article.addEventListener('dblclick', switchToEditor);
}
