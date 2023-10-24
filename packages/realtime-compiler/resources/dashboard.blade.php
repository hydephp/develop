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
    <style>/*! fileicon.css v0.1.1 | MIT License | github.com/picturepan2/fileicon.css */.file-icon{font-family:Arial,Tahoma,sans-serif;font-weight:300;display:inline-block;width:24px;height:32px;background:#018fef;position:relative;border-radius:2px;text-align:left;-webkit-font-smoothing:antialiased}.file-icon::before{display:block;content:"";position:absolute;top:0;right:0;width:0;height:0;border-bottom-left-radius:2px;border-width:5px;border-style:solid;border-color:#fff #fff rgba(255,255,255,.35) rgba(255,255,255,.35)}.file-icon::after{display:block;content:attr(data-type);position:absolute;bottom:0;left:0;font-size:10px;color:#fff;text-transform:lowercase;width:100%;padding:2px;white-space:nowrap;overflow:hidden}.file-icon-xs{width:12px;height:16px;border-radius:2px}.file-icon-xs::before{border-bottom-left-radius:1px;border-width:3px}.file-icon-xs::after{content:"";border-bottom:2px solid rgba(255,255,255,.45);width:auto;left:2px;right:2px;bottom:3px}.file-icon-sm{width:18px;height:24px;border-radius:2px}.file-icon-sm::before{border-bottom-left-radius:2px;border-width:4px}.file-icon-sm::after{font-size:7px;padding:2px}.file-icon-md{width:33px;height:44px;border-radius:3px}.file-icon-md::before{border-bottom-left-radius:2px;border-width:6px}.file-icon-md::after{font-size:12px;padding:3px 5px}.file-icon-lg{width:48px;height:64px;border-radius:3px}.file-icon-lg::before{border-bottom-left-radius:2px;border-width:8px}.file-icon-lg::after{font-size:16px;padding:4px 6px}.file-icon-xl{width:96px;height:128px;border-radius:4px}.file-icon-xl::before{border-bottom-left-radius:4px;border-width:16px}.file-icon-xl::after{font-size:24px;padding:4px 10px}.file-icon[data-type=rar],.file-icon[data-type=zip]{background:#acacac}.file-icon[data-type^=doc]{background:#307cf1}.file-icon[data-type^=xls]{background:#0f9d58}.file-icon[data-type^=ppt]{background:#d24726}.file-icon[data-type=pdf]{background:#e13d34}.file-icon[data-type=txt]{background:#5eb533}.file-icon[data-type=flac],.file-icon[data-type=m4a],.file-icon[data-type=mp3],.file-icon[data-type=wma]{background:#8e44ad}.file-icon[data-type=avi],.file-icon[data-type=mkv],.file-icon[data-type=mov],.file-icon[data-type=mp4],.file-icon[data-type=wmv]{background:#7a3ce7}.file-icon[data-type=bmp],.file-icon[data-type=gif],.file-icon[data-type=jpeg],.file-icon[data-type=jpg],.file-icon[data-type=png]{background:#f4b400}</style>
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
                                                <input type="hidden" name="action" value="openPageInEditor">
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
    <section>
        <div class="col-xl-10 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Media Library</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container d-flex flex-wrap" style="margin: 0 -0.5rem">
                        @foreach(\Hyde\Support\Filesystem\MediaFile::all() as $mediaFile)
                            <div class="col-lg-4 p-2 d-flex flex-grow-1">
                                <figure class="card w-100 p-2 mb-0">
                                    @if(in_array($mediaFile->getExtension(), ['svg', 'png', 'jpg', 'jpeg', 'gif']))
                                        <img src="media/{{ $mediaFile->getIdentifier() }}" alt="{{ $mediaFile->getName() }}" class="object-fit-cover w-100 rounded-2" style="height: 240px;">
                                    @else
                                        <code style="height: 240px; overflow: hidden; -webkit-mask-image: linear-gradient(180deg, white 60%, transparent);"><pre style="{{ (substr_count(trim($mediaFile->getContents()), "\n") < 3 && strlen($mediaFile->getContents()) > 200) ? 'white-space: normal;' : '' }}">{{ substr($mediaFile->getContents(), 0, 400) }}</pre></code>
                                    @endif
                                    <figcaption class="container mt-3">
                                        <div class="row flex-nowrap">
                                            <div class="col-auto px-0">
                                                <div class="file-icon file-icon-md" data-type="{{ $mediaFile->getExtension() }}"></div>
                                            </div>
                                            <div class="col">
                                                <div class="row flex-nowrap justify-content-start">
                                                    <p class="col-auto text-truncate mb-0 pe-2">
                                                        <strong title="{{ $mediaFile->getPath() }}">{{ $mediaFile->getName() }}</strong>
                                                    </p>
                                                    <div class="col px-0 text-nowrap">
                                                        <small class="text-muted">({{ $dashboard::bytesToHuman($mediaFile->getContentLength()) }})</small>
                                                    </div>
                                                </div>
                                                <div class="row small align-items-center">
                                                    <div class="w-auto pe-0">
                                                        <a href="media/{{ $mediaFile->getIdentifier() }}" title="Open this image in the browser" target="_blank">Fullscreen</a>
                                                    </div>
                                                    @if($dashboard->enableEditor())
                                                        <div class="w-auto ps-0">
                                                            <form class="buttonActionForm" action="" method="POST">
                                                                <input type="hidden" name="action" value="openMediaFileInEditor">
                                                                <input type="hidden" name="identifier" value="{{ $mediaFile->getIdentifier() }}">
                                                                <button type="submit" class="btn btn-link btn-sm py-0" title="Open this image in the system editor">Edit</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </figcaption>
                                </figure>
                            </div>
                        @endforeach
                    </div>
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
