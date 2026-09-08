@extends('layouts.dashboard')

@section('content')
<div class="page-wrap">
    <section class="panel module-panel">
        <div class="panel-title"><div><h1>{{ $title }}</h1><p>{{ $description }}</p></div><a class="primary-action" href="{{ route('dashboard') }}"><span class="material-symbols-outlined">arrow_back</span>Dashboard</a></div>
        <div class="module-table">
            @forelse($rows as $row)
                @if($module === 'students')
                    <a class="module-row" href="{{ route('dashboard.student', $row) }}"><strong>{{ $row->name }}</strong><span>{{ $row->nis }} · {{ $row->schoolClass?->name ?? 'Tanpa kelas' }}</span><span>{{ $row->discipline_points }} poin <span class="material-symbols-outlined row-arrow">chevron_right</span></span></a>
                @elseif($module === 'violations' || $module === 'reports')
                    <div class="module-row"><strong>{{ $row->student?->name ?? 'Siswa tidak ditemukan' }}</strong><span>{{ $row->violationType?->name ?? 'Pelanggaran' }}</span><span>{{ $row->occurred_at?->format('d M Y') ?? '-' }}</span></div>
                @elseif($module === 'agenda' || $module === 'assessments')
                    <div class="module-row"><strong>{{ $row->student?->name ?? 'Siswa tidak ditemukan' }}</strong><span>{{ $row->topic ?? 'Sesi konseling' }}</span><span>{{ $row->scheduled_at?->format('d M Y H:i') ?? '-' }}</span></div>
                @else
                    <div class="module-row"><strong>{{ $row->name }}</strong><span>{{ $row->category ?? 'Umum' }}</span><span>Aktif</span></div>
                @endif
            @empty
                <div class="empty-state">Belum ada data untuk modul ini.</div>
            @endforelse
        </div>
        @if(method_exists($rows, 'links'))<div class="pagination-wrap">{{ $rows->links() }}</div>@endif
    </section>
</div>
@endsection
