<?php

namespace App\Interfaces;

use App\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product;

    /**
     * Find a product by slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Find a product by ID_Products.
     *
     * @param string $idProducts
     * @return Product|null
     */
    public function findByIdProducts(string $idProducts): ?Product;

    /**
     * Get all products.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Get all products with filters (without pagination).
     *
     * @param string|null $sortBy
     * @param string $sortDirection
     * @param string|null $search
     * @param array $categoryIds
     * @param array $brandIds
     * @param bool|null $isNewArrival
     * @param float|null $minRating
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @param array $attributeValueIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
public function getAllWithFilters(?string $sortBy = null, string $sortDirection = 'desc', ?string $search = null, array $categoryIds = [], array $brandIds = [], ?int $storeId = null, ?bool $isNewArrival = null, ?float $minRating = null, ?float $minPrice = null, ?float $maxPrice = null, array $attributeValueIds = []);
    /**
     * Get paginated products.
     *
     * @param int $perPage
     * @param string|null $sortBy
     * @param string $sortDirection
     * @param string|null $search
     * @param array $categoryIds
     * @param array $brandIds
     * @param bool|null $isNewArrival
     * @param float|null $minRating
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @param array $attributeValueIds
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
public function paginate(int $perPage = 15, ?string $sortBy = null, string $sortDirection = 'desc', ?string $search = null, array $categoryIds = [], array $brandIds = [], ?int $storeId = null, ?bool $isNewArrival = null, ?float $minRating = null, ?float $minPrice = null, ?float $maxPrice = null, array $attributeValueIds = []);
    /**
     * Update a product.
     *
     * @param int $id
     * @param array $data
     * @return Product|null
     */
    public function update(int $id, array $data): ?Product;

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
