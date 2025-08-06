<?php

namespace App\ChangeRequestHandlers;

use App\Contracts\ChangeRequestHandlerInterface;
use App\Models\Championship;
use App\Models\ChangeRequest;
use App\Models\Promotion;
use App\Services\ChampionshipService;
use Exception;
use RuntimeException;

class ChampionshipChangeRequestHandler implements ChangeRequestHandlerInterface
{
    public function __construct(protected ChampionshipService $championshipService)
    {
    }

    public function handle(ChangeRequest $changeRequest): Championship|bool|null
    {
        return match ($changeRequest->action) {
            'create' => $this->handleCreate($changeRequest),
            'update' => $this->handleUpdate($changeRequest),
            'delete' => Championship::findOrFail($changeRequest->model_id)->delete(),
            default => throw new Exception("Unsupported action: {$changeRequest->action}"),
        };
    }

    protected function handleCreate(ChangeRequest $changeRequest): Championship
    {
        // Need Promotion model to create championship under it
        $promotionId = $changeRequest->data['promotion_id'] ?? null;

        if (!$promotionId) {
            throw new RuntimeException('promotion_id is required to create a Championship.');
        }

        $promotion = Promotion::findOrFail($promotionId);

        // Remove promotion_id from data before passing (if you want)
        $data = $changeRequest->data;
        unset($data['promotion_id']);

        return $this->championshipService->createChampionship($promotion, $data);
    }

    protected function handleUpdate(ChangeRequest $changeRequest): Championship
    {
        $championship = Championship::findOrFail($changeRequest->model_id);

        return $this->championshipService->updateChampionship($championship, $changeRequest->data);
    }
}
