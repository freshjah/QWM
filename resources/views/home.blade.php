<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])    
    {{--
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    --}}
    <script>
        // Theme toggle logic
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
        } else {
        document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body class="bg-white text-gray-900 dark:bg-gray-900 dark:text-white">

<!-- Navbar -->
<nav class="max-w-7xl mx-auto px-4 py-6 flex justify-between items-center">
  <x-app-logo />

  <div class="flex items-center gap-4">
    @auth
      <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">Dashboard</a>
    @else
      <a href="{{ route('login') }}" class="text-sm font-semibold hover:underline">Login</a>
      <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Register</a>
    @endauth
    <button onclick="toggleTheme()" class="ml-4 px-2 py-1 border rounded text-sm">
      🌓
    </button>
  </div>
</nav>

<!-- Hero -->
<section class="text-center py-20">
  <h1 class="text-4xl sm:text-5xl font-extrabold text-indigo-600 dark:text-indigo-400">Quantum-Secure Document Verification</h1>
  <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">Powered by Quantum Entropy & Post-Quantum Cryptography</p>
  <div class="mt-6 flex justify-center gap-4">
    <a href="#features" class="px-6 py-3 bg-indigo-600 text-white rounded hover:bg-indigo-700">Explore</a>
    <a href="#demo" class="px-6 py-3 border border-indigo-600 text-indigo-600 dark:text-indigo-300 dark:border-indigo-400 rounded hover:bg-indigo-50 dark:hover:bg-gray-800">Try Demo</a>
  </div>
</section>

<!-- Features -->
<section id="features" class="py-20 bg-gray-50 dark:bg-gray-800">
  <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 text-center">
    @foreach ([
      ['🔐', 'Quantum Entropy', 'Unique randomness from QRNG for each document'],
      ['🔏', 'Post-Quantum Security', 'Resilient to quantum decryption with SPHINCS+'],
      ['📄', 'Document Binding', 'Tamper-proof watermark signature logic'],
      ['📶', 'Offline Verification', 'Local verification using public key & hash'],
      ['📬', 'Public Verifier', 'Brandable interface for 3rd-party checks'],
      ['🧾', 'Audit Trail', 'Full revocation and verification history']
    ] as [$emoji, $title, $desc])
    <div>
      <div class="text-indigo-600 dark:text-indigo-400 text-3xl mb-2">{{ $emoji }}</div>
      <h3 class="text-lg font-semibold">{{ $title }}</h3>
      <p class="text-sm text-gray-600 dark:text-gray-300">{{ $desc }}</p>
    </div>
    @endforeach
  </div>
</section>

<!-- CTA -->
<section id="demo" class="bg-indigo-600 dark:bg-indigo-500 text-white py-20 text-center">
  <h2 class="text-3xl font-bold">Start Verifying with Quantum Confidence</h2>
  <p class="mt-2 text-sm text-indigo-100">Upload, Watermark, Verify. All in one secure flow.</p>
  <a href="/dashboard" class="mt-6 inline-block px-8 py-3 bg-white text-indigo-700 rounded font-semibold hover:bg-indigo-100">
    Get Started
  </a>
</section>

<!-- Footer -->
<footer class="py-10 text-center text-sm text-gray-400 dark:text-gray-500">
  &copy; {{ date('Y') }} QMark. Secure. Private. Verifiable.
</footer>

<script>
  function toggleTheme() {
    const html = document.documentElement;
    if (html.classList.contains('dark')) {
      html.classList.remove('dark');
      localStorage.theme = 'light';
    } else {
      html.classList.add('dark');
      localStorage.theme = 'dark';
    }
  }
</script>

</body>
</html>