<?php

namespace App\Http\Controllers;

use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductGroupController extends Controller
{
    /**
     * Get all product groups
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $query = ProductGroup::with('subGroups')
            ->orderBy('sort');

        if ($perPage === 'all' || $perPage <= 0) {
            $groups = $query->get();
        } else {
            $groups = $query->paginate((int) $perPage);
        }

        return response()->json([
            'success' => true,
            'data' => $groups,
        ]);
    }

    /**
     * Create a new product group
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'  => 'required|string|unique:product_groups,title',
            'key'    => 'required|string|unique:product_groups,key',
            'description' => 'nullable|string',
            'image'  => 'nullable|image|max:2048',
            'sort'   => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        try {
            $group = new ProductGroup(
                collect($validated)->except('image')->toArray()
            );

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('product-groups', 'public');
                $group->image = $path;
            }

            $group->save();

            return response()->json([
                'success' => true,
                'message' => 'Product group created successfully',
                'data'    => $group,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product group',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single product group with sub-groups
     */
    public function show($id)
    {
        $group = ProductGroup::with([
            'subGroups' => fn($q) => $q->orderBy('sort')
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $group,
        ]);
    }

    /**
     * Get a single product group by key with sub-groups
     */
    public function showByKey($key)
    {
        $group = ProductGroup::with([
            'subGroups' => fn($q) => $q->orderBy('sort')
        ])->where('key', $key)->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $group,
        ]);
    }

    /**
     * Update a product group
     */
    public function update(Request $request, $id)
    {
        $group = ProductGroup::findOrFail($id);

        $validated = $request->validate([
            'title'  => 'required|string|unique:product_groups,title,' . $id,
            'key'    => 'required|string|unique:product_groups,key,' . $id,
            'description' => 'nullable|string',
            'image'  => 'nullable|image|max:2048',
            'sort'   => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        try {
            $group->fill(
                collect($validated)->except('image')->toArray()
            );

            if ($request->hasFile('image')) {
                if ($group->image && Storage::disk('public')->exists($group->image)) {
                    Storage::disk('public')->delete($group->image);
                }

                $path = $request->file('image')->store('product-groups', 'public');
                $group->image = $path;
            }

            $group->save();

            return response()->json([
                'success' => true,
                'message' => 'Product group updated successfully',
                'data'    => $group,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product group',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a product group
     */
    public function destroy($id)
    {
        $group = ProductGroup::findOrFail($id);

        if ($group->image && Storage::disk('public')->exists($group->image)) {
            Storage::disk('public')->delete($group->image);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product group deleted successfully',
        ]);
    }
}
