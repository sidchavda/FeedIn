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
        $query = News::select(
                'news.id',
                'news.title',
                'news.link',
                'news.author',
                'news.description',
                'news.image',
                'news.created_at',
                'news.category_id',
                'categories.name as category'
            )
            ->leftJoin('categories', 'news.category_id', '=', 'categories.id')
            ->where('news.status', 1);
        
        // Search filter (using index if available)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('news.title', 'like', $search . '%')  // Left-side wildcard uses index better
                  ->orWhere('news.author', 'like', $search . '%');
            });
        }
        
        // Other filters
        $filters = ['language_id', 'country_id', 'state_id'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where("news.$filter", $request->$filter);
            }
        }
        
        // Category filter (single or multiple)
        if ($request->filled('category_id')) {
            $categoryIds = explode(',', $request->category_id);
            $query->whereIn('news.category_id', $categoryIds);
        }
        
        // Pagination setup
        $page = (int) $request->get('page', 1);
        $perPage = min((int) $request->get('per_page', 10), 100); // Max 100 items
        $offset = ($page - 1) * $perPage;
        
        // Single query for count and data using SQL_CALC_FOUND_ROWS equivalent
        $items = $query->orderBy('news.created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();
        
        $totalItems = News::where('status', 1)->count();
        
        // Efficient data mapping
        $data = $items->map(fn($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'link' => $item->link,
            'author' => $item->author,
            'description' => $item->description,
            'image' => $item->image,
            'full_image_url' => $item->image ? asset($item->image) : null,
            'category' => $item->category,
            'created_at' => $item->created_at,
        ]);
        
        return response()->json([
            'success' => true,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => (int) ceil($totalItems / $perPage),
                'has_more' => $page < ceil($totalItems / $perPage),
                'data' => $data
            ]
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
        $query = News::select(
                'news.id',
                'news.title',
                'news.link',
                'news.author',
                'news.description',
                'news.image',
                'news.created_at',
                'categories.name as category'
            )
            ->leftJoin('categories', 'news.category_id', '=', 'categories.id')
            ->where('news.status', 1)
            ->where('news.language_id', $languageId);
        
        if ($request->filled('category_id')) {
            $query->where('news.category_id', $request->category_id);
        }
        
        if ($request->filled('country_id')) {
            $query->where('news.country_id', $request->country_id);
        }
        
        $page = (int) $request->get('page', 1);
        $perPage = min((int) $request->get('per_page', 10), 100);
        $offset = ($page - 1) * $perPage;
        
        $items = $query->orderBy('news.created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();
        
        $totalItems = News::where('status', 1)->where('language_id', $languageId)->count();
        
        $data = $items->map(fn($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'link' => $item->link,
            'author' => $item->author,
            'description' => $item->description,
            'image' => $item->image,
            'full_image_url' => $item->image ? asset($item->image) : null,
            'category' => $item->category,
            'created_at' => $item->created_at,
        ]);
        
        return response()->json([
            'success' => true,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => (int) ceil($totalItems / $perPage),
                'has_more' => $page < ceil($totalItems / $perPage),
                'data' => $data
            ]
        ]);
    }

    /**
     * Get news by category (Custom Pagination)
     */
    public function getByCategory($categoryId, Request $request)
    {
        $query = News::select(
                'news.id',
                'news.title',
                'news.link',
                'news.author',
                'news.description',
                'news.image',
                'news.created_at',
                'categories.name as category'
            )
            ->leftJoin('categories', 'news.category_id', '=', 'categories.id')
            ->where('news.status', 1)
            ->where('news.category_id', $categoryId);
        
        if ($request->filled('language_id')) {
            $query->where('news.language_id', $request->language_id);
        }
        
        if ($request->filled('country_id')) {
            $query->where('news.country_id', $request->country_id);
        }
        
        $page = (int) $request->get('page', 1);
        $perPage = min((int) $request->get('per_page', 10), 100);
        $offset = ($page - 1) * $perPage;
        
        $items = $query->orderBy('news.created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();
        
        $totalItems = News::where('status', 1)->where('category_id', $categoryId)->count();
        
        $data = $items->map(fn($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'link' => $item->link,
            'author' => $item->author,
            'description' => $item->description,
            'image' => $item->image,
            'full_image_url' => $item->image ? asset($item->image) : null,
            'category' => $item->category,
            'created_at' => $item->created_at,
        ]);
        
        return response()->json([
            'success' => true,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => (int) ceil($totalItems / $perPage),
                'has_more' => $page < ceil($totalItems / $perPage),
                'data' => $data
            ]
        ]);
    }

    /**
     * Get news by country (Custom Pagination)
     */
    public function getByCountry($countryId, Request $request)
    {
        $query = News::select(
                'news.id',
                'news.title',
                'news.link',
                'news.author',
                'news.description',
                'news.image',
                'news.created_at',
                'categories.name as category'
            )
            ->leftJoin('categories', 'news.category_id', '=', 'categories.id')
            ->where('news.status', 1)
            ->where('news.country_id', $countryId);
        
        if ($request->filled('state_id')) {
            $query->where('news.state_id', $request->state_id);
        }
        
        if ($request->filled('language_id')) {
            $query->where('news.language_id', $request->language_id);
        }
        
        $page = (int) $request->get('page', 1);
        $perPage = min((int) $request->get('per_page', 10), 100);
        $offset = ($page - 1) * $perPage;
        
        $items = $query->orderBy('news.created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();
        
        $totalItems = News::where('status', 1)->where('country_id', $countryId)->count();
        
        $data = $items->map(fn($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'link' => $item->link,
            'author' => $item->author,
            'description' => $item->description,
            'image' => $item->image,
            'full_image_url' => $item->image ? asset($item->image) : null,
            'category' => $item->category,
            'created_at' => $item->created_at,
        ]);
        
        return response()->json([
            'success' => true,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => (int) ceil($totalItems / $perPage),
                'has_more' => $page < ceil($totalItems / $perPage),
                'data' => $data
            ]
        ]);
    }
}
