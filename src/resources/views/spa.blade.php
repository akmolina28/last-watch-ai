<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Last Watch AI</title>

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <main>
            <navigation></navigation>

            <div class="container main-container">
                <div class="lead">
                    <router-view></router-view>
                </div>
            </div>
        </main>
    </div>
    <script src="{{ mix("js/app.js") }}" type="text/javascript"></script>
</body>
</html>
