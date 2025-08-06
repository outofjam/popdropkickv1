<?php

// app/ChangeRequestHandlers/PromotionChangeRequestHandler.php
namespace App\ChangeRequestHandlers;

use App\Contracts\ChangeRequestHandlerInterface;
use App\Models\ChangeRequest;
use App\Models\Promotion;
use App\Services\PromotionService;
use Exception;

class PromotionChangeRequestHandler implements ChangeRequestHandlerInterface
{
    protected PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * @throws Exception
     */
    public function handle(ChangeRequest $changeRequest): mixed
    {
        return match ($changeRequest->action) {
            'create' => $this->promotionService->create($changeRequest->data),
//            'update' => $this->promotionService->update(
//                Promotion::findOrFail($changeRequest->model_id),
//                $changeRequest->data
//            ),
            'delete' => Promotion::findOrFail($changeRequest->model_id)->delete(),
            default => throw new Exception("Unsupported action: {$changeRequest->action}"),
        };
    }
}
