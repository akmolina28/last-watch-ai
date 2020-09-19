@extends('layout')

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/event.js') }}"></script>
@endsection

@section('content')
    <nav class="breadcrumb" aria-label="breadcrumbs">
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/events">Detection Events</a></li>
            <li class="is-active"><a href="#" aria-current="page">{{ basename($event->image_file_name) }}</a></li>
        </ul>
    </nav>
    <div class="columns">
        <div class="column is-one-third">
            <h1 class="heading">Predictions</h1>
            <h1 class="heading">Predictions</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Class</th>
                        <th>Confidence</th>
                        <th>Relevance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->aiPredictions as $index=>$prediction)
                        <tr class="prediction" data-prediction="{{ $prediction }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $prediction->object_class }}</td>
                            <td>{{ $prediction->confidence }}</td>
                            <td>
                                @if(count($prediction->detectionProfiles) > 0)
                                    <i class="fas fa-check"></i>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="column is-two-thirds">
            <canvas id="event-snapshot" height="480" width="640"></canvas>
        </div>
    </div>
@endsection
