<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Common JSON response format for all API controllers:
 * { "status": bool, "message": string, "data": mixed }
 */
trait ApiResponse
{
    protected function respond(bool $status, string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
