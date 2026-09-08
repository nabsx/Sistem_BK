@extends('layouts.dashboard')

@section('content')
<div class="page-wrap">
    <a href="{{ route('dashboard') }}">← Kembali ke Dashboard</a>
    <section class="panel" style="padding:32px;margin-top:24px">
        <h1>Hasil pencarian siswa</h1>
        <form class="search-box" action="{{ route('dashboard.search') }}">
            <input name="q" value="{{ $term }}" placeholder="Nama, NIS, atau NISN">
            <button class="case-button">Cari</button>
        </form>
        <div class="module-table">
            @forelse($students as $student)
                <a class="module-row" href="{{ route('dashboard.module', 'students') }}"><strong>{{ $student->name }}</strong><span>{{ $student->nis }} · {{ $student->schoolClass?->name }}</span><span>{{ $student->discipline_points }} poin</span></a>
            @empty
                <div class="empty-state">Tidak ada siswa yang cocok dengan pencarian.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
