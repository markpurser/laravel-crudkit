@extends('laravel-crudkit::layouts.core')

@section('laravel-crudkit-core-body')

<div class="wrapper">

    <header class="main-header">
        <a href="{{ url('admin-panel') }}" class="logo">
            {{ config('crudkit.app_name') }}
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-custom-menu">
            </div>
        </nav>
    </header>
    <aside class="main-sidebar">
        <section class="sidebar">
            <ul class="sidebar-menu">
                <li class="header">{{ config('crudkit.pages_heading') }}</li>
                @foreach ($pageMap as $item)
                    <li role="presentation" @if( $item->id == $page )class="active"@endif><a href="{{ url('admin-panel?page='.$item->id) }}"><i class="fa fa-book"></i> {{ $item->label }}</a></li>
                @endforeach
                <li class="header">{{ config('crudkit.extras_heading') }}</li>
                <li role="presentation"><a href="{{ url('logout') }}"><i class="fa fa-sign-out"></i>Log out</a></li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
        @yield('laravel-crudkit-core-admin-panel')
    </div>
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
        <b>Version</b> {{ config('crudkit.version') }}
      </div>
      <strong>{{ config('crudkit.footer_text') }}</strong>
  </footer>
</div>

@endsection