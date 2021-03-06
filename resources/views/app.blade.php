<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Clark Winkelmann">
    <meta name="description" content="Analyze Flarum extensions">
    <title>Flarum Query {{ $title ? ' - ' . $title : '' }}</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    {{--<link rel="icon" href="{{ mix('media/favicon.png') }}">--}}
</head>
<body>
<noscript>
    <p>You must enable javascript to use this website.</p>
</noscript>
<div id="loading">
    <p><i class="fas fa-spinner fa-pulse"></i> Loading...</p>
</div>
<div id="app" data-token="{{ csrf_token() }}" data-discuss="{{ config('app.discuss_url') }}" data-show-latest="{{ config('app.show_latest') }}" data-queries="{{ json_encode($queries) }}"></div>
@include('analytics')
<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
