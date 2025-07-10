<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTitleReignRequest;
use App\Http\Requests\UpdateTitleReignRequest;
use App\Models\TitleReign;
use App\Models\Wrestler;
use App\Services\TitleReignService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class TitleReignController extends Controller
{
    use ApiResponses;

    protected TitleReignService $service;

    public function __construct(TitleReignService $service)
    {
        $this->service = $service;
    }

    public function store(StoreTitleReignRequest $request, Wrestler $wrestler): JsonResponse
    {
        $data = $request->validated();

        // Use the service method which expects a Wrestler model
        $titleReign = $this->service->storeForWrestler($data, $wrestler);

        return $this->success($titleReign, 'Title Reign Created', null, 201);
    }

    public function update(UpdateTitleReignRequest $request, TitleReign $reign): JsonResponse
    {
        $data = $request->validated();

        // Pass the TitleReign model and data array to the service
        $this->service->updateReign($reign, $data);

        // Reload updated model
        $reign->refresh();

        return $this->success($reign, 'Title Reign Updated');
    }

    public function destroy(TitleReign $reign): JsonResponse
    {
        $reign->delete();

        return $this->ok(null, 'Title Reign Deleted');
    }
}
