<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveChangeRequestRequest;
use App\Http\Requests\BulkApproveChangeRequestsRequest;
use App\Http\Requests\IndexChangeRequestsRequest;
use App\Http\Requests\RejectChangeRequestRequest;
use App\Http\Requests\ShowChangeRequestRequest;
use App\Http\Resources\ChangeRequestResource;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\JsonResponse;

class ChangeRequestController extends Controller
{
    use ApiResponses;

    protected ChangeRequestService $service;

    public function __construct(ChangeRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * Get pending change requests for review
     *
     * @group Change Requests
     *
     * @authenticated
     */
    public function index(IndexChangeRequestsRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 15;

        $changeRequests = $this->service->getPaginated($filters, $perPage);

        return $this->ok(ChangeRequestResource::collection($changeRequests));
    }

    /**
     * Get a specific change request with detailed diff
     */
    public function show(ShowChangeRequestRequest $request, ChangeRequest $changeRequest): JsonResponse
    {
        $changeRequest->load(['user', 'reviewer']);

        // Add diff information for updates
        $diff = null;
        if ($changeRequest->action === 'update' && $changeRequest->original_data) {
            $diff = $this->service->generateDiff(
                $changeRequest->original_data,
                $changeRequest->data
            );
        }

        return $this->success(
            new ChangeRequestResource($changeRequest),
            null,
            compact('diff')
        );
    }

    /**
     * Approve a change request
     */
    public function approve(ApproveChangeRequestRequest $request, ChangeRequest $changeRequest): JsonResponse
    {
        try {
            $result = $this->service->approve($changeRequest, $request->validated());

            return $this->success([
                'change_request' => new ChangeRequestResource($changeRequest->fresh(['user', 'reviewer'])),
                'created_resource' => $result,
            ], 'Change request approved successfully');
        } catch (Exception $e) {
            return $this->error('Failed to approve change request: '.$e->getMessage(), 422);
        }
    }

    /**
     * Reject a change request
     */
    public function reject(RejectChangeRequestRequest $request, ChangeRequest $changeRequest): JsonResponse
    {
        $this->service->reject($changeRequest, $request->validated());

        return $this->success(
            new ChangeRequestResource($changeRequest->fresh(['user', 'reviewer'])),
            'Change request rejected'
        );
    }

    /**
     * Bulk approve multiple change requests
     */
    public function bulkApprove(BulkApproveChangeRequestsRequest $request): JsonResponse
    {
        $data = $request->validated();

        ['results' => $results, 'errors' => $errors] = $this->service->bulkApprove(
            $data['change_request_ids'],
            ['comments' => $data['comments'] ?? null]
        );

        return $this->success([
            'approved_count' => count($results),
            'errors' => $errors,
        ], 'Bulk approval completed');
    }
}
