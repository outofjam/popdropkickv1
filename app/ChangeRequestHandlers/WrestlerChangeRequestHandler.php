<?php

// app/ChangeRequestHandlers/WrestlerChangeRequestHandler.php
namespace App\ChangeRequestHandlers;

use App\Contracts\ChangeRequestHandlerInterface;
use App\Models\ChangeRequest;
use App\Models\Wrestler;
use App\Services\WrestlerService;
use Exception;

class WrestlerChangeRequestHandler implements ChangeRequestHandlerInterface
{
    protected WrestlerService $wrestlerService;

    public function __construct(WrestlerService $wrestlerService)
    {
        $this->wrestlerService = $wrestlerService;
    }

    public function handle(ChangeRequest $changeRequest): mixed
    {
        return match ($changeRequest->action) {
            'create' => $this->wrestlerService->create($changeRequest->data),
            'update' => $this->wrestlerService->update(
                Wrestler::findOrFail($changeRequest->model_id),
                $changeRequest->data
            ),
            'delete' => Wrestler::findOrFail($changeRequest->model_id)->delete(),
            default => throw new Exception("Unsupported action: {$changeRequest->action}"),
        };
    }
}
