@extends('partials.layout')

@section('content')
    <header>
        <h1>
            Welcome to Hyde Rocket!
        </h1>
    </header>
    <section id="project-overview" class="center">
        <h2>
            Project Overview
        </h2>
        <table>
            <caption>
                Project Information
            </caption>
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Project Path</th>
                    <th>Hyde Version</th>
                    @if($app->windows)
                        <th colspan="1">Open project directory in</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->path }}</td>
                    <td>{{ $project->version }}</td>
                    @if($app->windows)
                    <td>
                        <form action="/fileapi/open" method="POST">
                            <input type="hidden" name="path" value="">
                            <input type="hidden" name="back" value="{{ request()->path() }}">
                            <button type="submit">Windows Explorer</button>
                        </form>
                    </td>
                    @endif
                </tr>
            </tbody>
        </table>

        <table>
            <caption>
                Content Overview
            </caption>
            <thead>
                <tr>
                    @foreach($pages as $category => $group)
                        <th>{{ $category }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($pages as $category => $group)
                        <td>
                            <strong>{{ count($group) }}</strong>
                            {{ strtolower(explode(' ', $category)[1]) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </section>


    <section id="pages-overview" class="center">
        <h2>
            Your Pages
        </h2>
        <table>
            <thead>
            <tr>
                <th>Type</th>
                <th>Page</th>
                <th>Path</th>
                <th colspan="2">Actions</th>
            </tr>
            </thead>
            <tbody class="not-center">
                @foreach($pages['Blade Pages'] as $page)
                    <tr>
                        <td>Blade</td>
                        <td>{{ \Hyde\Framework\Hyde::titleFromSlug($page) }}</td>
                        <td>_pages/{{ $page }}.blade.php</td>
                        <td style="border-right: none; padding-right: 0.25rem;">
                            <form action="/fileapi/open" method="POST">
                                <input type="hidden" name="path" value="_pages/{{ $page }}.blade.php">
                                <input type="hidden" name="back" value="{{ request()->path() }}">
                                <button type="submit" title="Open in system editor">Open</button>
                            </form>
                        </td>
                        <td style="border-left: none; padding-left: 0.25rem;">
                            <form action="#" method="GET">
                                {{-- @TODO implement --}}
                                <button type="submit" title="View with Realtime Compiler">View</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
