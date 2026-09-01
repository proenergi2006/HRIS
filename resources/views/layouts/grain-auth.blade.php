<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ProPeople') }} - @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('img/propeople-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/propeople-icon-512.png') }}">

    <!-- Styles -->
    <link href="{{ asset('graindashboard/css/graindashboard.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
      /* Samakan dgn layouts/grain.blade.php — font aplikasi = Inter. Tidak sentuh
         tag <i>/[class^="gd-"] (icon font gd-icons). */
      body, .btn, .form-control, .form-control-sm, .form-control-lg, .card, table,
      h1, h2, h3, h4, h5, h6, input, select, textarea, button, label, .alert {
        font-family: 'Inter', 'Source Sans Pro', Helvetica, Arial, sans-serif !important;
      }
    </style>
</head>
<body>
    <main class="main">

      <div class="content">
		@yield('content')
	  </div>
    </main>

	<script src="{{ asset('graindashboard/js/graindashboard.js') }}"></script>
	<script src="{{ asset('graindashboard/js/graindashboard.vendor.js') }}"></script>
	@yield('scripts')
</body>
</html>
