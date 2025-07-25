<?php

namespace App\Services;

use App\Models\ChangeRequest;
use App\Models\User;
use App\Models\Wrestler;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ChangeRequestService
{
    protected WrestlerService $wrestlerService;

    public function __construct(WrestlerService $wrestlerService)
    {
        $this->wrestlerService = $wrestlerService;
    }

    public function create(array $data): ChangeRequest
    {
        return ChangeRequest::create($data);
    }

    /**
     * @throws Throwable
     */
    public function approve(ChangeRequest $changeRequest, array $reviewData = []): mixed
    {
        if ($changeRequest->status !== 'pending') {
            throw new RuntimeException('Change request has already been reviewed');
        }

        DB::beginTransaction();

        try {
            // Execute the actual change
            $result = match($changeRequest->action) {
                'create' => $this->executeCreate($changeRequest),
                'update' => $this->executeUpdate($changeRequest),
                'delete' => $this->executeDelete($changeRequest),
            };

            // Update the change request
            $changeRequest->update([
                'status' => 'approved',
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now(),
                'reviewer_comments' => $reviewData['comments'] ?? null
            ]);

            // Update user reputation
            $this->updateUserReputation($changeRequest->user, 'approved');

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(ChangeRequest $changeRequest, array $reviewData = []): void
    {
        $changeRequest->update([
            'status' => 'rejected',
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_comments' => $reviewData['comments'] ?? 'No reason provided'
        ]);

        $this->updateUserReputation($changeRequest->user, 'rejected');
    }

    public function generateDiff(array $original, array $new): array
    {
        $diff = [];

        foreach ($new as $key => $value) {
            $originalValue = $original[$key] ?? null;

            if ($originalValue !== $value) {
                $diff[$key] = [
                    'old' => $originalValue,
                    'new' => $value,
                    'changed' => true
                ];
            }
        }

        return $diff;
    }

    /**
     * @throws Exception
     */
    protected function executeCreate(ChangeRequest $changeRequest): mixed
    {
        return match($changeRequest->model_type) {
            'wrestler' => $this->wrestlerService->create($changeRequest->data),
            // Add other model types as you implement them
            default => throw new Exception("Unsupported model type: {$changeRequest->model_type}"),
        };
    }

    /**
     * @throws Exception
     */
    protected function executeUpdate(ChangeRequest $changeRequest): mixed
    {
        return match($changeRequest->model_type) {
            'wrestler' => $this->wrestlerService->update(
                Wrestler::findOrFail($changeRequest->model_id),
                $changeRequest->data
            ),
            // Add other model types as you implement them
            default => throw new Exception("Unsupported model type: {$changeRequest->model_type}"),
        };
    }

    /**
     * @throws Exception
     */
    protected function executeDelete(ChangeRequest $changeRequest): bool
    {
        return match($changeRequest->model_type) {
            'wrestler' => Wrestler::findOrFail($changeRequest->model_id)->delete(),
            // Add other model types as you implement them
            default => throw new Exception("Unsupported model type: {$changeRequest->model_type}"),
        };
    }

    private function updateUserReputation(User $user, string $outcome): void
    {
        $points = match($outcome) {
            'approved' => 5,
            'rejected' => -2,
            default => 0
        };

        // Either of these approaches work:
        $user->increment('reputation_score', $points);

        // Or more explicit:
        // $user->reputation_score += $points;
        // $user->save();
    }
}
