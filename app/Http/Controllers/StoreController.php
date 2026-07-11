<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest\StoreStoreRequest;
use App\Http\Requests\StoreRequest\UpdateStoreRequest;
use App\Http\Resources\StoreResource\StoreResource;
use App\Interfaces\StoreRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * @var StoreRepositoryInterface
     */
    protected StoreRepositoryInterface $storeRepository;

    /**
     * StoreController constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Display a listing of stores (paginated).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $sortBy = $request->get('sort_by');
        $sortDirection = $request->get('sort_direction', 'desc');

        $stores = $this->storeRepository->paginate((int) $perPage, $sortBy, $sortDirection);

        return response()->json([
            'success' => true,
            'data' => [
                'stores' => StoreResource::collection($stores->items()),
                'pagination' => [
                    'current_page' => $stores->currentPage(),
                    'per_page' => $stores->perPage(),
                    'total' => $stores->total(),
                    'last_page' => $stores->lastPage(),
                    'from' => $stores->firstItem(),
                    'to' => $stores->lastItem(),
                ],
                'sort' => [
                    'sort_by' => $sortBy ?? 'created_at',
                    'sort_direction' => $sortDirection,
                ],
            ],
        ], 200);
    }

    /**
     * Get all stores without pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse
    {
        $stores = $this->storeRepository->all();

        return response()->json([
            'success' => true,
            'data' => [
                'stores' => StoreResource::collection($stores),
            ],
        ], 200);
    }

    /**
     * Store a newly created store.
     *
     * @param StoreStoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $productIds = $data['product_ids'] ?? [];
        unset($data['product_ids']);

        $store = $this->storeRepository->create($data);

        // Attach products if provided
        if (!empty($productIds)) {
            $store->products()->attach($productIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store created successfully',
            'data' => [
                'store' => new StoreResource($store->fresh('products')),
            ],
        ], 201);
    }

    /**
     * Display the specified store.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $store = $this->storeRepository->findById($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'store' => new StoreResource($store),
            ],
        ], 200);
    }

    /**
     * Update the specified store.
     *
     * @param UpdateStoreRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateStoreRequest $request, int $id): JsonResponse
    {
        $store = $this->storeRepository->findById($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        $data = $request->validated();
        $productIds = $data['product_ids'] ?? null;
        unset($data['product_ids']);

        $updatedStore = $this->storeRepository->update($id, $data);

        // Sync products if provided
        if ($productIds !== null) {
            $updatedStore->products()->sync($productIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store updated successfully',
            'data' => [
                'store' => new StoreResource($updatedStore->fresh('products')),
            ],
        ], 200);
    }

    /**
     * Remove the specified store.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $store = $this->storeRepository->findById($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        $this->storeRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Store deleted successfully',
        ], 200);
    }
}
