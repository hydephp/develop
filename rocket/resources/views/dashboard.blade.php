@extends('partials.layout')

@section('content')
    <header>
        <h1>
            Welcome to Hyde Rocket!
        </h1>
    </header>
    <section class="center">
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
                    <td style="position: relative;">
                        {{ $project->version }}
                        <sup role="tooltip" style="position: absolute; top: 4px; right: 4px;" title="Retrieved from your project's composer.json">🛈</sup>
                    </td>
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
@endsection
