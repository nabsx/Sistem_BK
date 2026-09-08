@extends('layouts.dashboard')

@section('content')
<div class="page-wrap form-page">
    <div class="page-heading-row">
        <div><span class="eyebrow eyebrow-light"><span class="material-symbols-outlined">clinical_notes</span>Buku Induk Siswa</span><h1>Tambah Siswa</h1><p>Tambahkan data siswa baru ke Buku Induk dari database sekolah.</p></div>
        <a class="soft-action" href="{{ route('dashboard.module', 'students') }}"><span class="material-symbols-outlined">arrow_back</span>Kembali</a>
    </div>

    @if ($errors->any())
        <div class="form-alert"><strong>Periksa kembali data yang diisi.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('dashboard.student.store') }}" class="student-form">
        @csrf
        <section class="form-card"><div class="form-card-heading"><span class="step-number">01</span><div><h2>Identitas Siswa</h2><p>Informasi utama siswa yang tercatat di sekolah.</p></div></div><div class="form-grid">
            <label>NIS<input name="nis" value="{{ old('nis') }}" required></label>
            <label>NISN<input name="nisn" value="{{ old('nisn') }}"></label>
            <label class="span-2">Nama lengkap<input name="name" value="{{ old('name') }}" required></label>
            <label>Jenis kelamin<select name="gender" required><option value="">Pilih</option><option value="L" @selected(old('gender') === 'L')>Laki-laki</option><option value="P" @selected(old('gender') === 'P')>Perempuan</option></select></label>
            <label>Tanggal lahir<input type="date" name="birth_date" value="{{ old('birth_date') }}"></label>
        </div></section>
        <section class="form-card"><div class="form-card-heading"><span class="step-number">02</span><div><h2>Kelas dan Wali</h2><p>Hubungkan siswa ke kelas serta kontak wali.</p></div></div><div class="form-grid">
            <label class="span-2">Kelas<select name="school_class_id" required><option value="">Pilih kelas</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected(old('school_class_id') === $class->id)>{{ $class->name }} · {{ $class->academic_year }}</option>@endforeach</select></label>
            <label>Nama wali<input name="guardian_name" value="{{ old('guardian_name') }}"></label>
            <label>Hubungan<input name="guardian_relation" value="{{ old('guardian_relation') }}" placeholder="Ayah / Ibu / Wali"></label>
            <label class="span-2">Nomor telepon wali<input name="guardian_phone" value="{{ old('guardian_phone') }}"></label>
        </div></section>
        <div class="form-actions"><a class="soft-action" href="{{ route('dashboard.module', 'students') }}">Batal</a><button class="primary-action" type="submit"><span class="material-symbols-outlined">save</span>Simpan Siswa</button></div>
    </form>
</div>
@endsection
