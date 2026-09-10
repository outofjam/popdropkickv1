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

        $reign = $this->service->storeForWrestler($data, $wrestler);

        return $this->success($reign, 'Title Reign Created', null, 201);
    }

    public function update(UpdateTitleReignRequest $request, TitleReign $reign): JsonResponse
    {
        $reign = $this->service->updateReign($reign, $request->validated());

        return $this->success($reign, 'Title Reign Updated');
    }

    public function destroy(TitleReign $reign): JsonResponse
    {
        $reign->delete();

        return $this->ok(null, 'Title Reign Deleted');
    }
}
