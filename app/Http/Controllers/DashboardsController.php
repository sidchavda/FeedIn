<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\User;
use App\Models\News;
use App\Models\Category;
use App\Models\Language;

class DashboardsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $userCount = User::where('is_admin', 0)->count();
        $newsCount = News::where('status', 1)->count();
        $categoryCount = Category::where('is_active', 1)->count();
        $languageCount = Language::where('is_active', 1)->count();
        
        return view('pages.dashboard.index', compact('userCount', 'newsCount', 'categoryCount', 'languageCount'));
    }

}
