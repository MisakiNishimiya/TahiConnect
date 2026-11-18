<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Legacy Virtual Try-On Controller
 * 
 * These endpoints are deprecated. Use POST /api/tryon instead.
 * This controller provides stub responses for backward compatibility.
 */
class VirtualTryOnController extends Controller
{
    /**
     * Check API health status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'message' => 'Use POST /api/tryon for virtual try-on',
            'api_version' => 'gemini-2.5-flash',
        ]);
    }

    /**
     * Process virtual try-on (legacy)
     * 
     * DEPRECATED: Use POST /api/tryon instead
     */
    public function process(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'This endpoint is deprecated. Use POST /api/tryon instead.',
            'status' => 'error',
        ], 410); // 410 Gone
    }

    /**
     * Get try-on history (legacy)
     * 
     * DEPRECATED
     */
    public function history(): JsonResponse
    {
        return response()->json([
            'error' => 'This endpoint is deprecated.',
            'status' => 'error',
        ], 410);
    }

    /**
     * Delete try-on (legacy)
     * 
     * DEPRECATED
     */
    public function delete(int $id): JsonResponse
    {
        return response()->json([
            'error' => 'This endpoint is deprecated.',
            'status' => 'error',
        ], 410);
    }

    /**
     * Upload photo (legacy)
     * 
     * DEPRECATED
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'This endpoint is deprecated. Use POST /api/tryon instead.',
            'status' => 'error',
        ], 410);
    }

    /**
     * Upload garment (legacy)
     * 
     * DEPRECATED
     */
    public function uploadGarment(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'This endpoint is deprecated. Use POST /api/tryon instead.',
            'status' => 'error',
        ], 410);
    }

    /**
     * Get recent try-ons (legacy)
     * 
     * DEPRECATED
     */
    public function recent(): JsonResponse
    {
        return response()->json([
            'error' => 'This endpoint is deprecated.',
            'status' => 'error',
        ], 410);
    }

    /**
     * Save try-on (legacy)
     * 
     * DEPRECATED
     */
    public function save(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'This endpoint is deprecated.',
            'status' => 'error',
        ], 410);
    }
}
