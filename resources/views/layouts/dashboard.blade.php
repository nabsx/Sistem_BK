<!doctype html>
<html lang="id" class="bg-[#f5f8fc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} · BK Mardisiswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-body">
<div class="app-shell">
    @include('layouts.partials.sidebar')
    <main class="main-content">
        <header class="topbar">
            <button class="mobile-menu" data-menu-toggle aria-label="Buka menu"><span class="material-symbols-outlined">menu</span></button>
            <form class="search-box" action="{{ route('dashboard.search') }}">
                <span class="material-symbols-outlined">search</span>
                <input name="q" aria-label="Cari siswa" placeholder="Cari siswa, NIS, kelas..." value="{{ request('q') }}">
            </form>
            <div class="top-actions">
                <a class="soft-action" href="{{ route('dashboard.export.violations') }}"><span class="material-symbols-outlined">download</span>Ekspor Pelanggaran</a>
                <div class="profile"><div class="avatar">{{ collect(explode(' ', auth()->user()->name))->map(fn($part) => substr($part, 0, 1))->take(2)->implode('') }}</div><span><strong>{{ auth()->user()->name }}</strong><small>Guru BK</small></span></div>
            </div>
        </header>
        @yield('content')
    </main>
</div>
</body>
</html>
