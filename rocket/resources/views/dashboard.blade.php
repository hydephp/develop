@extends('partials.layout')

@section('content')
	<h1>
		Welcome to Hyde Rocket!
	</h1>
    <section>
        <table>
            <caption>
                Project Information
            </caption>
            <thead>
            <tr>
                <th>Project Name</th>
                <th>Project Path</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{$project->name}}</td>
                <td>{{$project->path}}</td>
            </tr>
            </tbody>
        </table>
    </section>
@endsection
