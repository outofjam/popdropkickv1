<?php

namespace App\ChangeRequestHandlers;

use App\Contracts\ChangeRequestHandlerInterface;
use App\Models\ChangeRequest;
use App\Models\Wrestler;
use App\Services\WrestlerService;
use Exception;

class WrestlerNameChangeRequestHandler implements ChangeRequestHandlerInterface
{
    public function __construct(
        protected WrestlerService $wrestlerService
    ) {}

    public function handle(ChangeRequest $changeRequest): array|bool
    {
        return match ($changeRequest->action) {
            'add_aliases' => $this->handleAddAliases($changeRequest),
            'remove_alias' => $this->handleRemoveAlias($changeRequest),
            default => throw new Exception("Unsupported action for WrestlerName: {$changeRequest->action}"),
        };
    }

    protected function handleAddAliases(ChangeRequest $changeRequest): array
    {
        $wrestler = Wrestler::findOrFail($changeRequest->model_id);
        $aliases = $changeRequest->data;

        return $this->wrestlerService->addAliases($wrestler, $aliases);
    }


    /**
     * @throws Exception
     */
    protected function handleRemoveAlias(ChangeRequest $changeRequest): bool
    {
        $wrestlerId = $changeRequest->data['wrestler_id'] ?? null;
        $aliasId = $changeRequest->model_id;

        if (!$wrestlerId) {
            throw new Exception("Missing wrestler_id for alias removal.");
        }

        $wrestler = Wrestler::findOrFail($wrestlerId);

        return $this->wrestlerService->removeAlias($wrestler, $aliasId);
    }
}
