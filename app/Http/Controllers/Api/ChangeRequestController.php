<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @authenticated
     */
    public function index(Request $request): JsonResponse
    {
        // Only moderators and admins can view change requests
        if (!auth()->user()->canReview()) {
            return $this->error('Unauthorized to view change requests', 403);
        }

        $changeRequests = ChangeRequest::with(['user', 'reviewer'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->model_type, fn($q, $type) => $q->where('model_type', $type))
            ->when($request->action, fn($q, $action) => $q->where('action', $action))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->ok($changeRequests);
    }

    /**
     * Get a specific change request with detailed diff
     */
    public function show(ChangeRequest $changeRequest): JsonResponse
    {
        if (!auth()->user()->canReview()) {
            return $this->error('Unauthorized to view change requests', 403);
        }

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
            $changeRequest,
            null,
            compact('diff')
        );
    }

    /**
     * Approve a change request
     */
    public function approve(Request $request, ChangeRequest $changeRequest): JsonResponse
    {
        if (!auth()->user()->canReview()) {
            return $this->error('Unauthorized to approve change requests', 403);
        }

        $request->validate([
            'comments' => 'nullable|string|max:1000'
        ]);

        try {
            $result = $this->service->approve($changeRequest, $request->only('comments'));

            return $this->success([
                'change_request' => $changeRequest->fresh(['user', 'reviewer']),
                'created_resource' => $result
            ], 'Change request approved successfully');

        } catch (Exception $e) {
            return $this->error('Failed to approve change request: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Reject a change request
     */
    public function reject(Request $request, ChangeRequest $changeRequest): JsonResponse
    {
        if (!auth()->user()->canReview()) {
            return $this->error('Unauthorized to reject change requests', 403);
        }

        $request->validate([
            'comments' => 'required|string|max:1000'
        ]);

        $this->service->reject($changeRequest, $request->only('comments'));

        return $this->success(
            $changeRequest->fresh(['user', 'reviewer']),
            'Change request rejected'
        );
    }

    /**
     * Bulk approve multiple change requests
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        if (!auth()->user()->canReview()) {
            return $this->error('Unauthorized to approve change requests', 403);
        }

        $request->validate([
            'change_request_ids' => 'required|array',
            'change_request_ids.*' => 'exists:change_requests,id',
            'comments' => 'nullable|string|max:1000'
        ]);

        $results = [];
        $errors = [];

        foreach ($request->change_request_ids as $id) {
            try {
                $changeRequest = ChangeRequest::findOrFail($id);
                if ($changeRequest->status === 'pending') {
                    $result = $this->service->approve($changeRequest, $request->only('comments'));
                    $results[] = $result;
                }
            } catch (Exception $e) {
                $errors[] = "ID {$id}: " . $e->getMessage();
            }
        }

        return $this->success([
            'approved_count' => count($results),
            'errors' => $errors
        ], 'Bulk approval completed');
    }
}
