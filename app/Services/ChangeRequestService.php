<?php

namespace App\Services;

use App\ChangeRequestHandlers\ChampionshipChangeRequestHandler;
use App\ChangeRequestHandlers\PromotionChangeRequestHandler;
use App\ChangeRequestHandlers\WrestlerChangeRequestHandler;
use App\ChangeRequestHandlers\WrestlerNameChangeRequestHandler;
use App\Contracts\ChangeRequestHandlerInterface;
use App\Models\ChangeRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ChangeRequestService
{
    public function __construct(
        protected WrestlerChangeRequestHandler $wrestlerHandler,
        protected PromotionChangeRequestHandler $promotionHandler,
        protected WrestlerNameChangeRequestHandler $wrestlerNameHandler,
        protected ChampionshipChangeRequestHandler $championshipHandler,
        // Inject others later
    ) {
    }


    /**
     * @throws Exception
     */
    protected function resolveHandler(string $modelType): ChangeRequestHandlerInterface
    {
        return match ($modelType) {
            'wrestler' => $this->wrestlerHandler,
            'promotion' => $this->promotionHandler,
            'wrestler_name' => $this->wrestlerNameHandler,
            'championship' => $this->championshipHandler,
            default => throw new Exception("Unsupported model type: {$modelType}"),
        };
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
            $handler = $this->resolveHandler($changeRequest->model_type);
            $result = $handler->handle($changeRequest);

            $changeRequest->update([
                'status' => 'approved',
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now(),
                'reviewer_comments' => $reviewData['comments'] ?? null
            ]);

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

    private function updateUserReputation(User $user, string $outcome): void
    {
        $points = match ($outcome) {
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
