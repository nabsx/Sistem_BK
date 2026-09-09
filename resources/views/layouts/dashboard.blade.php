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
                <div class="year-chip" aria-label="Tahun ajaran 2024 sampai 2025, semester ganjil">
                    <span class="material-symbols-outlined">school</span>
                    <span>TA 2024/2025<small>Ganjil</small></span>
                </div>
                <a class="case-button" href="{{ route('dashboard.module', 'violations') }}"><span class="material-symbols-outlined">add_circle</span>Catat Kasus</a>
                <button class="icon-button" type="button" aria-label="Notifikasi" title="Notifikasi">
                    <span class="material-symbols-outlined">notifications_none</span><i aria-hidden="true"></i>
                </button>
                <div class="profile-menu" data-profile-menu>
                    <button class="profile profile-trigger" type="button" data-profile-toggle aria-expanded="false" aria-haspopup="true">
                        <span class="avatar">{{ collect(explode(' ', auth()->user()->name))->map(fn($part) => substr($part, 0, 1))->take(2)->implode('') }}</span>
                        <span><strong>{{ auth()->user()->name }}</strong><small>Guru BK</small></span>
                        <span class="material-symbols-outlined profile-chevron">expand_more</span>
                    </button>
                    <div class="profile-dropdown" data-profile-dropdown hidden>
                        <div class="profile-dropdown-heading"><strong>{{ auth()->user()->name }}</strong><small>Guru BK</small></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="profile-logout"><span class="material-symbols-outlined">logout</span>Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        @yield('content')
    </main>
</div>
</body>
</html>
