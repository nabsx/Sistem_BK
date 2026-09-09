<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-mark"><span class="material-symbols-outlined">school</span></div>
        <div><strong>BK Mardisiswa</strong><small>LAYANAN KONSELING</small></div>
    </div>
    <div class="portal-chip"><span class="material-symbols-outlined">verified_user</span>Portal Bimbingan Terpadu</div>
    <nav class="nav-list" aria-label="Navigasi utama">
        <a data-menu-link class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="material-symbols-outlined">dashboard</span>Dashboard</a>
        <a class="nav-item {{ (request()->routeIs('dashboard.module') && request()->route('module') === 'students') || request()->routeIs('dashboard.student*') ? 'active' : '' }}" href="{{ route('dashboard.module', 'students') }}"><span class="material-symbols-outlined">clinical_notes</span>Buku Induk Siswa</a>
        <a class="nav-item {{ request()->routeIs('dashboard.module') && request()->route('module') === 'violations' ? 'active' : '' }}" href="{{ route('dashboard.module', 'violations') }}"><span class="material-symbols-outlined">assignment_late</span>Input Pelanggaran</a>
        <a class="nav-item {{ request()->routeIs('dashboard.module') && request()->route('module') === 'agenda' ? 'active' : '' }}" href="{{ route('dashboard.module', 'agenda') }}"><span class="material-symbols-outlined">event</span>Jadwal &amp; Home Visit</a>
        <a class="nav-item {{ request()->routeIs('dashboard.module') && request()->route('module') === 'assessments' ? 'active' : '' }}" href="{{ route('dashboard.module', 'assessments') }}"><span class="material-symbols-outlined">psychology</span>Asesmen &amp; Karir</a>
        <a class="nav-item {{ request()->routeIs('dashboard.module') && request()->route('module') === 'reports' ? 'active' : '' }}" href="{{ route('dashboard.module', 'reports') }}"><span class="material-symbols-outlined">summarize</span>Rekap &amp; Laporan</a>
        <a class="nav-item {{ request()->routeIs('dashboard.module') && request()->route('module') === 'settings' ? 'active' : '' }}" href="{{ route('dashboard.module', 'settings') }}"><span class="material-symbols-outlined">settings</span>Pengaturan</a>
    </nav>
    <div class="help-card"><span class="material-symbols-outlined">support_agent</span><strong>Bantuan Teknis</strong><p>Hubungi admin IT sekolah jika ada kendala data.</p></div>
</aside>
