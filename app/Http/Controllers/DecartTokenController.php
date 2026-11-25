<?php

namespace App\Http\Controllers;

use App\Services\DecartVirtualTryOnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DecartTokenController extends Controller
{
    /**
     * Get a client token for Decart realtime connection
     * POST /api/decart/token
     *
     * This endpoint is called from the browser every time a try-on session starts.
     * It returns a short-lived token that the Decart SDK uses to connect securely.
     */
    public function getToken(Request $request): JsonResponse
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User must be authenticated',
                ], 401);
            }
            
            $service = new DecartVirtualTryOnService();
            $token = $service->createClientToken();

            return response()->json($token);
        } catch (\Exception $e) {
            \Log::error('Failed to generate Decart token', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Provide user-friendly error message
            $userMessage = $e->getMessage();
            if (str_contains($userMessage, 'Could not resolve host')) {
                $userMessage = 'Unable to connect to Decart AI service. This may be due to network restrictions on the server. Please contact support or try again later.';
            }

            return response()->json([
                'error' => 'Failed to generate token',
                'message' => $userMessage,
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check Decart API health
     * GET /api/decart/health
     */
    public function health(): JsonResponse
    {
        try {
            $service = new DecartVirtualTryOnService();
            $health = $service->checkApiHealth();

            if ($health['online']) {
                return response()->json([
                    'status' => 'healthy',
                    'provider' => 'decart',
                    'model' => config('services.decart.model'),
                ]);
            }

            return response()->json([
                'status' => 'error',
                'error' => $health['error'],
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save a try-on result image
     * POST /api/decart/save-tryon
     */
    public function saveTryOn(Request $request): JsonResponse
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User must be authenticated',
                ], 401);
            }

            $request->validate([
                'image' => 'required|image|max:10240|mimes:jpeg,png,jpg,gif',
            ]);

            $path = $request->file('image')->store('virtual-tryon-results', 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'message' => 'Try-on result saved successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save try-on result', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'error' => 'Failed to save try-on result',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
