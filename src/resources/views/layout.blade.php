<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito';
        }
    </style>
</head>
<body class="antialiased px-5 py-5">
    <div class="container">
        <div class="columns">
            <div class="column is-one-quarter">
                <aside class="menu">
                    <p class="menu-label">
                        Vision Alerts
                    </p>
                    <ul class="menu-list">
                        <li><a href="/profiles" class="{{ (request()->is('profiles*')) ? 'is-active' : '' }}">Detection Profiles</a></li>
                        <li><a href="/events" class="{{ (request()->is('events*')) ? 'is-active' : '' }}">Detection Events</a></li>
                    </ul>
                    <p class="menu-label">
                        Automation
                    </p>
                    <ul class="menu-list">
                        <li><a>Network Path</a></li>
                        <li><a>MQTT</a></li>
                        <li><a>Webhook</a></li>
                        <li><a href="/telegram" class="{{ (request()->is('telegram*')) ? 'is-active' : '' }}">Telegram</a></li>
                    </ul>
                    <p class="menu-label">
                        Configuration
                    </p>
                    <ul class="menu-list">
                        <li><a>Settings</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-three-quarters">
                @yield('content')
            </div>
        </div>
    </div>
    @include('footer')
    <!-- Scripts -->
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
