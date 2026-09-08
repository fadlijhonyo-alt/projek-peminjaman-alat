<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogAktivitasResource extends JsonResource
{
    /**
     * Transform resource menjadi array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name ?? 'Sistem',
                'role' => $this->user?->role,
            ],

            'aktivitas' => $this->aktivitas,

            'waktu' => $this->created_at?->format(
                'Y-m-d H:i:s'
            ),
        ];
    }
}