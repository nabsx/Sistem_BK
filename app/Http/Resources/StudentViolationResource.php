<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentViolationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'occurred_at' => $this->occurred_at->format('Y-m-d H:i'),
            'location' => $this->location,
            'notes' => $this->notes,
            'point_snapshot' => $this->point_snapshot,
            'action_status' => $this->action_status,
            'evidence_url' => $this->evidence_path ? asset('storage/' . $this->evidence_path) : null,

            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->name,
                'nis' => $this->student->nis,
                'class' => $this->student->schoolClass?->name,
                'discipline_points' => $this->student->discipline_points,
                'discipline_status' => $this->student->discipline_status,
            ],

            'violation_type' => [
                'id' => $this->violationType->id,
                'name' => $this->violationType->name,
                'category' => $this->violationType->category,
            ],

            'reporter' => [
                'id' => $this->reporter->id,
                'name' => $this->reporter->name,
            ],

            'notified_homeroom_teacher' => $this->notified_homeroom_teacher,
            'notified_guardian' => $this->notified_guardian,
        ];
    }
}