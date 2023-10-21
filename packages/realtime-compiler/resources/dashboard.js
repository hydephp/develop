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

document.querySelectorAll(".openInEditorForm").forEach(form => {
    registerAsyncForm(form);
});

function registerCreateFormModalHandlers() {
    let createPageModal = null;

    document.addEventListener('DOMContentLoaded', function () {
        createPageModal = new bootstrap.Modal('#createPageModal');
    });

    const createPageForm = document.getElementById("createPageForm");
    const createPageFormSubmit = document.getElementById("createPageButton");
    const createPageFormError = document.getElementById("createPageFormError");
    const createPageFormErrorContents = document.getElementById("createPageFormErrorContents");

    registerAsyncForm(createPageForm, async function (response) {
        let data = await response.json();
        createPageModal.hide();
        Swal.fire({
            title: 'Page created!',
            text: data.body,
            icon: 'success',
            timer: 3000,
            timerProgressBar: true,
        })
        createPageForm.reset()
    }, async function (response) {
        let data = await response.json();
        createPageFormError.style.display = 'block';
        createPageFormErrorContents.innerText = data.error;
    }, function () {
        createPageFormSubmit.disabled = true;
        createPageFormError.style.display = 'none';
        createPageFormErrorContents.innerText = '';
    }, function () {
        createPageFormSubmit.disabled = false;
    });
}

registerCreateFormModalHandlers();
