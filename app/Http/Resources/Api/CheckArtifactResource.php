<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckArtifactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'check_run_id' => $this->check_run_id,
            'type' => $this->type,
            'path' => $this->path,
            'url' => $this->getUrl(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Get the URL for the artifact
     */
    private function getUrl(): ?string
    {
        if (!$this->path) {
            return null;
        }

        // For public artifacts, return the public URL
        if (\Storage::disk('public')->exists($this->path)) {
            return \Storage::disk('public')->url($this->path);
        }

        // For private artifacts, return a download URL
        return route('api.artifacts.download', ['artifact' => $this->id]);
    }
}
