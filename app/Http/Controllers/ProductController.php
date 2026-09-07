<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest\StoreProductRequest;
use App\Http\Requests\ProductRequest\UpdateProductRequest;
use App\Http\Resources\ProductResource\ProductResource;
use App\Models\Product;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\BrandRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var BrandRepositoryInterface
     */
    protected BrandRepositoryInterface $brandRepository;

    /**
     * ProductController constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository, BrandRepositoryInterface $brandRepository)
    {
        $this->productRepository = $productRepository;
        $this->brandRepository = $brandRepository;
    }

    /**
     * Display a listing of products (paginated or all).
     * If per_page is not provided, returns all products without pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page');
        $page = $request->get('page', 1);
        $sortBy = $request->get('sort_by');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Get filter parameters
        $categoryIds = $request->get('category_ids', []);
        if (is_string($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }
        $categoryIds = array_filter(array_map('intval', (array) $categoryIds));

        $brandIds = $request->get('brand_ids', []);
        if (is_string($brandIds)) {
            $brandIds = explode(',', $brandIds);
        }
        $brandIds = array_filter(array_map('intval', (array) $brandIds));

        $brandSlugs = $request->get('brand_slugs', []);
        if (is_string($brandSlugs)) {
            $brandSlugs = explode(',', $brandSlugs);
        }
        $brandSlugs = array_filter((array) $brandSlugs);

        if (!empty($brandSlugs)) {
            $slugBrandIds = $this->brandRepository->findIdsBySlugs($brandSlugs);
            $brandIds = array_values(array_unique(array_merge($brandIds, $slugBrandIds)));
        }

        // Get filter parameters - attribute values (e.g. ukuran/size)
        $attributeValueIds = $request->get('attribute_value_ids', []);
        if (is_string($attributeValueIds)) {
            $attributeValueIds = explode(',', $attributeValueIds);
        }
        $attributeValueIds = array_filter(array_map('intval', (array) $attributeValueIds));

        $storeId = $request->get('store_id');
        $storeId = $storeId !== null && $storeId !== '' ? (int) $storeId : null;

        // Get search query
        $search = $request->get('search');
        $search = $search ? trim($search) : null;

        // Get filter parameters
        $isNewArrival = $request->get('is_new_arrival');
        $isNewArrival = $isNewArrival !== null ? filter_var($isNewArrival, FILTER_VALIDATE_BOOLEAN) : null;

        $minRating = $request->get('min_rating');
        $minRating = $minRating !== null ? (float) $minRating : null;

        $minPrice = $request->get('min_price');
        $minPrice = $minPrice !== null ? (float) $minPrice : null;

        $maxPrice = $request->get('max_price');
        $maxPrice = $maxPrice !== null ? (float) $maxPrice : null;

        // If per_page is not provided, return all products without pagination
        if ($perPage === null || $perPage === '') {
            $products = $this->productRepository->getAllWithFilters(
                $sortBy,
                $sortDirection,
                $search,
                $categoryIds,
                $brandIds,
                $storeId,
                $isNewArrival,
                $minRating,
                $minPrice,
                $maxPrice,
                $attributeValueIds
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => ProductResource::collection($products),
                    'total' => $products->count(),
                    'sort' => [
                        'sort_by' => $sortBy ?? 'created_at',
                        'sort_direction' => $sortDirection,
                    ],
                ],
            ], 200);
        }

        // Otherwise, return paginated results
        $products = $this->productRepository->paginate(
            (int) $perPage,
            $sortBy,
            $sortDirection,
            $search,
            $categoryIds,
            $brandIds,
            $storeId,
            $isNewArrival,
            $minRating,
            $minPrice,
            $maxPrice,
            $attributeValueIds,
        );

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductResource::collection($products->items()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'sort' => [
                    'sort_by' => $sortBy ?? 'created_at',
                    'sort_direction' => $sortDirection,
                ],
            ],
        ], 200);
    }

    /**
     * Store a newly created product.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Calculate base_discount_percent if base_strike_price is provided
        if (isset($validated['base_strike_price']) && $validated['base_strike_price'] > 0 && isset($validated['base_price']) && $validated['base_price'] > 0) {
            $validated['base_discount_percent'] = (($validated['base_strike_price'] - $validated['base_price']) / $validated['base_strike_price']) * 100;
        }

        $product = $this->productRepository->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => [
                'product' => new ProductResource($product),
            ],
        ], 201);
    }

    /**
     * Display the specified product by slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->load('hasMany_brand');

        return response()->json([
            'success' => true,
            'data' => [
                'product' => new ProductResource($product),
            ],
        ], 200);
    }

    /**
     * Update the specified product.
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $validated = $request->validated();

        // Calculate base_discount_percent if base_strike_price is provided
        if (isset($validated['base_strike_price']) && $validated['base_strike_price'] > 0) {
            // Get current base_price if not in request
            if (!isset($validated['base_price'])) {
                if ($product->base_price > 0) {
                    $validated['base_discount_percent'] = (($validated['base_strike_price'] - $product->base_price) / $validated['base_strike_price']) * 100;
                }
            } elseif (isset($validated['base_price']) && $validated['base_price'] > 0) {
                $validated['base_discount_percent'] = (($validated['base_strike_price'] - $validated['base_price']) / $validated['base_strike_price']) * 100;
            }
        } else {
            // If base_strike_price is null, set base_discount_percent to null
            $validated['base_discount_percent'] = null;
        }

        $updatedProduct = $this->productRepository->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => [
                'product' => new ProductResource($updatedProduct),
            ],
        ], 200);
    }

    /**
     * Remove the specified product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $this->productRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ], 200);
    }

    /**
     * Get related products by slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function related(Request $request, string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $limit = (int) $request->query('limit', 5);

        $limit = min($limit, 20);

        $related = Product::query()
            ->where('id', '!=', $product->id)
            ->when(
                $product->category_id,
                fn($q) =>
                $q->where('category_id', $product->category_id)
            )
            ->inRandomOrder()
            ->limit($limit)
            ->with(['hasMany_image', 'reviews'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductResource::collection($related),
            ],
        ]);
    }
}
