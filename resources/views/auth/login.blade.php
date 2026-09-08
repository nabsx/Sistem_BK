<!DOCTYPE html>
<html lang="id" class="min-h-full bg-[#f8faff]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — BK SMA Mardisiswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { color-scheme: light; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .login-pattern { background-image: radial-gradient(#cbd5e1 0.75px, transparent 0.75px); background-size: 16px 16px; }
    </style>
</head>
<body class="min-h-screen bg-[#f8faff] text-slate-800">
<main class="login-pattern flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-8 lg:px-12">
    <div class="grid w-full max-w-6xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70 lg:grid-cols-12">
        <section class="relative flex min-h-[460px] flex-col justify-center overflow-hidden bg-gradient-to-br from-[#1e3a8a] via-[#1e40af] to-[#172554] px-8 py-10 text-white sm:px-11 lg:col-span-5">
            <img src="{{ asset('images/login-campus.png') }}" alt="Gedung sekolah SMA Mardisiswa" class="absolute inset-0 h-full w-full object-cover opacity-20 mix-blend-overlay">
            <div class="absolute inset-0 bg-gradient-to-t from-[#172554]/95 via-[#1e3a8a]/75 to-[#1e40af]/55"></div>
            <div class="relative z-10">
                <div class="mb-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-lg font-extrabold ring-1 ring-white/25">BK</div>
                    <div><strong class="block text-sm">BK Mardisiswa</strong><span class="text-[10px] tracking-[.18em] text-blue-100">LAYANAN KONSELING</span></div>
                </div>
                <h1 class="text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Membimbing Potensi,<br><span class="text-sky-300">Membangun Karakter Terpuji</span></h1>
                <p class="mt-4 max-w-md text-sm leading-6 text-blue-100">Sistem terintegrasi pendampingan konseling, monitoring kedisiplinan berkeadilan restoratif, dan pemetaan minat karir siswa SMA Mardisiswa.</p>
                <div class="mt-9 space-y-4">
                    @foreach ([['✓','Pendekatan Mediasi Restoratif','Pencatatan poin terukur dengan solusi pembinaan tanpa stigma.'],['↯','Notifikasi Real-time Terintegrasi','Sinergi langsung antara Guru Piket, Wali Kelas, dan Orang Tua.'],['◔','Asesmen Minat Holland RIASEC & AKPD','Bimbingan penjurusan dan studi lanjut berbasis data valid.']] as $feature)
                        <div class="flex items-start gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-sky-300/30 bg-sky-400/15 text-xs text-sky-200">{{ $feature[0] }}</span><div><strong class="block text-xs">{{ $feature[1] }}</strong><span class="text-[11px] leading-4 text-blue-100">{{ $feature[2] }}</span></div></div>
                    @endforeach
                </div>
            </div>
        </section>
        <section class="flex flex-col justify-center px-7 py-10 sm:px-12 lg:col-span-7 lg:py-16">
            <div class="mb-8 flex items-start justify-between gap-4"><div><h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Masuk ke Akun Anda</h2><p class="mt-2 text-xs leading-5 text-slate-500 sm:text-sm">Silakan masukkan kredensial resmi sekolah untuk melanjutkan.</p></div><span class="shrink-0 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">T.A. 2024/2025</span></div>
            @if ($errors->any())<div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">@csrf
                <div><label for="identity" class="mb-2 block text-xs font-bold text-slate-700">NIP / NUPTK / Akun SSO Madrasah</label><div class="relative"><span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">♙</span><input id="identity" name="identity" value="{{ old('identity') }}" required autofocus autocomplete="username" placeholder="Masukkan NIP atau Email Akun" class="w-full rounded-lg border border-slate-300 bg-slate-50 py-3 pl-10 pr-4 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-100"></div></div>
                <div><div class="mb-2 flex items-center justify-between"><label for="password" class="text-xs font-bold text-slate-700">Kata Sandi</label><a href="#" class="text-xs font-semibold text-blue-700 hover:underline">Lupa kata sandi?</a></div><div class="relative"><span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">▣</span><input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan kata sandi akun" class="w-full rounded-lg border border-slate-300 bg-slate-50 py-3 pl-10 pr-12 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-100"><button type="button" data-password-toggle class="absolute inset-y-0 right-0 px-4 text-slate-400" aria-label="Tampilkan kata sandi">◉</button></div></div>
                <div class="flex items-center justify-between gap-3"><label class="flex items-center gap-2 text-xs font-medium text-slate-600"><input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500">Ingat sesi perangkat ini (30 hari)</label><span class="hidden text-[11px] text-slate-500 sm:inline"><span class="text-emerald-600">▣</span> Enkripsi SSL 256-bit</span></div>
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#1e3a8a] px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-900/25 transition hover:-translate-y-0.5 hover:bg-[#1e40af]">Masuk ke Sistem BK <span>→</span></button>
            </form>
        </section>
    </div>
</main>
</body>
</html>
