@extends('laravel-crudkit::core-admin-panel')

@section('laravel-crudkit-page-content')

<html>
    <head>
        <title>Admin Panel Error.</title>
    </head>
    <body>
        <div>{{ $message }}</div>
    </body>
</html>

@endsection