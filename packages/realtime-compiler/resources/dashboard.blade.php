@php /** @var \Hyde\RealtimeCompiler\Http\DashboardController $dashboard */ @endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>{{ $title }}</title>
    <base target="_parent">
    <style>
        .justCreatedPage td {
            animation: 2s ease-out 0s 1 FadeOut;
        }

        @keyframes FadeOut {
            0% {
                background-color: rgba(25, 135, 84, 0.4);
            }
            100% {
                background-color: white;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-dark bg-dark flex-md-nowrap p-2">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="/dashboard" style="font-weight: 600;">{{ $title }}</a>
    <div class="navbar-nav">
        @if($request->embedded)
            <div class="nav-item text-nowrap pe-4">
                <a class="nav-link px-3" href="/dashboard">Open full page dashboard</a>
            </div>
        @else
        <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="/">Back to site</a>
        </div>
        @endif
    </div>
</nav>
<main class="container py-4 mb-auto">
    <div class="col-xl-10 mx-auto">
        <header class="px-4 py-5 my-4 text-center bg-light">
            <h1 class="display-6 fw-bold">{{ $title }}</h1>
            <div class="mx-auto">
                <h2 class="h4">Welcome to the dashboard for your HydePHP site.</h2>
                <p class="lead mb-0">This page is accessible through the Hyde Realtime Compiler and will not be saved to your static site.</p>
            </div>
        </header>
    </div>
    <section>
        <div class="col-xl-10 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Site Pages & Routes</h2>
                        @if($dashboard->enableEditor())
                            <form class="buttonActionForm" action="" method="POST">
                                <input type="hidden" name="action" value="openInExplorer">
                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Open in system file explorer">Open folder</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            @foreach($dashboard->getProjectInformation() as $type => $info)
                                <td>
                                    <strong class="h6">{{ $type }}</strong>
                                    <span class="card-text">{{ $info }}</span>
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <section>
        <div class="col-xl-10 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Site Pages & Routes</h2>
                        @if($dashboard->enableEditor())
                            <noscript><style>#createPageModalButton { display: none; }</style></noscript>
                            <!-- Button trigger modal -->
                            <button id="createPageModalButton" type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createPageModal">
                                Create page
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="createPageModal" tabindex="-1" aria-labelledby="createPageModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="createPageModalLabel">Create new page</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form id="createPageForm" action="" method="POST">
                                            <input type="hidden" name="action" value="createPage">

                                            <div class="modal-body">
                                                <div id="createPageFormError" class="alert alert-danger" style="display: none;">
                                                    <strong>Error:</strong>
                                                    <span id="createPageFormErrorContents"></span>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="pageTypeSelection" class="form-label">Select page type</label>
                                                    <select id="pageTypeSelection" name="pageTypeSelection" class="form-select" aria-label="Select page type">
                                                        <option selected disabled>Select page type</option>
                                                        @foreach(['BladePage', 'MarkdownPage', 'MarkdownPost', 'DocumentationPage'] as $page)
                                                            <option value="{{ str($page)->kebab() }}">{{ $page }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="page-creation-group" id="baseInfo" style="display: none">
                                                    <div class="px-3 d-flex align-items-center justify-content-between">
                                                        <div class="col"><hr role="presentation"></div>
                                                        <div class="col-auto px-2"><small class="text-muted">Required Details</small></div>
                                                        <div class="col"><hr role="presentation"></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="titleInput" id="titleInputLabel" class="form-label">Page title</label>
                                                        <input type="text" class="form-control" id="titleInput" name="titleInput" placeholder="Enter a title" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="contentInput" id="contentInputLabel" class="form-label">Markdown text</label>
                                                        <textarea class="form-control" id="contentInput" name="contentInput" rows="8" placeholder="Enter your Markdown text" required></textarea>
                                                    </div>
                                                </div>

                                                <div class="page-creation-group" id="createsPost" style="display: none">
                                                    <div class="px-3 d-flex align-items-center justify-content-between">
                                                        <div class="col"><hr role="presentation"></div>
                                                        <div class="col-auto px-2"><small class="text-muted">Extra Details</small></div>
                                                        <div class="col"><hr role="presentation"></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="postDescription" class="form-label">Post description</label>
                                                        <input type="text" class="form-control" id="postDescription" name="postDescription" placeholder="Enter a post description (optional)">
                                                    </div>

                                                    <div class="mb-3 row">
                                                        <div class="col-lg-4">
                                                            <label for="postCategory" class="form-label">Post category</label>
                                                            <input type="text" class="form-control" id="postCategory" name="postCategory" placeholder="Enter a post category (optional)">
                                                        </div>

                                                        <div class="col-lg-4">
                                                            <label for="postAuthor" class="form-label">Post author</label>
                                                            <input type="text" class="form-control" id="postAuthor" name="postAuthor" placeholder="Enter a post author (optional)">
                                                        </div>

                                                        <div class="col-lg-4">
                                                            <label for="postDate" class="form-label">Post date</label>
                                                            <input type="datetime-local" class="form-control" id="postDate" name="postDate" placeholder="Enter a post date (optional)" value="{{ date('Y-m-d H:i') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="https://github.com/hydephp/realtime-compiler/issues/new?{{ http_build_query(['title' => 'Feedback on the dashboard create page modal', 'body' => 'Write something nice!']) }}" class="btn btn-sm btn-outline-success me-auto" title="This is a new feature, we'd love your feedback!" target="_blank" rel="noopener">Send feedback</a>

                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-sm btn-primary" id="createPageButton" title="Please select a page type first" disabled>Create page</button>
                                            </div>
                                            <script>
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
                                            </script>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            @foreach(['Page Type', 'Route Key', 'Source File', 'Output File', 'Identifier'] as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                            <th class="text-end">Actions</th>
                        </tr>
                        @foreach($dashboard->getPageList() as $route)
                            <tr id="pageRow-{{ $route->getRouteKey() }}" @class(['page-table-row', $dashboard->getFlash('justCreatedPage') === $route->getRouteKey() ? 'justCreatedPage active' : ''])>
                                <td>
                                    <code title="\{{ $route->getPageClass() }}">{{ class_basename($route->getPageClass()) }}</code>
                                </td>
                                <td>
                                    {{ $route->getRouteKey() }}
                                </td>
                                <td>
                                    {{ $route->getSourcePath() }}
                                </td>
                                <td>
                                    {{ $route->getOutputPath() }}
                                </td>
                                <td>
                                    {{ $route->getPageIdentifier() }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end">
                                        @if($dashboard->enableEditor())
                                            <form class="buttonActionForm" action="" method="POST">
                                                <input type="hidden" name="action" value="openInEditor">
                                                <input type="hidden" name="routeKey" value="{{ $route->getRouteKey() }}">
                                                <button type="submit" class="btn btn-outline-primary btn-sm me-2" title="Open in system default application">Edit</button>
                                            </form>
                                        @endif
                                        <a href="{{ $route->getLink() }}" class="btn btn-outline-primary btn-sm" title="Open this page preview in browser">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </section>
    @if($dashboard->showTips())
        <section>
            <div class="col-xl-10 mx-auto pt-3 px-5">
                <div class="alert alert-success">
                    <strong>Tip:</strong>
                    {{ $dashboard->getTip() }}
                </div>
            </div>
        </section>
    @endif
</main>
<footer class="bg-light text-center py-3 mt-3">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="col-lg-3"></div>
        <div class="col-lg-6">
            <p class="mb-1">
                HydePHP Realtime Compiler <span class="text-muted">{{ $dashboard->getVersion() }}</span>
            </p>
        </div>
        <div class="col-lg-3"></div>
    </div>
</footer>
@if($dashboard->enableEditor())
    {{-- Interactivity is not needed when editor is disabled --}}
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script>{!! $dashboard->getScripts() !!}</script>
@endif
</body>
</html>
