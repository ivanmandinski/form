<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormTargetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'target_id' => $this->target_id,
            'selector_type' => $this->selector_type,
            'selector_value' => $this->selector_value,
            'method_override' => $this->method_override,
            'action_override' => $this->action_override,
            'driver_type' => $this->driver_type,
            'uses_js' => $this->uses_js,
            'recaptcha_expected' => $this->recaptcha_expected,
            'success_selector' => $this->success_selector,
            'error_selector' => $this->error_selector,
            'schedule_enabled' => $this->schedule_enabled,
            'schedule_frequency' => $this->schedule_frequency,
            'schedule_cron' => $this->schedule_cron,
            'schedule_timezone' => $this->schedule_timezone,
            'schedule_next_run_at' => $this->schedule_next_run_at?->toISOString(),
            'last_scheduled_run_at' => $this->last_scheduled_run_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'target' => new TargetResource($this->whenLoaded('target')),
            'field_mappings' => FieldMappingResource::collection($this->whenLoaded('fieldMappings')),
            'latest_check_run' => new CheckRunResource($this->whenLoaded('checkRuns')),
            'check_runs_count' => $this->when($this->relationLoaded('checkRuns'), function () {
                return $this->checkRuns->count();
            }),
        ];
    }
}
