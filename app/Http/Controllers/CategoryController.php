<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Category;
use App\Models\Language;

use Response;
class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * This function is the constructor for the CategoryController class.
     * It is used to initialize the middleware for the class.
     *
     * @return void
     */

    public function index(Request $request)
    {
        $query = Category::with('language');
        // Search `
        $search = $request->search;
        $query = $query->where(function($query) use ($search){
            $query->orWhere('name', 'like', "%".$search."%");
        });
        $categories = $query->orderBy('created_at','desc')->paginate(config('constants.PAGINATION_LIMIT'));

        return view('pages.category.index')->with('categories', $categories);
    }

    public function add(Request $request)
    {
        $languages = Language::orderBy('name')->get();
        return view('pages.category.add', ['languages' => $languages]);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'language_id' => 'required|exists:languages,id',
        ]);

        $payload = [
            'name' => $request->name,
            'language_id' => $request->language_id,
            'is_active' => !empty($request->status) ? 1 : 0,
        ];

        $user = Category::create($payload);
        if($user) {
            Session::flash('alert_msg', 'Category created successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }
        return redirect()->route('admin.category.index');
    }

    public function edit($id)
    {
        $category = Category::find($id);
        $languages = Language::orderBy('name')->get();

        return view('pages.category.add', ['category' => $category, 'languages' => $languages]);
    }

    public function update($id, Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'language_id' => 'required|exists:languages,id',
        ]);
        $data = [
            'name' => $request->name,
            'language_id' => $request->language_id,
            'is_active' => !empty($request->status) ? 1 : 0,
        ];

        $user = Category::where('id', $id)->update($data);

        if($user) {
            Session::flash('alert_msg', 'Category updated successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }

        return redirect()->route('admin.category.edit', [$id]);
    }

   

    /**
     * Get categories by language ID
     */
    public function getCategoriesByLanguage($languageId)
    {
        $categories = Category::where('language_id', $languageId)
            ->where('is_active', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        return response()->json($categories);
    }

    /**
     * Delete category
     */
    public function delete($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'msg' => 'Category not found'
            ]);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Category deleted successfully'
        ]);
    }
}
