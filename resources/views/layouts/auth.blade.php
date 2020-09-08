<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <title>{{ $page['title'] or '' }} | {{ config('app.name') }}</title>
    <link href='https://fonts.googleapis.com/css?family=Titillium+Web:400,300,600' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="{{ cdn('css/style.css') }}">
</head>
<body>
@yield('body')
<script src='{{ cdn('js/jquery.min.js') }}'></script>
<script  src="{{ cdn('js/index.js') }}"></script>
</body>
</html>