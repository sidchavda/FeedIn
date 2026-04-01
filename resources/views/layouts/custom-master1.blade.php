<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">

    <head>

        <!-- Meta Data -->
        <meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="Sypaze Technologies">
        <meta name="Author" content="Sypaze Technologies">
        <meta name="keywords" content="Sypaze Technologies">

		<!-- TITLE -->
        <title> Admin</title>

        <!-- Authentication JS -->
        @vite('resources/assets/js/authentication-main.js')

        <!-- Favicon -->
        <link rel="icon" href="{{asset('build/assets/img/brand-logos/favicon.ico')}}" type="image/x-icon">

        <!-- BOOTSTRAP CSS -->
        <link id="style" href="{{asset('build/assets/libs/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">

        <!-- ICONS CSS -->
        <link href="{{asset('build/assets/icon-fonts/icons.css')}}" rel="stylesheet">

        <!-- APP CSS & APP SCSS -->
        @vite(['resources/sass/app.scss'])

        @yield('styles')

    </head>

	<body class="cover1">

        @yield('content')

        <!-- Bootstrap JS -->
    	<script src="{{asset('build/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

        @yield('scripts')

    </body>

</html>


