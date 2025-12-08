<!DOCTYPE html>
<html lang="id">
<head>
  <title>@yield('title', 'UKM Band — Aplikasi Musik')</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <style>
      /* Custom Animations */
      .fade-in { animation: fadeIn 0.8s ease-in-out; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
      
      .hover-scale { transition: transform 0.3s ease; }
      .hover-scale:hover { transform: scale(1.03); }
      
      /* Custom Audio Player Styling */
      audio {
          filter: invert(1) hue-rotate(180deg); /* Simple dark mode trick for default player */
          border-radius: 30px;
      }
  </style>
  @stack('styles')
</head>
<body class="bg-dark-950 text-white d-flex flex-column min-vh-100">

  @include('layouts.navbar')

  <main class="flex-grow-1">
    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success bg-accent-soft border-accent text-white">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger bg-danger text-white">{{ session('error') }}</div>
        </div>
    @endif
    @yield('content')
  </main>



  @include('partials.player')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
