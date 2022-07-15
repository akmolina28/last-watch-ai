<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-theme">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Last Watch AI</title>

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>

<body style="overflow-y:scroll">
    <div id="app">
        <app></app>
    </div>
    <script src="{{ mix("js/app.js") }}" type="text/javascript"></script>
</body>

</html>