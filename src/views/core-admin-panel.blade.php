@extends('laravel-crudkit::layouts.core-body')

@section('laravel-crudkit-core-admin-panel')

<section class="content-header">
    <div class="row">
        <div class="col-md-4">
            <h3 style="margin-top: 5px">
                {{$pageLabel}}
            </h3>
        </div>
        <div class="col-md-8">
            <div class="pull-right">
                @yield('laravel-crudkit-action-buttons')
            </div>
        </div>
    </div>
</section>
    <div class="col-md-12">
        <div id="alertTarget">
        </div>
    </div>
<section class="content">
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary" style='padding-top:15px'>
            <div class="box-body" >
                @yield('laravel-crudkit-page-content')
            </div>
        </div> 
    </div>
</div>
</section>

@endsection