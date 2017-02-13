<!DOCTYPE html>
<html ng-app="apApp">
<head>
    <title>Laravel CrudKit Admin Panel</title>

    <link href="{{ URL::asset('laravel-crudkit/adminlte/bootstrap/css/bootstrap.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ URL::asset('laravel-crudkit/adminlte/dist/css/AdminLTE.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ URL::asset('laravel-crudkit/adminlte/dist/css/skins/skin-blue.css') }}" type="text/css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="{{ URL::asset('laravel-crudkit/vendor/datetimepicker/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">

    <script src="{{ URL::asset('laravel-crudkit/adminlte/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
    <script src="{{ URL::asset('laravel-crudkit/adminlte/plugins/jQueryUI/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('laravel-crudkit/adminlte/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ URL::asset('laravel-crudkit/adminlte/dist/js/app.js') }}" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular.min.js"></script>
    <script src="{{ URL::asset('laravel-crudkit/vendor/ui-bootstrap-custom-0.13.0.min.js') }}" type="text/javascript"></script>
    <script src="{{ URL::asset('laravel-crudkit/vendor/ui-bootstrap-custom-tpls-0.13.0.min.js') }}" type="text/javascript"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
    <script src="{{ URL::asset('laravel-crudkit/vendor/datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::asset('laravel-crudkit/vendor/lodash/lodash.min.4.5.1.js') }}"></script>

    <script>
        // bootstrap with server data
        angular.module('apConfig', [])
            .value('Page', '{{$page}}')
            .value('PrimaryKey', '{{$primaryKey}}')
            .value('ItemId', '{{$itemId}}');
    </script>


    <!-- https://github.com/atais/angular-eonasdan-datetimepicker -->
    <script src="{{ URL::asset('laravel-crudkit/vendor/datetimepicker/angular-eonasdan-datetimepicker.min.js') }}"></script>

    <script src="{{ URL::asset('laravel-crudkit/js/admin-panel-app.js') }}" type="text/javascript"></script>

    <!-- prevent return keypress from activating buttons in the edit form that aren't in focus, except for textareas -->
    <script>
    $(document).ready(function() {
            $("#editForm").bind("keypress", function(e) {
                if (e.keyCode == 13 && e.target.nodeName != "TEXTAREA") {
                    return false;
                }
            });

            // Polyfill for IE 11
            if(!String.prototype.startsWith) {
                String.prototype.startsWith = function(searchString, position) {
                    position = position || 0;
                    return this.substr(position, searchString.length) === searchString;
                };
            }
        });
    </script>


</head>
<body class="skin-blue">
    @yield('laravel-crudkit-core-body')
</body>
</html>