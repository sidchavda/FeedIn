<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Response;

class LanguageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Language::query();
        $search = $request->search;
        $query = $query->where(function ($query) use ($search) {
            $query->orWhere('name', 'like', "%".$search."%");
            $query->orWhere('code', 'like', "%".$search."%");
        });
        $languages = $query->orderBy('created_at', 'desc')->paginate(config('constants.PAGINATION_LIMIT'));

        return view('pages.language.index')->with('languages', $languages);
    }

    public function add(Request $request)
    {
        return view('pages.language.add');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'code' => 'required|max:10|unique:languages,code',
        ]);

        $payload = [
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => !empty($request->status) ? 1 : 0,
        ];

        $data = Language::create($payload);
        if ($data) {
            Session::flash('alert_msg', 'Language created successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }

        return redirect()->route('admin.language.index');
    }

    public function edit($id)
    {
        $language = Language::find($id);

        return view('pages.language.add')->with('language', $language);
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'code' => 'required|max:10|unique:languages,code,'.$id,
        ]);

        $data = [
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => !empty($request->status) ? 1 : 0,
        ];

        $updated = Language::where('id', $id)->update($data);

        if ($updated) {
            Session::flash('alert_msg', 'Language updated successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }

        return redirect()->route('admin.language.edit', [$id]);
    }

    public function delete($id)
    {
        $language = Language::find($id);
        if ($language) {
            $language->delete();
            $response = ['success' => true, 'msg' => 'Language deleted successfully'];
        } else {
            $response = ['success' => false, 'msg' => 'No language found!'];
        }

        return Response::json($response);
    }
}
