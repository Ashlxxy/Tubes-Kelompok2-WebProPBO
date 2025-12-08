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
  <script src="https://unpkg.com/swup@4"></script>
  <style>
      /* Custom Animations */
      .fade-in { animation: fadeIn 0.8s ease-in-out; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
      
      .hover-scale { transition: transform 0.3s ease; }
      .hover-scale:hover { transform: scale(1.03); }
      
      /* Swup Transitions */
      .transition-fade {
        transition: 0.4s;
        opacity: 1;
      }
      html.is-animating .transition-fade {
        opacity: 0;
      }
      
      /* Custom Audio Player Styling */
      audio {
          filter: invert(1) hue-rotate(180deg); /* Simple dark mode trick for default player */
          border-radius: 30px;
      }
  </style>
  @stack('styles')
</head>
<body class="bg-dark-950 text-white d-flex flex-column min-vh-100">

  <div id="navbar-wrapper">
    @include('layouts.navbar')
  </div>

  <main id="swup" class="flex-grow-1 transition-fade">
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
    <div style="height: 120px;"></div>
  </main>

  @include('partials.player')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
      const swup = new Swup({
        containers: ["#swup", "#navbar-wrapper"],
        cache: false, // Disable cache to ensure fresh data
      });
      
      // Re-init Bootstrap components like Modals after content replace
      swup.hooks.on('content:replace', () => {
          // Re-initialize Bootstrap modals/tooltips if needed
          // But purely data-bs-toggle usually works via delegated events in BS5?
          // Actually BS5 mostly uses delegation, but tooltips need init.
          // Modals might need checking.
          
          // Re-trigger fade-ins
          const fadeElements = document.querySelectorAll('.fade-in');
          fadeElements.forEach(el => {
              el.style.animation = 'none';
              el.offsetHeight; /* trigger reflow */
              el.style.animation = null; 
          });
          
          // Execute scripts in the new content??
          // Swup 4 by default does NOT execute scripts in head/body unless using the Scripts plugin.
          // However, we are not installing plugins via npm here easily.
          // We can manually execute scripts found in the new container.
          
          // Simple script runner for inline scripts in the new container
          const scripts = document.querySelectorAll('#swup script');
          scripts.forEach(script => {
              const newScript = document.createElement('script');
              Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
              newScript.appendChild(document.createTextNode(script.innerHTML));
              script.parentNode.replaceChild(newScript, script);
          });
      });
  </script>
  @stack('scripts')
</body>
</html>
