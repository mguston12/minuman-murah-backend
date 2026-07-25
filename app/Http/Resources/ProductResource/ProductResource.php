<?php

namespace App\Http\Resources\ProductResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $basePrice = $this->base_price ?? 0;
        $baseStrikePrice = $this->base_strike_price ?? null;

        $baseDiscountPercent = null;
        if ($baseStrikePrice !== null && $basePrice > 0 && $baseStrikePrice > 0) {
            $baseDiscountPercent = round((($baseStrikePrice - $basePrice) / $baseStrikePrice) * 100, 2);
        }

        $finalPrice = $basePrice;

        $variants = $this->relationLoaded('hasMany_variant') ? $this->hasMany_variant : collect();
        $totalStock = $variants->sum(function ($variant) {
            return $variant->stock ?? 0;
        }) ?? 0;
        $isAvailable = $totalStock > 0;

        $reviews = $this->relationLoaded('reviews') ? $this->reviews : collect();

        $approvedReviews = $reviews->where('is_approved', true);
        $reviewCount = $approvedReviews->count();
        $averageRating = $reviewCount > 0
            ? round($approvedReviews->avg('rating') * 10) / 10
            : 0;

        $isNewArrival = false;
        if ($this->created_at) {
            $createdAt = $this->created_at;
            $thirtyDaysAgo = now()->subDays(30);
            $isNewArrival = $createdAt->greaterThanOrEqualTo($thirtyDaysAgo);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_freeshiping' => $this->is_freeshiping,
            'product_information' => $this->product_information,
            'meta_keywords' => $this->meta_keywords,
            'meta_description' => $this->meta_description,
            'meta_title' => $this->meta_title,
            'material' => $this->material,
            'finishing' => $this->finishing,
            'color' => $this->color,
            'weight' => $this->weight,
            'type_weight' => $this->type_weight,
            'size_long' => $this->size_long,
            'size_tall' => $this->size_tall,
            'size_wide' => $this->size_wide,
            'type_size' => $this->type_size,
            'package_long' => $this->package_long,
            'package_wide' => $this->package_wide,
            'package_tall' => $this->package_tall,
            'sku' => $this->sku,
            'base_price' => $basePrice,
            'base_strike_price' => $baseStrikePrice,
            'base_discount_percent' => $baseDiscountPercent,
            'sort' => $this->sort,
            'tags' => $this->tags,
            'product_protection_percent' => $this->product_protection_percent,
            'status' => $this->status,
            'price' => $basePrice,
            'final_price' => $finalPrice,
            'discount_percent' => $baseDiscountPercent,
            'total_stock' => $totalStock,
            'is_available' => $isAvailable,
            'average_rating' => $averageRating,
            'review_count' => $reviewCount,
            'is_new_arrival' => $isNewArrival,
            'categories' => $this->whenLoaded('hasMany_category', function () {
                if (!$this->hasMany_category) {
                    return [];
                }
                return $this->hasMany_category->map(function ($cat) {
                    $category = $cat->relationLoaded('fk_category') ? $cat->fk_category : null;
                    return [
                        'id' => $cat->id ?? null,
                        'product_id' => $cat->fk_product_id ?? null,
                        'category_id' => $cat->fk_category_id ?? null,
                        'category_name' => $category->taxonomy_name ?? null,
                        'category_slug' => $category->taxonomy_slug ?? null,
                    ];
                });
            }),

            'brands' => $this->whenLoaded('hasMany_brand', function () {
                return $this->hasMany_brand->map(function ($brandProduct) {
                    return [
                        'id' => $brandProduct->fk_brand->id ?? null,
                        'name' => $brandProduct->fk_brand->name ?? null,
                    ];
                });
            }),

            'variants' => $this->whenLoaded('hasMany_variant', function () {
                if (!$this->hasMany_variant) {
                    return [];
                }
                return $this->hasMany_variant->map(function ($var) {
                    $variantPrice = $var->price ?? 0;
                    $variantStrikePrice = $var->strike_price ?? null;

                    $variantDiscountPercent = null;
                    if ($variantStrikePrice !== null && $variantPrice > 0 && $variantStrikePrice > 0) {
                        $variantDiscountPercent = round((($variantStrikePrice - $variantPrice) / $variantStrikePrice) * 100, 2);
                    }

                    $variantFinalPrice = $variantPrice;

                    $variantStock = $var->stock ?? 0;

                    $variantOptions = [];
                    if ($var->relationLoaded('options') && $var->options) {
                        $variantOptions = $var->options->map(function ($option) {
                            return [
                                'attribute_id' => $option->attribute_id ?? null,
                                'attribute_name' => ($option->relationLoaded('attribute') && $option->attribute)
                                    ? $option->attribute->name
                                    : null,
                                'attribute_value_id' => $option->attribute_value_id ?? null,
                                'attribute_value' => ($option->relationLoaded('attributeValue') && $option->attributeValue)
                                    ? $option->attributeValue->value
                                    : null,
                            ];
                        })->toArray();
                    }

                    $stockRelations = [];
                    if ($var->relationLoaded('stockRelations') && $var->stockRelations) {
                        $stockRelations = $var->stockRelations->map(function ($stock) {
                            return [
                                'id' => $stock->id ?? null,
                                'variant_id' => $stock->variant_id ?? null,
                                'store_id' => $stock->store_id ?? null,
                                'qty' => $stock->qty ?? 0,
                                'reserved_qty' => $stock->reserved_qty ?? 0,
                                'store' => ($stock->relationLoaded('store') && $stock->store) ? [
                                    'id' => $stock->store->id ?? null,
                                    'name' => $stock->store->name ?? null,
                                    'code' => $stock->store->code ?? null,
                                    'address' => $stock->store->address ?? null,
                                    'city' => $stock->store->city ?? null,
                                    'city_id' => $stock->store->city_id ?? null,
                                    'province' => $stock->store->province ?? null,
                                    'postal_code' => $stock->store->postal_code ?? null,
                                ] : null,
                            ];
                        })->toArray();
                    }

                    return [
                        'id' => $var->id ?? null,
                        'variant_name' => $var->variant_name ?? null,
                        'sku' => $var->sku ?? null,
                        'weight' => $var->weight,
                        'type_weight' => $var->type_weight ?? 'GRAM',
                        'weight_display' => $var->weight_display,
                        'image_path' => ($var->image_path ?? null) ? (str_starts_with($var->image_path, 'http') ? $var->image_path : asset($var->image_path)) : null,
                        'price' => $variantPrice,
                        'strike_price' => $variantStrikePrice,
                        'discount_percent' => $variantDiscountPercent,
                        'final_price' => $variantFinalPrice,
                        'stock' => $variantStock,
                        'status' => $var->status ?? 'INACTIVE',
                        'is_available' => $variantStock > 0,
                        'options' => $variantOptions,
                        'stock_relations' => $stockRelations,
                    ];
                });
            }),

            'images' => $this->whenLoaded('hasMany_image', function () {
                if (!$this->hasMany_image) {
                    return [];
                }
                return $this->hasMany_image->map(fn($img) => [
                    'id' => $img->id ?? null,
                    'path' => ($img->path ?? null) ? asset($img->path) : null,
                    'order_number' => $img->order_number ?? 0,
                    'is_featured' => $img->is_featured ?? false,
                ]);
            }),

            'featured_image' => $this->whenLoaded('hasMany_image', function () {
                if (!$this->hasMany_image || $this->hasMany_image->isEmpty()) {
                    return null;
                }
                $featured = $this->hasMany_image->where('is_featured', true)->first();
                if ($featured) {
                    return [
                        'id' => $featured->id ?? null,
                        'path' => ($featured->path ?? null) ? asset($featured->path) : null,
                    ];
                }
                $first = $this->hasMany_image->sortBy('order_number')->first();
                return $first ? [
                    'id' => $first->id ?? null,
                    'path' => ($first->path ?? null) ? asset($first->path) : null,
                ] : null;
            }),

            'reviews' => $this->whenLoaded('reviews', function () {
                if (!$this->reviews) {
                    return [];
                }

                return $this->reviews->map(function ($review) {
                    $user = $review->relationLoaded('user') ? $review->user : null;

                    return [
                        'id' => $review->id ?? null,
                        'user' => $user ?? null,
                        'rating' => $review->rating ?? 0,
                        'comment' => $review->comment ?? null,
                        'review_date' => $review->review_date?->format('Y-m-d'),
                        'is_approved' => $review->is_approved ?? false,
                    ];
                });
            }),

            'stores' => $this->whenLoaded('stores', function () {
                if (!$this->stores) {
                    return [];
                }
                return $this->stores->map(fn($store) => [
                    'id' => $store->id ?? null,
                    'name' => $store->name ?? null,
                    'city_id' => $store->city_id ?? null,
                    'city' => $store->city ?? null,
                    'province' => $store->province ?? null,
                    'stock' => $store->pivot->stock ?? 0,
                    'shipping_cost' => $store->pivot->shipping_cost ?? 0,
                    'estimated_days_min' => $store->pivot->estimated_days_min ?? null,
                    'estimated_days_max' => $store->pivot->estimated_days_max ?? null,
                    'is_available' => $store->pivot->is_available ?? false,
                ]);
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
