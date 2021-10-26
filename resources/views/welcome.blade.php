<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

</head>
<body>
<div id="app">
    <router-view></router-view>
</div>
<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
<!-- 引入 Vue app -->
<script src="{{mix('js/app.js')}}"></script>

</body>
</html>
