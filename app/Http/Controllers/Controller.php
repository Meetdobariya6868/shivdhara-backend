<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    /**
     * Return a standardised success JSON response.
     *
     * @param  mixed  $data   Resource, Resource Collection, or plain array.
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
}
