<?php

namespace App\Interfaces;

use App\Models\Store;

interface StoreRepositoryInterface
{
    /**
     * Create a new store.
     *
     * @param array $data
     * @return Store
     */
    public function create(array $data): Store;

    /**
     * Find a store by ID.
     *
     * @param int $id
     * @return Store|null
     */
    public function findById(int $id): ?Store;

    /**
     * Get all stores.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Get all active stores for public usage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicActiveStores();

    /**
     * Get paginated stores.
     *
     * @param int $perPage
     * @param string|null $sortBy
     * @param string $sortDirection
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, ?string $sortBy = null, string $sortDirection = 'desc');

    /**
     * Update a store.
     *
     * @param int $id
     * @param array $data
     * @return Store|null
     */
    public function update(int $id, array $data): ?Store;

    /**
     * Delete a store.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
