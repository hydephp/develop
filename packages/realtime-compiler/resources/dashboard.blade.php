@php /** @var \Hyde\RealtimeCompiler\Http\DashboardController $dashboard */ @endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <title>{{ $title }}</title>
</head>
<body>
<main class="container py-4">
    <div class="col-xl-10 mx-auto">
        <header class="px-4 py-5 my-5 text-center bg-light">
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
                    <h2 class="h5 mb-0">Project Versions</h2>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            @foreach($dashboard->getVersions() as $type => $version)
                                <td>
                                    <strong class="h6">{{ $type }}</strong>
                                    <span class="card-text">{{ $version }}</span>
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
                            @foreach(['Page Type', 'Route Key', 'Source File', 'Output File'] as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                        @foreach($dashboard->getPageList() as $route)
                            <tr>
                                <td>
                                    <code title="\{{ $route->getPageClass() }}">{{ class_basename($route->getPageClass()) }}</code>
                                </td>
                                <td>
                                    <a href="{{ $route->getLink() }}">{{ $route->getRouteKey() }}</a>
                                </td>
                                <td>
                                    <a href="{{ Hyde::path($route->getSourcePath()) }}">{{ $route->getSourcePath() }}</a>
                                </td>
                                <td>
                                    @if(file_exists(Hyde::sitePath($route->getOutputPath())))
                                        <a href="{{ Hyde::sitePath($route->getOutputPath()) }}">{{ $route->getOutputPath() }}</a>
                                    @else
                                        _site/{{ $route->getOutputPath() }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>
<footer class="bg-light text-center py-3 mt-3">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="col-lg-3"></div>
        <div class="col-lg-6">
            <p class="mb-1">
                Lorem ipsum dolor sit amet.
            </p>
        </div>
        <div class="col-lg-3">
            @if(\Hyde\RealtimeCompiler\Http\DashboardApiController::enabled())
                <span class="badge bg-success">Enhanced Mode Enabled</span>
                <button class="btn btn-info btn-sm rounded-circle">🛈</button>
            @endif
        </div>
    </div>
</footer>
</body>
</html>
