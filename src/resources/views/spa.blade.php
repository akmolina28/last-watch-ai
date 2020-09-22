<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Vision Alerts</title>

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
    <div id="app" class="container">
        <div class="columns">
            <div class="column is-one-quarter">
                <aside class="menu">
                    <p class="menu-label">
                        Vision Alerts
                    </p>
                    <ul class="menu-list">
                        <li><router-link to="/profiles">Detection Profiles</router-link></li>
                        <li><router-link to="/events">Detection Events</router-link></li>
                    </ul>
                    <p class="menu-label">
                        Automation
                    </p>
                    <ul class="menu-list">
                        <li><router-link to="/folderCopy">Folder Copy</router-link></li>
                        <li><router-link to="/webRequest">Web Request</router-link></li>
                        <li><router-link to="/telegram">Telegram</router-link></li>
                        <li><a>MQTT (coming soon)</a></li>
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
                <router-view></router-view>
            </div>
        </div>
    </div>
    <!-- Javascript -->
    <script src="{{ mix("js/app.js") }}" type="text/javascript"></script>
</body>
</html>
