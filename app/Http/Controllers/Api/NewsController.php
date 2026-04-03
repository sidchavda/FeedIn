<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Get news listing with filters (Custom Pagination)
     */
    public function index(Request $request)
    {
        $query = News::select('id', 'title', 'link', 'author', 'description', 'image', 'created_at', 'category_id')
            ->with(['category:id,name'])
            ->where('status', 1);
        
        // Search by title, author, or description
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by language
        if ($request->has('language_id') && !empty($request->language_id)) {
            $query->where('language_id', $request->language_id);
        }
        
        // Filter by single or multiple category IDs (comma-separated)
        if ($request->has('category_id') && !empty($request->category_id)) {
            $categoryIds = $request->category_id;
            
            // Handle comma-separated string (e.g., category_id=1,2,3)
            if (str_contains($categoryIds, ',')) {
                $categoryIdsArray = explode(',', $categoryIds);
                $query->whereIn('category_id', $categoryIdsArray);
            } 
            // Handle single category ID
            else {
                $query->where('category_id', $categoryIds);
            }
        }
        
        // Filter by country
        if ($request->has('country_id') && !empty($request->country_id)) {
            $query->where('country_id', $request->country_id);
        }
        
        // Filter by state
        if ($request->has('state_id') && !empty($request->state_id)) {
            $query->where('state_id', $request->state_id);
        }
        
        // Order by latest
        $query->orderBy('created_at', 'desc');
        
        // Custom Pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalItems = $query->count();
        
        // Get items for current page
        $items = $query->skip($offset)->take($perPage)->get();
        
        // Transform items to add full image URL and category name as single key
        $items->transform(function ($item) {
            if ($item->image) {
                $item->full_image_url = asset($item->image);
            } else {
                $item->full_image_url = null;
            }
            // Add category as single key with just the name
            $item->category = $item->category ? $item->category->name : null;
            unset($item->category_id);
            return $item;
        });
        
        // Calculate pagination values
        $totalPages = (int) ceil($totalItems / $perPage);
        $hasMore = $page < $totalPages;
        
        $pagination = [
            'current_page' => (int) $page,
            'per_page' => (int) $perPage,
            'total_items' => (int) $totalItems,
            'total_pages' => $totalPages,
            'has_more' => $hasMore,
            'data' => $items
        ];
        
        return response()->json([
            'success' => true,
            'pagination' => $pagination
        ]);
    }

    /**
     * Get single news detail
     */
    public function show($id)
    {
        $news = News::with(['country', 'state', 'language', 'category'])
            ->where('status', 1)
            ->find($id);
        
        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'News not found'
            ], 404);
        }
        
        // Add full image URL
        if ($news->image) {
            $news->full_image_url = asset($news->image);
        } else {
            $news->full_image_url = null;
        }
        
        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * Get latest news
     */
    public function latest(Request $request)
    {
        $limit = $request->get('limit', 5);
        
        $news = News::select('title','link', 'author', 'description', 'image', 'created_at')
            ->where('status', 1)
            ->latest()
            ->limit($limit)
            ->get();
        
        // Transform items to add full image URL
        $news->transform(function ($item) {
            if ($item->image) {
                $item->full_image_url = asset($item->image);
            } else {
                $item->full_image_url = null;
            }
            return $item;
        });
        
        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * Get news by language (Custom Pagination)
     */
    public function getByLanguage($languageId, Request $request)
    {
        $query = News::select('title','link', 'author', 'description', 'image', 'created_at')->where('status', 1)
            ->where('language_id', $languageId);
        
        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by country
        if ($request->has('country_id') && !empty($request->country_id)) {
            $query->where('country_id', $request->country_id);
        }
        
        $query->orderBy('created_at', 'desc');
        
        // Custom Pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;
        
        $totalItems = $query->count();
        $items = $query->skip($offset)->take($perPage)->get();
        
        // Transform items to add full image URL
        $items->transform(function ($item) {
            if ($item->image) {
                $item->full_image_url = asset($item->image);
            } else {
                $item->full_image_url = null;
            }
            return $item;
        });
        
        $totalPages = (int) ceil($totalItems / $perPage);
        $hasMore = $page < $totalPages;
        
        $pagination = [
            'current_page' => (int) $page,
            'per_page' => (int) $perPage,
            'total_items' => (int) $totalItems,
            'total_pages' => $totalPages,
            'has_more' => $hasMore,
            'data' => $items
        ];
        
        return response()->json([
            'success' => true,
            'pagination' => $pagination
        ]);
    }

    /**
     * Get news by category (Custom Pagination)
     */
    public function getByCategory($categoryId, Request $request)
    {
        $query = News::select('title','link', 'author', 'description', 'image', 'created_at')->where('status', 1)
            ->where('category_id', $categoryId);
        
        // Filter by language
        if ($request->has('language_id') && !empty($request->language_id)) {
            $query->where('language_id', $request->language_id);
        }
        
        // Filter by country
        if ($request->has('country_id') && !empty($request->country_id)) {
            $query->where('country_id', $request->country_id);
        }
        
        $query->orderBy('created_at', 'desc');
        
        // Custom Pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;
        
        $totalItems = $query->count();
        $items = $query->skip($offset)->take($perPage)->get();
        
        // Transform items to add full image URL
        $items->transform(function ($item) {
            if ($item->image) {
                $item->full_image_url = asset($item->image);
            } else {
                $item->full_image_url = null;
            }
            return $item;
        });
        
        $totalPages = (int) ceil($totalItems / $perPage);
        $hasMore = $page < $totalPages;
        
        $pagination = [
            'current_page' => (int) $page,
            'per_page' => (int) $perPage,
            'total_items' => (int) $totalItems,
            'total_pages' => $totalPages,
            'has_more' => $hasMore,
            'data' => $items
        ];
        
        return response()->json([
            'success' => true,
            'pagination' => $pagination
        ]);
    }

    /**
     * Get news by country (Custom Pagination)
     */
    public function getByCountry($countryId, Request $request)
    {
        $query = News::select('title','link', 'author', 'description', 'image', 'created_at')->where('status', 1)
            ->where('country_id', $countryId);
        
        // Filter by state
        if ($request->has('state_id') && !empty($request->state_id)) {
            $query->where('state_id', $request->state_id);
        }
        
        // Filter by language
        if ($request->has('language_id') && !empty($request->language_id)) {
            $query->where('language_id', $request->language_id);
        }
        
        $query->orderBy('created_at', 'desc');
        
        // Custom Pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;
        
        $totalItems = $query->count();
        $items = $query->skip($offset)->take($perPage)->get();
        
        // Transform items to add full image URL
        $items->transform(function ($item) {
            if ($item->image) {
                $item->full_image_url = asset($item->image);
            } else {
                $item->full_image_url = null;
            }
            return $item;
        });
        
        $totalPages = (int) ceil($totalItems / $perPage);
        $hasMore = $page < $totalPages;
        
        $pagination = [
            'current_page' => (int) $page,
            'per_page' => (int) $perPage,
            'total_items' => (int) $totalItems,
            'total_pages' => $totalPages,
            'has_more' => $hasMore,
            'data' => $items
        ];
        
        return response()->json([
            'success' => true,
            'pagination' => $pagination
        ]);
    }
}
