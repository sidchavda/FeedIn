<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Get all languages
     */
    public function index(Request $request)
    {
        $query = Language::query();
        
        // Search by name or code
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $languages = $query->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $languages
        ]);
    }

    /**
     * Get single language
     */
    public function show($id)
    {
        $language = Language::find($id);
        
        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $language
        ]);
    }

    /**
     * Create new language
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages,code',
            'is_active' => 'boolean'
        ]);

        $language = Language::create([
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => $request->is_active ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language created successfully',
            'data' => $language
        ], 201);
    }

    /**
     * Update language
     */
    public function update(Request $request, $id)
    {
        $language = Language::find($id);
        
        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages,code,' . $id,
            'is_active' => 'boolean'
        ]);

        $language->update([
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => $request->is_active ?? $language->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => $language
        ]);
    }

    /**
     * Delete language
     */
    public function destroy($id)
    {
        $language = Language::find($id);
        
        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found'
            ], 404);
        }

        $language->delete();

        return response()->json([
            'success' => true,
            'message' => 'Language deleted successfully'
        ]);
    }
}
