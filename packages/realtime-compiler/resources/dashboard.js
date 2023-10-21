/**
 * Progressive enhancement when JavaScript is enabled to intercept form requests
 * and instead handle them with an asynchronous Fetch instead of refreshing the page.
 *
 * @param {Element} form
 * @param {?callback} okHandler
 * @param {?callback} errorHandler
 * @param {?callback} beforeCallHandler
 * @param {?callback} afterCallHandler
 */
function registerAsyncForm(form, okHandler = null, errorHandler = null, beforeCallHandler = null, afterCallHandler = null) {
    form.addEventListener("submit", function (event) {
        // Disable default form submit
        event.preventDefault();

        if (beforeCallHandler) {
            beforeCallHandler();
        }

        fetch("", {
            method: "POST",
            body: new FormData(event.target),
            headers: new Headers({
                "X-RC-Handler": "Async",
            }),
        }).then(response => {
            if (response.ok) {
                if (okHandler) {
                    okHandler(response);
                }
            } else {
                if (errorHandler) {
                    errorHandler(response);
                } else {
                    console.error("Fetch request failed.");
                }
            }
        }).catch(error => {
            // Handle any network-related errors
            console.error("Network error:", error);
        });

        if (afterCallHandler) {
            afterCallHandler();
        }
    });
}

function registerCreateFormModalHandlers() {
    let createPageModal = null;

    document.addEventListener('DOMContentLoaded', () => {
        createPageModal = new bootstrap.Modal('#createPageModal');
    });

    const createPageForm = document.getElementById("createPageForm");
    const createPageFormSubmit = document.getElementById("createPageButton");
    const createPageFormError = document.getElementById("createPageFormError");
    const createPageFormErrorContents = document.getElementById("createPageFormErrorContents");

    const okHandler = async response => {
        let data = await response.json();
        createPageModal.hide();
        createPageForm.reset();

        // Reload so new page shows up in the table
        location.reload();
    };

    const errorHandler = async response => {
        let data = await response.json();
        createPageFormError.style.display = 'block';
        createPageFormErrorContents.innerText = data.error;
    };

    const beforeCallHandler = () => {
        createPageFormSubmit.disabled = true;
        createPageFormError.style.display = 'none';
        createPageFormErrorContents.innerText = '';
    };

    const afterCallHandler = () => {
        createPageFormSubmit.disabled = false;
    };

    registerAsyncForm(createPageForm, okHandler, errorHandler, beforeCallHandler, afterCallHandler);
}

function handleModalInteractivity() {
    // Focus when modal is opened
    const modal = document.getElementById('createPageModal')
    const firstInput = document.getElementById('pageTypeSelection')

    modal.addEventListener('shown.bs.modal', () => {
        firstInput.focus()
    })

    // Handle form interactivity

    const createPageModalLabel = document.getElementById('createPageModalLabel');
    const titleInputLabel = document.getElementById('titleInputLabel');
    const contentInputLabel = document.getElementById('contentInputLabel');

    const contentInput = document.getElementById('contentInput');
    const pageTypeSelection = document.getElementById('pageTypeSelection');
    const createPageButton = document.getElementById('createPageButton');

    const baseInfo = document.getElementById('baseInfo');
    const createsPost = document.getElementById('createsPost');

    const createPageModalLabelDefault = createPageModalLabel.innerText;
    const titleInputLabelDefault = titleInputLabel.innerText;
    const contentInputLabelDefault = contentInputLabel.innerText;
    const contentInputPlaceholderDefault = contentInput.placeholder;

    pageTypeSelection.addEventListener('change', function (event) {
        createPageModalLabel.innerText = createPageModalLabelDefault;
        titleInputLabel.innerText = titleInputLabelDefault;
        contentInputLabel.innerText = contentInputLabelDefault;
        contentInput.placeholder = contentInputPlaceholderDefault;

        createPageButton.disabled = false;
        createPageButton.title = '';

        baseInfo.style.display = 'none';
        createsPost.style.display = 'none';

        let selection = event.target.value;

        if (selection === 'markdown-post') {
            baseInfo.style.display = 'block';
            createsPost.style.display = 'block';
            createPageModalLabel.innerText = 'Creating a new Markdown post';
            titleInputLabel.innerText = 'Post title';
        } else {
            baseInfo.style.display = 'block';
            createPageModalLabel.innerText = 'Creating a new ' + selection.replace(/-/g, ' ').replace(/^\w/, c => c.toUpperCase());
        }

        if (selection === 'blade-page') {
            contentInputLabel.innerText = 'Blade content';
            contentInput.placeholder = 'Enter your Blade content';
        }
    });
}

document.querySelectorAll(".buttonActionForm").forEach(form => {
    registerAsyncForm(form);
});

registerCreateFormModalHandlers();

handleModalInteractivity();
