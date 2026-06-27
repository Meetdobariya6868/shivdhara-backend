<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Return a standardised success JSON response.
     *
     * @param  mixed  $data  Resource, Resource Collection, or plain array.
     */
    protected function success(
        mixed $data,
        string $message,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Return a standardised success response with no data payload
     * (e.g. logout, delete).
     */
    protected function message(
        string $message,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $status);
    }

    /**
     * Return a standardised paginated response: the resource collection in
     * `data` plus a `meta` block with pagination details. Keeps every list
     * endpoint's contract identical for the frontend.
     *
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function paginated(
        LengthAwarePaginator $paginator,
        AnonymousResourceCollection $collection,
        string $message,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $collection,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }
}
