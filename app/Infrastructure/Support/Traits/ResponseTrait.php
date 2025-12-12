<?php

namespace App\Infrastructure\Support\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    /**
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    public function response(string $message = 'Success', mixed $data = '', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'message'       =>  $message,
            'data'          =>  $data,
        ], $code);
    }

    /**
     * @param array $errors
     * @param int $code
     * @return JsonResponse
     */
    public function jsonApiErrorResponse(array $errors, int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'errors'    =>  $errors,
        ], $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function defaultErrorResponse(string $message, int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => [],
        ], $code);
    }
}
