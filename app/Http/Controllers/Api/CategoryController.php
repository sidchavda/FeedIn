<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index(Request $request)
    {
        $query = Category::with('language');
        
        // Search by name
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filter by language
        if ($request->has('language_id') && !empty($request->language_id)) {
            $query->where('language_id', $request->language_id);
        }
        
        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $categories = $query->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get single category
     */
    public function show($id)
    {
        $category = Category::with('language')->find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Create new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'language_id' => 'required|exists:languages,id',
            'is_active' => 'boolean'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'language_id' => $request->language_id,
            'is_active' => $request->is_active ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'language_id' => 'required|exists:languages,id',
            'is_active' => 'boolean'
        ]);

        $category->update([
            'name' => $request->name,
            'language_id' => $request->language_id,
            'is_active' => $request->is_active ?? $category->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Get categories by language ID
     */
    public function getByLanguage($languageId)
    {
        $categories = Category::where('language_id', $languageId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'language_id', 'is_active']);
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
