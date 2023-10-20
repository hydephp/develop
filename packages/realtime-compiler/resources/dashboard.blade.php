@php /** @var \Hyde\RealtimeCompiler\Http\DashboardController $dashboard */ @endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <title>{{ $title }}</title>
    <base target="_parent">
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
                    <h2 class="h5 mb-0">Project Information</h2>
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
                    <h2 class="h5 mb-0">Site Pages & Routes</h2>
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
                            <tr>
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
                                            <form class="openInEditorForm" action="" method="POST">
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
    <script>
        /**
         * Progressive enhancement when JavaScript is enabled to intercept form requests
         * and instead handle them with an asynchronous Fetch instead of refreshing the page.
         */
        const forms = document.querySelectorAll(".openInEditorForm");
        forms.forEach(form => {
            form.addEventListener("submit", function (event) {
                // Disable default form submit
                event.preventDefault();

                fetch("", {
                    method: "POST",
                    body: new FormData(event.target),
                }).then(response => {
                    if (response.ok) {
                        // Request was successful, no need to do anything.
                    } else {
                        // Request failed, let's log it.
                        console.error("Fetch request failed.");
                    }
                }).catch(error => {
                    // Handle any network-related errors
                    console.error("Network error:", error);
                });
            });
        });
    </script>
@endif
</body>
</html>
