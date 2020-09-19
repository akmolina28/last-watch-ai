@extends('layout')

@section('content')
    <nav class="breadcrumb" aria-label="breadcrumbs">
        <ul>
            <li><a href="/">Home</a></li>
            <li class="is-active"><a href="#" aria-current="page">Detection Events</a></li>
        </ul>
    </nav>
    <div id="wrapper">
        <table class="table">
            <thead>
            <tr>
                <th>Matched File</th>
                <th>Occurred</th>
                <th>Relevant</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($events as $event)
                <tr>
                    <td>{{ $event->image_file_name }}</td>
                    <td>{{ $event->occurred_at }}</td>
                    <td>
                        @if ($event->detection_profiles_count > 0)
                            <i class='fas fa-check'></i>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $events->links() }}
    </div>
@endsection
