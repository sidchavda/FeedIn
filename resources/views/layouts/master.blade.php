<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
    <head>

        <!-- Meta Data -->
        <meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="Tej">
        <meta name="Author" content="Tej">
        <meta name="keywords" content="Tej">
        <meta name="csrf-token" content="{{ csrf_token() }}">

		<!-- TITLE -->
        <title> FeedIn </title>

        <!-- Favicon -->
        <link rel="icon" href="{{asset('build/assets/img/brand-logos/favicon.ico')}}" type="image/x-icon">

        <!-- ICONS CSS -->
        <link href="{{asset('build/assets/icon-fonts/icons.css')}}" rel="stylesheet">

        @include('layouts.components.styles')

        <!-- APP CSS & APP SCSS -->
        @vite(['resources/sass/app.scss' ])

        <!-- Main Theme Js -->
        <script src="{{asset('build/assets/main.js')}}"></script>

        @yield('styles')

    </head>

    <body class="">

        <!-- Switcher -->
        <!-- @include('layouts.components.switcher') -->
        <!-- End switcher -->

        <!-- Loader -->
        <div id="loader" >
            <img src="{{asset('build/assets/img/media/loader.svg')}}" alt="">
        </div>
        <!-- Loader -->

        <div class="page">
            <!-- toaster msg -->
            @include('layouts.components.toaster')
            <!-- End toaster msg -->

            <!-- Main-Header -->
            @include('layouts.components.main-header')
            <!-- End Main-Header -->

            <!--Main-Sidebar-->
            @include('layouts.components.main-sidebar')
            <!-- End Main-Sidebar-->

            @yield('content')

            <!-- Country-selector modal -->
            @include('layouts.components.modal')
            <!-- End Country-selector modal -->

            <!-- Footer opened -->
            @include('layouts.components.footer')
            <!-- End Footer -->

            @yield('modals')

        </div>

        <!-- SCRIPTS -->
        @include('layouts.components.scripts')

        <!-- Sticky JS -->
        <script src="{{asset('build/assets/sticky.js')}}"></script>

        <!-- Custom-Switcher JS -->
        @vite('resources/assets/js/custom-switcher.js')

        <!-- APP JS-->
		@vite('resources/js/app.js')
        <!-- END SCRIPTS -->
    </body>

</html>
