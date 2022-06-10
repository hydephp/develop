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
                <th colspan="1" class="windows">Open project directory in</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{$project->name}}</td>
                <td>{{$project->path}}</td>
                <td>{{$project->hyde()->version()}}</td>
                <td class="windows">
                    <form action="/fileapi/open" method="POST">
                        <input type="hidden" name="path" value="">
                        <input type="hidden" name="back" value="{{request()->path()}}">
                        <button type="submit">Windows Explorer</button>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
@endsection
