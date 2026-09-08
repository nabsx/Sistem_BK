@extends('layouts.dashboard')

@section('content')
@if($module === 'students')
<div class="student-directory" data-student-directory>
    <div class="student-directory-head">
        <div>
            <div class="student-breadcrumb"><a href="{{ route('dashboard') }}"><span class="material-symbols-outlined">home</span> Beranda</a><span>/</span><strong>Buku Induk Siswa</strong></div>
            <h1>Buku Induk &amp; Database Siswa</h1>
            <p>Data induk rekam perilaku, kedisiplinan, asesmen minat bakat, dan catatan bimbingan konseling seluruh siswa aktif SMA Mardisiswa.</p>
        </div>
        <div class="student-directory-actions"><a class="soft-action" href="{{ route('dashboard.export.violations') }}"><span class="material-symbols-outlined">download</span>Ekspor Data</a><button class="soft-action" type="button"><span class="material-symbols-outlined">sync</span>Sinkron Dapodik</button><a class="primary-action" href="{{ route('dashboard.student.create') }}"><span class="material-symbols-outlined">person_add</span>Tambah Siswa Baru</a></div>
    </div>

    <div class="student-kpis">
        <article class="student-kpi"><div><span>Total Siswa Aktif</span><strong>{{ $studentMetrics['total'] }}</strong><small><i class="kpi-dot teal"></i>100% Terdaftar di Dapodik</small></div><span class="kpi-icon blue material-symbols-outlined">groups</span></article>
        <article class="student-kpi"><div><span>Status Aman &amp; Teladan</span><strong class="teal-text">{{ $studentMetrics['safe'] }}</strong><small><b>0 - 30 Poin</b> &nbsp; ({{ $studentMetrics['safePercent'] }}%)</small></div><span class="kpi-icon teal material-symbols-outlined">verified</span></article>
        <article class="student-kpi"><div><span>Perhatian / Konseling</span><strong class="navy-text">{{ $studentMetrics['attention'] }}</strong><small><b class="blue-label">31 - 60 Poin</b> &nbsp; ({{ $studentMetrics['attentionPercent'] }}%)</small></div><span class="kpi-icon sky material-symbols-outlined">hearing</span></article>
        <article class="student-kpi"><div><span>Intervensi &amp; Panggilan</span><strong class="red-text">{{ $studentMetrics['intervention'] }}</strong><small><b class="red-label">&gt; 60 Poin</b> &nbsp; ({{ $studentMetrics['interventionPercent'] }}%)</small></div><span class="kpi-icon red material-symbols-outlined">notifications_active</span></article>
    </div>

    <div class="student-toolbar">
        <label class="student-search"><span class="material-symbols-outlined">search</span><input data-student-search type="search" placeholder="Cari berdasarkan nama atau NIS..." aria-label="Cari siswa"><kbd>CTRL+K</kbd></label>
        <select data-student-filter="class" aria-label="Filter kelas"><option value="">Semua Tingkat</option>@foreach($classes as $class)<option value="{{ $class->name }}">{{ $class->name }}</option>@endforeach</select>
        <select data-student-filter="status" aria-label="Filter status"><option value="">Status Perilaku</option><option value="aman">Aman</option><option value="waspada">Waspada</option><option value="perlu pendampingan">Perlu Pendampingan</option></select>
        <button class="sort-button" type="button" data-sort-points>Poin Tertinggi <span class="material-symbols-outlined">sort</span></button>
        <div class="view-toggle"><button class="is-active" type="button" data-view="table" aria-label="Tampilan tabel"><span class="material-symbols-outlined">view_list</span></button><button type="button" data-view="grid" aria-label="Tampilan kartu"><span class="material-symbols-outlined">grid_view</span></button></div>
    </div>

    <section class="student-table-shell" data-student-table>
        <div class="student-table-head"><span>NO</span><span>PROFIL SISWA &amp; IDENTITAS</span><span>KELAS &amp; ROMBEL</span><span>WALI KELAS</span><span>AKUMULASI<br>POIN PERILAKU</span><span>STATUS BIMBINGAN</span><span></span></div>
        @forelse($rows as $index => $row)
        @php($points = (int) $row->discipline_points)
        @php($status = str_replace('_', ' ', $row->discipline_status ?: ($points > 60 ? 'perlu pendampingan' : ($points > 30 ? 'waspada' : 'aman'))))
        @php($initials = collect(explode(' ', trim($row->name)))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode(''))
        <a class="student-table-row" data-student-row data-name="{{ strtolower($row->name) }}" data-nis="{{ $row->nis }}" data-class="{{ $row->schoolClass?->name }}" data-status="{{ strtolower($status) }}" data-points="{{ $points }}" href="{{ route('dashboard.student', $row) }}"><span class="row-number">{{ str_pad($rows->firstItem() + $index, 2, '0', STR_PAD_LEFT) }}</span><span class="student-profile"><span class="student-avatar {{ $points > 60 ? 'danger' : ($points > 30 ? 'warning' : '') }}">{{ $initials }}</span><span><strong>{{ $row->name }}</strong><small>NIS: {{ $row->nis }} <em>·</em> NISN: {{ $row->nisn ?: '-' }}</small></span></span><span><b class="class-chip">{{ $row->schoolClass?->name ?? 'Tanpa kelas' }}</b></span><span class="homeroom"><strong>{{ $row->schoolClass?->homeroomTeacher?->name ?? 'Belum ditentukan' }}</strong><small><span class="material-symbols-outlined">phone</span> Kontak wali kelas</small></span><span class="point-cell"><strong>{{ $points }}</strong><small>{{ $points ? 'Batas: 100' : 'Bebas Catatan' }}</small><i><b style="width: {{ min($points, 100) }}%"></b></i></span><span><b class="behavior-pill {{ $points > 60 ? 'danger' : ($points > 30 ? 'warning' : 'safe') }}"><i></i>{{ ucfirst($status) }}</b></span><span class="detail-link">Detail Induk <span class="material-symbols-outlined">chevron_right</span></span></a>
        @empty
        <div class="empty-state">Belum ada data siswa aktif.</div>
        @endforelse
    </section>
    <div class="student-footer"><span>Menampilkan <strong>{{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }}</strong> dari <strong>{{ $rows->total() }}</strong> siswa</span><span class="sync-status"><i></i> Dapodik sinkron: Hari ini 08:30 WIB</span><div class="student-pagination">{{ $rows->onEachSide(1)->links() }}</div></div>
</div>
@else
<div class="page-wrap"><section class="panel module-panel"><div class="panel-title"><div><h1>{{ $title }}</h1><p>{{ $description }}</p></div><div class="module-actions"><a class="soft-action" href="{{ route('dashboard') }}"><span class="material-symbols-outlined">arrow_back</span>Dashboard</a></div></div><div class="module-table">@forelse($rows as $row)@if($module === 'violations' || $module === 'reports')<div class="module-row"><strong>{{ $row->student?->name ?? 'Siswa tidak ditemukan' }}</strong><span>{{ $row->violationType?->name ?? 'Pelanggaran' }}</span><span>{{ $row->occurred_at?->format('d M Y') ?? '-' }}</span></div>@elseif($module === 'agenda' || $module === 'assessments')<div class="module-row"><strong>{{ $row->student?->name ?? 'Siswa tidak ditemukan' }}</strong><span>{{ $row->topic ?? 'Sesi konseling' }}</span><span>{{ $row->scheduled_at?->format('d M Y H:i') ?? '-' }}</span></div>@else<div class="module-row"><strong>{{ $row->name }}</strong><span>{{ $row->category ?? 'Umum' }}</span><span>Aktif</span></div>@endif @empty<div class="empty-state">Belum ada data untuk modul ini.</div>@endforelse</div>@if(method_exists($rows, 'links'))<div class="pagination-wrap">{{ $rows->links() }}</div>@endif</section></div>
@endif
@endsection
