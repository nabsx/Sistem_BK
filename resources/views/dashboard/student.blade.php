@extends('layouts.dashboard')

@section('content')
<div class="student-page">
    <div class="student-breadcrumb">
        <a href="{{ route('dashboard') }}"><span class="material-symbols-outlined">home</span> Beranda</a><span>/</span>
        <a href="{{ route('dashboard.module', 'students') }}">Buku Induk Siswa</a><span>/</span>
        <strong>Detail Siswa: {{ $student->name }}</strong>
        <span class="student-chip">NIS: {{ $student->nis }} · {{ $student->schoolClass?->name ?? 'Kelas belum diatur' }}</span>
        <span class="sync-status"><i></i> Sinkron terakhir: {{ $student->dapodik_synced_at?->format('d M Y, H:i').' WIB' ?? 'Belum pernah' }}</span>
    </div>

    <section class="student-hero-card">
        <div class="student-identity">
            <div class="student-photo-wrap">
                @if($student->photo_path)
                    <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Foto {{ $student->name }}" class="student-photo">
                @else
                    <div class="student-photo student-photo-fallback">{{ collect(explode(' ', $student->name))->map(fn($part) => substr($part, 0, 1))->take(2)->implode('') }}</div>
                @endif
                <span class="verified-mark material-symbols-outlined">verified</span>
            </div>
            <div class="student-identity-copy">
                <div class="identity-meta"><span class="status-pill">{{ ucfirst($student->status) }}</span><span>NISN: {{ $student->nisn ?: 'Belum diisi' }}</span></div>
                <h1>{{ $student->name }}</h1>
                <p>Kelas: <strong>{{ $student->schoolClass?->name ?? 'Belum diatur' }}</strong></p>
                <div class="identity-details">
                    <span><span class="material-symbols-outlined">supervisor_account</span> Wali: <strong>{{ $student->guardian_name ?: 'Belum diisi' }}</strong></span>
                    @if($student->guardian_phone)<a href="tel:{{ $student->guardian_phone }}"><span class="material-symbols-outlined">contact_phone</span>{{ $student->guardian_phone }}</a>@endif
                </div>
            </div>
        </div>
        <div class="discipline-card">
            <div class="discipline-heading"><div><small>AKUMULASI POIN DISIPLIN</small><strong>{{ $totalPoints }}<em>/ 100 Poin</em></strong></div><span class="risk-badge material-symbols-outlined">{{ $totalPoints >= 60 ? 'warning' : 'check_circle' }} {{ $totalPoints >= 60 ? 'Perlu pendampingan' : 'Aman' }}</span></div>
            <div class="point-meter"><span style="width: {{ min($totalPoints, 100) }}%"></span></div>
            <div class="meter-labels"><span>0–30 Aman</span><span>31–60 Waspada</span><span>75+ Prioritas</span></div>
            <p><span class="material-symbols-outlined">info</span>{{ $totalPoints >= 75 ? 'Siswa sudah masuk ambang prioritas pendampingan.' : 'Data poin dihitung langsung dari riwayat pelanggaran.' }}</p>
        </div>
        <div class="student-actions-panel">
            <div class="student-stats"><div><strong>{{ $attendance ?? '—' }}</strong><small>Kehadiran</small></div><div><strong>{{ $violations->count() }}</strong><small>Pelanggaran</small></div><div><strong>{{ $sessions->count() }}</strong><small>Sesi BK</small></div><div><strong>{{ $student->discipline_status ? ucfirst(str_replace('_', ' ', $student->discipline_status)) : '—' }}</strong><small>Status</small></div></div>
            <div class="student-actions"><a href="{{ route('dashboard.module', ['module' => 'agenda', 'student' => $student->id]) }}" class="primary-action"><span class="material-symbols-outlined">add_circle</span> Jadwal Konseling</a><a href="{{ route('dashboard.module', ['module' => 'violations', 'student' => $student->id]) }}" class="light-action"><span class="material-symbols-outlined">warning</span> Catat Kasus</a><a href="{{ route('dashboard.student.export', $student) }}" class="icon-action" title="Unduh rekap siswa"><span class="material-symbols-outlined">download</span></a></div>
        </div>
    </section>

    <nav class="student-tabs" aria-label="Bagian buku induk">
        <a class="active" href="#violations"><span class="material-symbols-outlined">assignment_late</span> Riwayat Pelanggaran <b>{{ $violations->count() }}</b></a>
        <a href="#sessions"><span class="material-symbols-outlined">event_busy</span> Riwayat Konseling <b>{{ $sessions->count() }}</b></a>
        <a href="#profile"><span class="material-symbols-outlined">badge</span> Data Profil</a>
    </nav>

    <div class="student-content-grid">
        <section class="student-panel" id="violations">
            <div class="panel-heading"><div><h2>Kronologi Rekam Kedisiplinan</h2><p>{{ $violations->count() }} kejadian tercatat dari database sekolah.</p></div><a href="{{ route('dashboard.export.violations') }}" class="panel-icon-action" title="Ekspor riwayat"><span class="material-symbols-outlined">download</span></a></div>
            @forelse($violations as $violation)
                <article class="violation-row"><div class="date-column"><strong>{{ $violation->occurred_at?->format('d M Y') }}</strong><small>{{ $violation->occurred_at?->format('H:i') }} WIB</small></div><div class="violation-copy"><strong>{{ $violation->violationType?->name ?? 'Pelanggaran' }}</strong><p>{{ $violation->notes ?: 'Tidak ada catatan tambahan.' }}</p></div><span class="points-badge">+{{ $violation->point_snapshot }} poin</span><div class="reporter"><strong>{{ $violation->reporter?->name ?? 'Tidak diketahui' }}</strong><small>{{ str_replace('_', ' ', ucfirst($violation->action_status)) }}</small></div></article>
            @empty
                <div class="empty-state"><span class="material-symbols-outlined">verified</span><strong>Belum ada pelanggaran</strong><p>Riwayat kedisiplinan siswa akan tampil di sini.</p></div>
            @endforelse
        </section>
        <aside class="student-side-column">
            <section class="student-panel" id="profile"><div class="panel-heading"><div><h2>Data Profil</h2><p>Informasi dasar siswa aktif.</p></div><span class="material-symbols-outlined panel-heading-icon">badge</span></div><dl class="profile-list"><div><dt>NIS</dt><dd>{{ $student->nis }}</dd></div><div><dt>NISN</dt><dd>{{ $student->nisn ?: 'Belum diisi' }}</dd></div><div><dt>Jenis Kelamin</dt><dd>{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div><div><dt>Tanggal Lahir</dt><dd>{{ $student->birth_date?->format('d M Y') ?: 'Belum diisi' }}</dd></div><div><dt>Hubungan Wali</dt><dd>{{ $student->guardian_relation ?: 'Belum diisi' }}</dd></div></dl></section>
            <section class="student-panel" id="sessions"><div class="panel-heading"><div><h2>Sesi Konseling Terakhir</h2><p>Agenda dan catatan pendampingan.</p></div><span class="session-count">{{ $sessions->count() }} sesi</span></div>@forelse($sessions->take(3) as $session)<div class="session-row"><div><strong>{{ $session->topic ?: 'Sesi konseling' }}</strong><small>{{ $session->scheduled_at?->format('d M Y, H:i') }} · {{ ucfirst($session->status) }}</small></div><span class="material-symbols-outlined">chevron_right</span></div>@empty<div class="empty-state compact"><span class="material-symbols-outlined">forum</span><p>Belum ada sesi konseling.</p></div>@endforelse</section>
        </aside>
    </div>
</div>
@endsection
