<?php

namespace App\Http\Resources;

use App\Models\User;

class ChangeRequestResource extends BaseResource
{
    public function toArray($request): array
    {
        return array_merge(
            [
                'id' => $this->resource->id,
                'action' => $this->resource->action,
                'model_type' => $this->resource->model_type,
                'model_id' => $this->resource->model_id,
                'data' => $this->resource->data,
                'original_data' => $this->resource->original_data,
                'status' => $this->resource->status,
                'reviewer_comments' => $this->resource->reviewer_comments,
                'reviewed_at' => $this->formatTimestamp($this->resource->reviewed_at),
                'user' => $this->whenLoaded('user', fn () => $this->formatUserReference($this->resource->user)),
                'reviewer' => $this->whenLoaded('reviewer', fn () => $this->resource->reviewer
                    ? $this->formatUserReference($this->resource->reviewer)
                    : null),
            ],
            $this->formatTimestamps()
        );
    }

    private function formatUserReference(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
