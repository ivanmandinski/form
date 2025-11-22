<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckRunResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_target_id' => $this->form_target_id,
            'driver' => $this->driver,
            'status' => $this->status,
            'http_status' => $this->http_status,
            'final_url' => $this->final_url,
            'message_excerpt' => $this->message_excerpt,
            'error_detail' => $this->error_detail,
            'debug_info' => $this->debug_info,
            'started_at' => $this->started_at?->toISOString(),
            'finished_at' => $this->finished_at?->toISOString(),
            'duration_seconds' => $this->started_at && $this->finished_at 
                ? $this->started_at->diffInSeconds($this->finished_at) 
                : null,
            'is_successful' => $this->isSuccessful(),
            'is_blocked' => $this->isBlocked(),
            'is_error' => $this->isError(),
            'form_target' => new FormTargetResource($this->whenLoaded('formTarget')),
        ];
    }
}
