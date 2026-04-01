<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Session;
use Response;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = User::query();
        // Search
        $search = $request->search;
        $query = $query->where(function($query) use ($search){
            $query->orWhere('name', 'like', "%".$search."%");
            $query->orWhere('email', 'like', "%".$search."%");
        });
        $users = $query->orderBy('created_at','desc')->paginate(config('constants.PAGINATION_LIMIT'));

        return view('pages.users.index')->with('users', $users);
    }

    public function add(Request $request)
    {
        return view('pages.users.add');
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            // 'mobile' => 'required|email'
        ]);
        $request->merge([
            'password' => Hash::make('123456'),
        ]);
        if(empty($request->get('is_admin'))) {
            $request->merge([
                'is_admin' => false,
            ]);
        }
        $user = User::create(request()->all());
        if($user) {
            Session::flash('alert_msg', 'User created successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }
        return redirect()->route('admin.users.index');
    }

    public function edit($id)
    {
        $user = User::find($id);
        return view('pages.users.add')->with('user',$user);
    }

    public function update($id, Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'mobile' => 'required|max:10|unique:users,mobile,'.$id,
        ]);

        // if(!empty($request->get('password'))) {
        //     $request->merge([
        //         'password' => Hash::make($request->get('password'))
        //     ]);
        // }
        $updateException = ['_token'];

        $user = User::where('id', $id)->update(request()->except($updateException));

        if($user) {
            Session::flash('alert_msg', 'User updated successfully!');
            Session::flash('alert_class', 'success');
        } else {
            Session::flash('alert_msg', 'Something went wrong!');
            Session::flash('alert_class', 'danger');
        }

        return redirect()->route('admin.user.edit', [$id]);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if($user) {
            $user->delete();
            $response = ['success' => true, 'msg' => 'User deleted successfully'];
        } else {
            $response = ['success' => false, 'msg' => 'No user found!'];
        }
        return Response::json($response);
    }

    /**
     * Show profile page
     */
    public function profile()
    {
        $user = auth()->user();
        return view('pages.users.profile', compact('user'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $this->validate($request,[
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'mobile' => 'nullable|max:15',
        ]);

        $updateData = $request->only(['name', 'email', 'mobile']);
        
        // Handle profile image upload
        if ($request->hasFile('avatar')) {
            $imageName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('images/avatars'), $imageName);
            $updateData['avatar'] = 'images/avatars/' . $imageName;
        }

        User::where('id', $user->id)->update($updateData);

        Session::flash('alert_msg', 'Profile updated successfully!');
        Session::flash('alert_class', 'success');

        return redirect()->route('admin.profile');
    }

    /**
     * Show password change page
     */
    public function password()
    {
        return view('pages.users.password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $this->validate($request,[
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            Session::flash('alert_msg', 'Current password is incorrect!');
            Session::flash('alert_class', 'danger');
            return redirect()->route('admin.password');
        }

        // Update password
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->password)
        ]);

        Session::flash('alert_msg', 'Password changed successfully!');
        Session::flash('alert_class', 'success');

        return redirect()->route('admin.password');
    }
}
