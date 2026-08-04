<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Repositories\ProductVariantRepository;
use App\Models\ProductVariantStock;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariantController extends Controller
{
    protected $variantRepository;

    public function __construct(ProductVariantRepository $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    /**
     * Display a listing of product variants.
     */
    public function index(): JsonResponse
    {
        try {
            $variants = $this->variantRepository->all();

            return response()->json([
                'success' => true,
                'message' => 'Product variants retrieved successfully',
                'data' => $variants,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product variants',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get variants by product ID.
     */
    public function getByProduct(int $productId): JsonResponse
    {
        try {
            $variants = $this->variantRepository->getByProductId($productId);

            return response()->json([
                'success' => true,
                'message' => 'Product variants retrieved successfully',
                'data' => $variants,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product variants',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active variants by product ID.
     */
    public function getActiveByProduct(int $productId): JsonResponse
    {
        try {
            $variants = $this->variantRepository->getActiveByProductId($productId);

            return response()->json([
                'success' => true,
                'message' => 'Active product variants retrieved successfully',
                'data' => $variants,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving active product variants',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created product variant in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fk_product_id' => 'required|integer|exists:products,id',
                'variant_name' => 'nullable|string|max:250',
                'sku' => [
                    'nullable',
                    'string',
                    Rule::unique('product_variants', 'sku')->whereNull('deleted_at'),
                ],
                'image_path' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'strike_price' => 'nullable|numeric|min:0',
                'is_ignore_stock' => 'nullable|boolean',
                'status' => 'nullable|in:ACTIVE,INACTIVE',
                'attribute_value_ids' => 'nullable|array',
                'attribute_value_ids.*' => 'integer|exists:attribute_values,id',
                'weight' => 'nullable|numeric|min:0',
                'type_weight' => 'nullable|in:GRAM,KG',
            ]);

            // Calculate discount_percent if strike_price is provided
            if (isset($validated['strike_price']) && $validated['strike_price'] > 0 && isset($validated['price']) && $validated['price'] > 0) {
                $validated['discount_percent'] = (($validated['strike_price'] - $validated['price']) / $validated['strike_price']) * 100;
            }

            // Extract attribute_value_ids before creating variant
            $attributeValueIds = $validated['attribute_value_ids'] ?? [];
            unset($validated['attribute_value_ids']);

            $variant = $this->variantRepository->create($validated, $attributeValueIds);

            return response()->json([
                'success' => true,
                'message' => 'Product variant created successfully',
                'data' => $variant->load(['options.attribute', 'options.attributeValue', 'stockRelations.store']),
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
                'message' => 'Error creating product variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product variant.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $variant = $this->variantRepository->findById($id);

            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variant not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product variant retrieved successfully',
                'data' => $variant,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product variant in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fk_product_id' => 'sometimes|required|integer|exists:products,id',
                'variant_name' => 'nullable|string|max:250',
                'sku' => [
                    'nullable',
                    'string',
                    Rule::unique('product_variants', 'sku')
                        ->ignore($id)
                        ->whereNull('deleted_at'),
                ],
                'image_path' => 'nullable|string',
                'price' => 'sometimes|required|numeric|min:0',
                'strike_price' => 'nullable|numeric|min:0',
                'is_ignore_stock' => 'nullable|boolean',
                'status' => 'nullable|in:ACTIVE,INACTIVE',
                'attribute_value_ids' => 'nullable|array',
                'attribute_value_ids.*' => 'integer|exists:attribute_values,id',
                'weight' => 'nullable|numeric|min:0',
                'type_weight' => 'nullable|in:GRAM,KG',
            ]);

            // Calculate discount_percent if strike_price is provided
            if (isset($validated['strike_price']) && $validated['strike_price'] > 0) {
                // Get current price if not in request
                if (!isset($validated['price'])) {
                    $currentVariant = $this->variantRepository->findById($id);
                    if ($currentVariant && $currentVariant->price > 0) {
                        $validated['discount_percent'] = (($validated['strike_price'] - $currentVariant->price) / $validated['strike_price']) * 100;
                    }
                } elseif (isset($validated['price']) && $validated['price'] > 0) {
                    $validated['discount_percent'] = (($validated['strike_price'] - $validated['price']) / $validated['strike_price']) * 100;
                }
            } else {
                // If strike_price is null, set discount_percent to null
                $validated['discount_percent'] = null;
            }

            // Extract attribute_value_ids before updating variant
            $attributeValueIds = $validated['attribute_value_ids'] ?? null;
            unset($validated['attribute_value_ids']);

            $variant = $this->variantRepository->update($id, $validated, $attributeValueIds);

            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variant not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product variant updated successfully',
                'data' => $variant->load(['options.attribute', 'options.attributeValue', 'stockRelations.store']),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product variant from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $variant = $this->variantRepository->findById($id);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variant not found',
                ], 404);
            }
            $activeOrderItemsCount = OrderItem::where('fk_variant_id', $id)
                ->whereHas('order', function ($q) {
                    $q->whereIn('status', [
                        'PENDING',
                        'PACKING',
                        'DELIVERING',
                        'DELIVERED',
                    ]);
                })
                ->count();

            if ($activeOrderItemsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak bisa hapus variant ini karena masih digunakan di {$activeOrderItemsCount} item pesanan yang belum selesai.",
                ], 422);
            }
            $deleted = $this->variantRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete product variant',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product variant deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update stock for a product variant.
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:0',
            ]);

            $updated = $this->variantRepository->updateStock($id, $validated['quantity']);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variant not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product variant stock updated successfully',
                'data' => $this->variantRepository->findById($id),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product variant stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload variant image file.
     * Only uploads the file and returns the path, does NOT save to product_images table.
     */
    public function uploadVariantImage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fk_product_id' => 'required|integer|exists:products,id',
                'image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120', // Max 5MB
            ]);

            // Upload file ke storage/app/public/products/variants/
            // Gunakan uniqid() agar beberapa upload pada detik yang sama tidak saling menimpa
            $file = $request->file('image');
            $filename = 'variant_' . $validated['fk_product_id'] . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs(
                'products/variants',
                $filename,
                'public'
            );

            // Generate full URL untuk database
            $imageUrl = '/storage/' . $path;

            return response()->json([
                'success' => true,
                'message' => 'Variant image uploaded successfully',
                'data' => [
                    'path' => $imageUrl,
                    'url' => url($imageUrl),
                ],
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
                'message' => 'Error uploading variant image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all store stocks for a variant.
     */
    public function getStoreStocks(int $id): JsonResponse
    {
        try {
            $variant = ProductVariant::with('stockRelations.store')->find($id);

            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variant not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Store stocks retrieved successfully',
                'data' => $variant->stockRelations ?? [],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving store stocks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create or update store stock for a variant.
     */
    public function createOrUpdateStoreStock(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'store_id' => 'required|integer|exists:stores,id',
                'qty' => 'required|integer|min:0',
                'reserved_qty' => 'nullable|integer|min:0',
            ]);

            $variant = ProductVariant::find($id);

            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variant not found',
                ], 404);
            }

            // Set default reserved_qty if not provided
            $reservedQty = $validated['reserved_qty'] ?? 0;

            // Update or create stock record
            $storeStock = ProductVariantStock::updateOrCreate(
                [
                    'variant_id' => $id,
                    'store_id' => $validated['store_id'],
                ],
                [
                    'qty' => $validated['qty'],
                    'reserved_qty' => $reservedQty,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Store stock saved successfully',
                'data' => $storeStock->load('store'),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving store stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete store stock for a variant.
     */
    public function deleteStoreStock(int $id, int $storeId): JsonResponse
    {
        try {
            $variant = ProductVariant::find($id);

            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variant not found',
                ], 404);
            }

            $storeStock = ProductVariantStock::where('variant_id', $id)
                ->where('store_id', $storeId)
                ->first();

            if (!$storeStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store stock not found',
                ], 404);
            }

            $storeStock->delete();

            return response()->json([
                'success' => true,
                'message' => 'Store stock deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting store stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
