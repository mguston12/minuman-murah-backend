<?php

namespace App\Repositories;

use App\Models\Store;
use App\Interfaces\StoreRepositoryInterface;

class StoreRepository implements StoreRepositoryInterface
{
    /**
     * Create a new store.
     *
     * @param array $data
     * @return Store
     */
    public function create(array $data): Store
    {
        return Store::create($data);
    }

    /**
     * Find a store by ID.
     *
     * @param int $id
     * @return Store|null
     */
    public function findById(int $id): ?Store
    {
        return Store::with('products')->find($id);
    }

    /**
     * Get all stores.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Store::with('products')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all active stores for public usage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicActiveStores()
    {
        return Store::with('products')
            ->where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated stores.
     *
     * @param int $perPage
     * @param string|null $sortBy
     * @param string $sortDirection
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, ?string $sortBy = null, string $sortDirection = 'desc')
    {
        $query = Store::with('products');

        // Validate sortBy to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'code', 'email', 'status', 'created_at'];
        $sortBy = $sortBy && in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Update a store.
     *
     * @param int $id
     * @param array $data
     * @return Store|null
     */
    public function update(int $id, array $data): ?Store
    {
        $store = $this->findById($id);

        if (!$store) {
            return null;
        }

        $store->update($data);

        return $store->fresh(['products']);
    }

    /**
     * Delete a store.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $store = $this->findById($id);

        if (!$store) {
            return false;
        }

        return $store->delete();
    }
}
