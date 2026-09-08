<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentViolationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi granular tetap dijaga oleh middleware role:permission di route,
        // di sini cukup pastikan user sudah lolos guard tersebut.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'uuid', 'exists:students,id'],
            'violation_type_id' => ['required', 'uuid', 'exists:violation_types,id'],

            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'image', 'max:5120'], // max 5MB

            'notify_homeroom_teacher' => ['boolean'],
            'notify_guardian' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.exists' => 'Data siswa tidak ditemukan.',
            'violation_type_id.exists' => 'Jenis pelanggaran tidak valid.',
            'evidence.max' => 'Ukuran file bukti maksimal 5MB.',
        ];
    }
}