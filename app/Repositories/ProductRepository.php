<?php

namespace App\Repositories;

use App\Models\Product;
use App\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product
    {
        return Product::with([
            'hasMany_category.fk_category',
            'hasMany_variant.options.attribute',
            'hasMany_variant.options.attributeValue',
            'hasMany_variant.stockRelations.store',
            'hasMany_image',
            'reviews' => function ($query) {
                $query->where('is_approved', true)
                    ->with('user')
                    ->orderBy('review_date', 'desc');
            },
            'stores'
        ])->find($id);
    }

    /**
     * Find a product by slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::with([
            'hasMany_category.fk_category',
            'hasMany_variant.options.attribute',
            'hasMany_variant.options.attributeValue',
            'hasMany_variant.stockRelations.store',
            'hasMany_image',
            'reviews' => function ($query) {
                $query->where('is_approved', true)
                    ->with('user')
                    ->orderBy('review_date', 'desc');
            },
            'stores'
        ])->where('slug', $slug)->first();
    }

    /**
     * Find a product by ID_Products.
     *
     * @param string $idProducts
     * @return Product|null
     */
    public function findByIdProducts(string $idProducts): ?Product
    {
        return Product::with(['hasMany_category', 'hasMany_variant', 'hasMany_image'])->where('id_products', $idProducts)->first();
    }

    /**
     * Get all products.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Product::with([
            'hasMany_category.fk_category', // Eager load category details
            'hasMany_variant.stockRelations.store', // Load stock per store for variants
            'hasMany_image' => function ($query) {
                $query->orderBy('order_number', 'asc')->limit(1); // Primary image only
            }
        ])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all products with filters (without pagination).
     *
     * @param string|null $sortBy
     * @param string $sortDirection
     * @param string|null $search
     * @param array $categoryIds
     * @param array $brandIds
     * @param int|null $storeId
     * @param bool|null $isNewArrival
     * @param float|null $minRating
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @param array $attributeValueIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithFilters(?string $sortBy = null, string $sortDirection = 'desc', ?string $search = null, array $categoryIds = [], array $brandIds = [], ?int $storeId = null, ?bool $isNewArrival = null, ?float $minRating = null, ?float $minPrice = null, ?float $maxPrice = null, array $attributeValueIds = [])
    {
        $query = $this->buildFilteredQuery($sortBy, $sortDirection, $search, $categoryIds, $brandIds, $storeId, $isNewArrival, $minRating, $minPrice, $maxPrice, $attributeValueIds);
        return $query->get();
    }

    /**
     * Get paginated products.
     *
     * @param int $perPage
     * @param string|null $sortBy
     * @param string $sortDirection
     * @param string|null $search
     * @param array $categoryIds
     * @param array $brandIds
     * @param int|null $storeId
     * @param bool|null $isNewArrival
     * @param float|null $minRating
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @param array $attributeValueIds
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, ?string $sortBy = null, string $sortDirection = 'desc', ?string $search = null, array $categoryIds = [], array $brandIds = [], ?int $storeId = null, ?bool $isNewArrival = null, ?float $minRating = null, ?float $minPrice = null, ?float $maxPrice = null, array $attributeValueIds = [])
    {
        $query = $this->buildFilteredQuery($sortBy, $sortDirection, $search, $categoryIds, $brandIds, $storeId, $isNewArrival, $minRating, $minPrice, $maxPrice, $attributeValueIds);
        return $query->paginate($perPage);
    }

    /**
     * Build filtered query (shared logic for paginate and getAllWithFilters).
     *
     * @param string|null $sortBy
     * @param string $sortDirection
     * @param string|null $search
     * @param array $categoryIds
     * @param array $brandIds
     * @param int|null $storeId
     * @param bool|null $isNewArrival
     * @param float|null $minRating
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @param array $attributeValueIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildFilteredQuery(?string $sortBy = null, string $sortDirection = 'desc', ?string $search = null, array $categoryIds = [], array $brandIds = [], ?int $storeId = null, ?bool $isNewArrival = null, ?float $minRating = null, ?float $minPrice = null, ?float $maxPrice = null, array $attributeValueIds = [])
    {
        $query = Product::with([
            'hasMany_category.fk_category',
            'hasMany_variant' => function ($query) {
        $query->where('status', 'ACTIVE')
              ->orderBy('price', 'asc')
              ->with([
                  'stockRelations.store',
                  'options.attribute',
                  'options.attributeValue'
              ]);
    },
            'hasMany_variant.stockRelations.store',
            'hasMany_image' => function ($query) {
                $query->orderBy('is_featured', 'desc')->orderBy('order_number', 'asc');
            },
            'reviews' => function ($query) {
                $query->where('is_approved', true)
                    ->with('user')
                    ->orderBy('review_date', 'desc');
            },
        ]);

        // Filter by categories if provided
        if (!empty($categoryIds)) {
            $query->whereHas('hasMany_category', function ($q) use ($categoryIds) {
                $q->whereIn('fk_category_id', $categoryIds);
            });
        }

        if (!empty($brandIds)) {
            $query->whereHas('hasMany_brand', function ($q) use ($brandIds) {
                $q->whereIn('fk_brand_id', $brandIds);
            });
        }

        // Filter by attribute value (e.g. ukuran/size) via variant options
        if (!empty($attributeValueIds)) {
            $query->whereHas('hasMany_variant', function ($variantQuery) use ($attributeValueIds) {
                $variantQuery->where('status', 'ACTIVE')
                    ->whereHas('options', function ($optQuery) use ($attributeValueIds) {
                        $optQuery->whereIn('attribute_value_id', $attributeValueIds);
                    });
            });
        }

        if ($storeId !== null) {
            $query->where(function ($storeQuery) use ($storeId) {
                $storeQuery->whereHas('stores', function ($q) use ($storeId) {
                    $q->where('stores.id', $storeId);
                })->orWhereHas('hasMany_variant.stockRelations', function ($q) use ($storeId) {
                    $q->where('store_id', $storeId);
                });
            });
        }
        
        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('product_information', 'like', "%{$search}%");
            });
        }

        if ($isNewArrival === true) {
            $thirtyDaysAgo = now()->subDays(30);
            $query->where('created_at', '>=', $thirtyDaysAgo);
        }

        if ($minRating !== null && $minRating > 0) {
            $query->whereHas('reviews', function ($q) use ($minRating) {
                $q->where('is_approved', true);
            })->whereRaw('(
                SELECT AVG(rating) 
                FROM product_reviews 
                WHERE product_reviews.fk_product_id = products.id 
                AND product_reviews.is_approved = 1
            ) >= ?', [$minRating]);
        }

        // Filter by price range
        if ($minPrice !== null || $maxPrice !== null) {
            $query->where(function ($q) use ($minPrice, $maxPrice) {
                if ($minPrice !== null && $maxPrice !== null) {
                    $q->where(function ($subQ) use ($minPrice, $maxPrice) {
                        $subQ->whereNotNull('base_price')
                            ->whereBetween('base_price', [$minPrice, $maxPrice]);
                    })
                        ->orWhereHas('hasMany_variant', function ($variantQuery) use ($minPrice, $maxPrice) {
                            $variantQuery->where('status', 'ACTIVE')
                                ->whereBetween('price', [$minPrice, $maxPrice]);
                        });
                } elseif ($minPrice !== null) {
                    $q->where(function ($subQ) use ($minPrice) {
                        $subQ->whereNotNull('base_price')
                            ->where('base_price', '>=', $minPrice);
                    })
                        ->orWhereHas('hasMany_variant', function ($variantQuery) use ($minPrice) {
                            $variantQuery->where('status', 'ACTIVE')
                                ->where('price', '>=', $minPrice);
                        });
                } elseif ($maxPrice !== null) {
                    $q->where(function ($subQ) use ($maxPrice) {
                        $subQ->whereNotNull('base_price')
                            ->where('base_price', '<=', $maxPrice);
                    })
                        ->orWhereHas('hasMany_variant', function ($variantQuery) use ($maxPrice) {
                            $variantQuery->where('status', 'ACTIVE')
                                ->where('price', '<=', $maxPrice);
                        });
                }
            });
        }

        // Validate sortBy to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'slug', 'sort', 'status', 'created_at', 'updated_at'];
        $sortBy = $sortBy && in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        $query->orderBy($sortBy, $sortDirection);

        return $query;
    }

    /**
     * Update a product.
     *
     * @param int $id
     * @param array $data
     * @return Product|null
     */
    public function update(int $id, array $data): ?Product
    {
        $product = $this->findById($id);

        if (!$product) {
            return null;
        }

        $product->update($data);

        return $product->fresh([
            'hasMany_category',
            'hasMany_variant.stockRelations.store',
            'hasMany_image',
            'reviews.user',
            'stores'
        ]);
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $product = $this->findById($id);

        if (!$product) {
            return false;
        }

        return $product->delete();
    }
}