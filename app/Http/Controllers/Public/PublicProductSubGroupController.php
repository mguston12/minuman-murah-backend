<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource\ProductResource;
use App\Models\ProductSubGroup;
use Illuminate\Http\Request;

class PublicProductSubGroupController extends Controller
{
    /**
     * Get active sub-groups for a product group (public)
     */
    public function index(Request $request, $groupId)
    {
        $subGroups = ProductSubGroup::where('product_group_id', $groupId)
            ->status('ACTIVE') // status sub-group, tetap ACTIVE
            ->with([
                'products' => function ($query) use ($request) {

                    // Hanya produk PUBLISH yang tampil ke publik
                    $query->where('products.status', 'PUBLISH');

                    if ($request->has('limit') && is_numeric($request->limit)) {
                        $query->take((int) $request->limit);
                    }

                    $query
                        ->with([
                            'hasMany_image',
                            'hasMany_variant',
                            'hasMany_category',
                            'reviews',
                        ])
                        ->orderByPivot('sort');
                }
            ])
            ->orderBy('sort')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subGroups->map(function ($subGroup) {
                return [
                    'id' => $subGroup->id,
                    'product_group_id' => $subGroup->product_group_id,
                    'title' => $subGroup->title,
                    'sort' => $subGroup->sort,
                    'products' => ProductResource::collection($subGroup->products),
                ];
            }),
        ]);
    }

    /**
     * Get single active sub-group (public)
     */
    public function show(Request $request, $groupId, $subGroupId)
    {
        $subGroup = ProductSubGroup::where('product_group_id', $groupId)
            ->status('ACTIVE') // status sub-group, tetap ACTIVE
            ->with([
                'products' => function ($query) use ($request) {

                    // Hanya produk PUBLISH yang tampil ke publik
                    $query->where('products.status', 'PUBLISH');

                    if ($request->has('limit') && is_numeric($request->limit)) {
                        $query->take((int) $request->limit);
                    }

                    $query
                        ->with([
                            'hasMany_image',
                            'hasMany_variant',
                            'hasMany_category',
                            'reviews',
                        ])
                        ->orderByPivot('sort');
                }
            ])
            ->findOrFail($subGroupId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $subGroup->id,
                'product_group_id' => $subGroup->product_group_id,
                'title' => $subGroup->title,
                'sort' => $subGroup->sort,
                'products' => ProductResource::collection($subGroup->products),
            ],
        ]);
    }
}