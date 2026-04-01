<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Smtp;

use Response;
class SmtpController extends Controller
{
    public function index(Request $request)
    {
        $query = Smtp::query();
        // Search
        $search = $request->search;
        $query = $query->where(function($query) use ($search){
            $query->orWhere('server', 'like', "%".$search."%");
        });
        $smtps = $query->orderBy('created_at','desc')->paginate(config('constants.PAGINATION_LIMIT'));

        return view('pages.smtp.index',['smtps' => $smtps]);

    }

    public function add(Request $request)
    {
        $smtp = new Smtp();
        return view('pages.smtp.add',['smtp' => $smtp]);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'server' => 'required|max:255|unique:smtps',
            'port' => 'required',
            'username' => 'required',
        ]);

        $data = Smtp::create(request()->all());
        if($data) {
            Session::flash('alert_msg', 'SMTP created successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }
        return redirect()->route('admin.smtp.index');
    }

    public function edit($id)
    {
        $smtp = Smtp::find($id);

        return view('pages.smtp.add')->with('smtp',$smtp);
    }

    public function update($id, Request $request)
    {

        $this->validate($request,[
            'server' => 'required|max:255',
        ]);

        if(empty($request->get('status'))) {
            $request->merge([
                'is_active' => false,
            ]);
        }
        $updateException = ['_token'];

        $data = Smtp::where('id', $id)->update(request()->except($updateException));

        if($data) {
            Session::flash('alert_msg', 'Smtp updated successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }

        return redirect()->route('admin.smtp.edit', [$id]);
    }

    public function delete($id)
    {
        $user = Smtp::find($id);
        if($user) {
            $user->delete();
            $response = ['success' => true, 'msg' => 'Smtp deleted successfully'];
        } else {
            $response = ['success' => false, 'msg' => 'No category found!'];
        }
        return Response::json($response);
    }

}
