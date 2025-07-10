<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, ?array $additionalMeta = null, int $statusCode = 200): JsonResponse
    {
        $additionalMeta ??= [];

        $pagination = null;
        $links = null;

        if ($data instanceof LengthAwarePaginator) {
            $pagination = [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
                'path' => $data->path(),
            ];

            $links = [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ];
        } elseif ($data instanceof AnonymousResourceCollection) {
            $responseData = $data->response()->getData(true);
            $meta = $responseData['meta'] ?? [];

            if (isset($meta['current_page'])) {
                $pagination = [
                    'current_page' => $meta['current_page'],
                    'per_page' => $meta['per_page'],
                    'from' => $meta['from'],
                    'to' => $meta['to'],
                    'last_page' => $meta['last_page'],
                    'total' => $meta['total'],
                    'path' => url()->current(),
                ];
            }

            $links = $responseData['links'] ?? null;
        }

        if ($pagination) {
            $additionalMeta['page'] = $pagination;
        }

        if ($links) {
            $additionalMeta['links'] = $links;
        }

        $meta = array_merge(
            ['status' => $statusCode],
            $additionalMeta,
            [
                'timestamps' => [
                    'timestamp' => now()->toIso8601String(),
                    'response_time_ms' => self::getResponseTime(),
                ],
            ]
        );

        $responseData = null;

        if ($data instanceof AnonymousResourceCollection) {
            $responseData = $data->response()->getData(true)['data'];
        } elseif ($data instanceof LengthAwarePaginator) {
            $responseData = $data->items();
        } else {
            $responseData = $data;
        }

        return response()->json([
            'data' => $responseData,
            'meta' => $meta,
            'message' => $message,
        ], $statusCode);
    }

    public static function error(string $message, int $statusCode): JsonResponse
    {
        $timestamps = self::prepareTimestamps(null);

        return response()->json([
            'message' => $message,
            'meta' => [
                'status' => $statusCode,
                'timestamps' => $timestamps,
            ],
            'data' => null,
        ], $statusCode);
    }

    protected static function prepareTimestamps(mixed $data): array
    {
        $timestamps = [
            'timestamp' => now()->toIso8601String(),
            'response_time_ms' => self::getResponseTime(),
        ];

        // Only add created_at/updated_at if data is array/object and contains those keys
        if (is_array($data) || is_object($data)) {
            $createdAt = data_get($data, 'created_at');
            $updatedAt = data_get($data, 'updated_at');

            if ($createdAt !== null) {
                $timestamps['created_at'] = self::formatTimestamp($createdAt);
            }

            if ($updatedAt !== null) {
                $timestamps['updated_at'] = self::formatTimestamp($updatedAt);
            }
        }

        return $timestamps;
    }

    protected static function formatTimestamp($timestamp): string
    {
        if ($timestamp instanceof Carbon) {
            return $timestamp->toIso8601String();
        }

        if (is_string($timestamp)) {
            return Carbon::parse($timestamp)->toIso8601String();
        }

        return (string) $timestamp;
    }

    protected static function getResponseTime(): float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }

        return round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
    }
}
