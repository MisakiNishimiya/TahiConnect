<?php

namespace App\Http\Controllers;

use App\Services\VirtualTryOnService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TryOnController extends Controller
{
    /**
     * Generate a virtual try-on using Google Gemini API
     *
     * POST /api/tryon
     * Content-Type: multipart/form-data
     *
     * Parameters:
     *   - person_image (file, required): Image of person (JPG, PNG, WebP, max 5MB)
     *   - cloth_image (file, required): Image of garment (JPG, PNG, WebP, max 5MB)
     *   - cloth_type (string, optional): Category - 'upper', 'lower', 'overall' (default: 'upper')
     *
     * Response:
     *   {
     *     "status": "success",
     *     "result_url": "https://example.com/storage/tryons/tryon_abc123.jpg"
     *   }
     */
    public function generateTryOn(Request $request): JsonResponse
    {
        try {
            // Validate inputs
            $validated = $request->validate([
                'person_image' => 'required|image|mimes:jpeg,png,webp|max:5120',
                'cloth_image' => 'required|image|mimes:jpeg,png,webp|max:5120',
                'cloth_type' => 'nullable|string|in:upper,lower,overall',
            ]);

            $clothType = $validated['cloth_type'] ?? 'upper';

            // Generate virtual try-on
            $service = new VirtualTryOnService();
            $resultUrl = $service->generateTryOn(
                $request->file('person_image'),
                $request->file('cloth_image'),
                $clothType
            );

            return response()->json([
                'status' => 'success',
                'result_url' => $resultUrl,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
