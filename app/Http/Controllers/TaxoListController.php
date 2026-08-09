<?php

namespace App\Http\Controllers;

use App\Repositories\TaxoListRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaxoListController extends Controller
{
    protected $taxoListRepository;

    public function __construct(TaxoListRepository $taxoListRepository)
    {
        $this->taxoListRepository = $taxoListRepository;
    }

    /**
     * Display a listing of taxonomy items.
     */
    public function index(): JsonResponse
    {
        try {
            $taxoLists = $this->taxoListRepository->all();

            return response()->json([
                'success' => true,
                'message' => 'Taxonomy items retrieved successfully',
                'data' => $taxoLists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving taxonomy items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get taxonomy items by type.
     */
    public function getByType(int $type): JsonResponse
    {
        try {
            $taxoLists = $this->taxoListRepository->getByType($type);

            return response()->json([
                'success' => true,
                'message' => 'Taxonomy items retrieved successfully',
                'data' => [
                    'taxo_lists' => $taxoLists,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving taxonomy items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get taxonomy items by parent.
     */ 
    public function getByParent(int $parentId): JsonResponse
    {
        try {
            $taxoLists = $this->taxoListRepository->getByParent($parentId);

            return response()->json([
                'success' => true,
                'message' => 'Taxonomy items retrieved successfully',
                'data' => $taxoLists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving taxonomy items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get root taxonomy items.
     */
    public function getRoots(): JsonResponse
    {
        try {
            $taxoLists = $this->taxoListRepository->getRoots();

            return response()->json([
                'success' => true,
                'message' => 'Root taxonomy items retrieved successfully',
                'data' => $taxoLists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving root taxonomy items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created taxonomy item in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'parent' => 'nullable|integer|exists:taxo_lists,id',
                'taxonomy_ref_key' => 'nullable|integer',
                'taxonomy_name' => 'required|string|max:250',
                'taxonomy_description' => 'nullable|string',
                'taxonomy_slug' => 'nullable|string|max:250',
                'taxonomy_type' => 'required|integer|exists:taxo_types,id',
                'taxonomy_image' => 'nullable|string',
                'taxonomy_sort' => 'nullable|integer',
                'taxonomy_status' => 'nullable|in:ACTIVE,INACTIVE',
            ]);

            $taxoList = $this->taxoListRepository->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Taxonomy item created successfully',
                'data' => $taxoList,
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
                'message' => 'Error creating taxonomy item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified taxonomy item.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $taxoList = $this->taxoListRepository->findById($id);

            if (!$taxoList) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taxonomy item not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Taxonomy item retrieved successfully',
                'data' => $taxoList,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving taxonomy item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified taxonomy item in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'parent' => 'nullable|integer|exists:taxo_lists,id',
                'taxonomy_ref_key' => 'nullable|integer',
                'taxonomy_name' => 'sometimes|required|string|max:250',
                'taxonomy_description' => 'nullable|string',
                'taxonomy_slug' => 'nullable|string|max:250',
                'taxonomy_type' => 'sometimes|required|integer|exists:taxo_types,id',
                'taxonomy_image' => 'nullable|string',
                'taxonomy_sort' => 'nullable|integer',
                'taxonomy_status' => 'nullable|in:ACTIVE,INACTIVE',
            ]);

            $taxoList = $this->taxoListRepository->update($id, $validated);

            if (!$taxoList) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taxonomy item not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Taxonomy item updated successfully',
                'data' => $taxoList,
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
                'message' => 'Error updating taxonomy item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified taxonomy item from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->taxoListRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taxonomy item not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Taxonomy item deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting taxonomy item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
