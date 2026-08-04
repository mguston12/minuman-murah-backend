<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Repositories\ProductImageRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageUploadController extends Controller
{
    protected $imageRepository;

    public function __construct(ProductImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    /**
     * Upload product image file (not just URL).
     * 
     * Stores file in storage/app/public/products/images/
     * Access via: http://domain/storage/products/images/filename.jpg
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fk_product_id' => 'required|integer|exists:products,id',
                'image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120', // Max 5MB
                'order_number' => 'nullable|integer|min:0',
                'is_featured' => 'nullable|boolean',
            ]);

            // Upload file ke storage/app/public/products/images/
            // Gunakan uniqid() agar beberapa upload pada detik yang sama tidak saling menimpa
            $file = $request->file('image');
            $filename = 'product_' . $validated['fk_product_id'] . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs(
                'products/images',
                $filename,
                'public'
            );

            // Generate full URL untuk database
            $imageUrl = '/storage/' . $path;

            // Simpan ke database dengan URL
            $imageData = [
                'fk_product_id' => $validated['fk_product_id'],
                'path' => $imageUrl,
                'order_number' => $validated['order_number'] ?? 0,
            ];
            
            // Handle is_featured (convert string '1'/'0' to boolean if needed)
            if (isset($validated['is_featured'])) {
                $imageData['is_featured'] = filter_var($validated['is_featured'], FILTER_VALIDATE_BOOLEAN);
            }
            
            $image = $this->imageRepository->create($imageData);

            return response()->json([
                'success' => true,
                'message' => 'Product image uploaded successfully',
                'data' => $image,
                'file_url' => url($imageUrl),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple product images.
     */
    public function uploadMultipleImages(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fk_product_id' => 'required|integer|exists:products,id',
                'images' => 'required|array|max:7', // Max 7 images
                'images.*' => 'image|mimes:jpeg,png,gif,webp|max:5120',
            ]);

            $uploadedImages = [];
            $orderNumber = 1;

            foreach ($request->file('images') as $file) {
                $filename = 'product_' . $validated['fk_product_id'] . '_' . time() . '_' . $orderNumber . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                
                $path = $file->storeAs(
                    'products/images',
                    $filename,
                    'public'
                );

                $imageUrl = '/storage/' . $path;

                $image = $this->imageRepository->create([
                    'fk_product_id' => $validated['fk_product_id'],
                    'path' => $imageUrl,
                    'order_number' => $orderNumber,
                ]);

                $uploadedImages[] = [
                    'id' => $image->id,
                    'url' => url($imageUrl),
                    'order' => $orderNumber,
                ];

                $orderNumber++;
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedImages) . ' images uploaded successfully',
                'data' => $uploadedImages,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading images',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete product image and file from storage.
     */
    public function deleteImage(int $imageId): JsonResponse
    {
        try {
            $image = ProductImage::find($imageId);

            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found',
                ], 404);
            }

            // Hapus file dari storage
            if ($image->path) {
                // Remove /storage/ prefix untuk mendapatkan path relative
                $filePath = str_replace('/storage/', '', $image->path);
                Storage::disk('public')->delete($filePath);
            }

            // Hapus dari database
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
