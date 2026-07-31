<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Produces the single JSON envelope used by every API endpoint.
 *
 * The structure is fixed by 09_API_Architecture.md §11–§13; controllers must
 * never assemble JSON by hand.
 */
final class ResponseBuilder
{
    /**
     * @param  mixed  $data
     */
    public static function success(
        mixed $data = null,
        string $message = '',
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => self::normalise($data),
        ];

        if ($meta = self::extractMeta($data)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public static function error(
        string $message,
        array $errors = [],
        int $status = Response::HTTP_BAD_REQUEST,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = ''): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Converts resources and paginators into their array representation.
     */
    private static function normalise(mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }

        if ($data instanceof ResourceCollection) {
            return $data->resolve();
        }

        if ($data instanceof JsonResource) {
            return $data->resolve();
        }

        if ($data instanceof AbstractPaginator) {
            return $data->items();
        }

        return $data;
    }

    /**
     * Builds the documented pagination metadata block.
     *
     * @return array<string, int>|null
     */
    private static function extractMeta(mixed $data): ?array
    {
        $paginator = match (true) {
            $data instanceof AbstractPaginator => $data,
            $data instanceof ResourceCollection && $data->resource instanceof AbstractPaginator => $data->resource,
            default => null,
        };

        if ($paginator === null) {
            return null;
        }

        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
