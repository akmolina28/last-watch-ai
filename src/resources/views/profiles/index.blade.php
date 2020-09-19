@extends('layout')

@section('content')
    <nav class="breadcrumb" aria-label="breadcrumbs">
        <ul>
            <li><a href="/">Home</a></li>
            <li class="is-active"><a href="#" aria-current="page">Detection Profiles</a></li>
        </ul>
    </nav>
    <div id="wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Pattern</th>
                    <th>Regex</th>
                    <th>Min Confidence</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($profiles as $profile)
                    <tr>
                        <td>{{ $profile->name }}</td>
                        <td>{{ $profile->file_pattern }}</td>
                        <td>{{ $profile->use_regex }}</td>
                        <td>{{ $profile->min_confidence }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a class="button" href="/profiles/create">New Profile</a>
    </div>
@endsection
