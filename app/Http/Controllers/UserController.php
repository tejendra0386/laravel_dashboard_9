<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if($request->user()->can('view-user')){
            if ($request->ajax()) {
                $data = User::latest()->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $editurl = route('user.edit', $row->id);
                        $deleteurl = route('user.destroy', $row->id);
                        $csrf_token = csrf_token();
                        $btn = "<a href='$editurl' class='edit btn btn-primary btn-sm'>Edit</a>
                        <form action='$deleteurl' method='POST' style='display:inline-block;'>
                        <input type='hidden' name='_token' value='$csrf_token'>
                        <input type='hidden' name='_method' value='DELETE' />
                            <button type='submit' class='btn btn-danger btn-sm'>Delete</button>
                        </form>
                        ";
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
                }

            return view('backend.users.index');
        }else{
            return view('backend.permission.permission');
        }


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        if($request->user()->can('create-user')){
            $roles = Role::all();
            return view('backend.users.create', compact('roles'));
        }else{
            return view('backend.permission.permission');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        if($request->user()->can('create-user')){
            $data = $this->validate($request, [
                'name'=>'required|string|max:255',
                'email'=>'required|string|email|max:255|unique:users',
                'role_id'=>'required',
                'password' => 'sometimes|min:8|confirmed',
            ]);
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->roles()->attach($data['role_id']);
            $permissions = RolePermission::where('role_id', $data['role_id'])->get();
            $selectedperm = array();
                foreach($permissions as $permission){
                    $selectedperm[] = $permission->permission_id;
                }
            $user->permissions()->attach($selectedperm);
            $user->save();
            return redirect()->route('user.index')->with('success', 'User Successfully Created');
        }else{
            return view('backend.permission.permission');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        //
        if($request->user()->can('edit-user')){
            $roles = Role::all();
            $userrole = UserRole::where('user_id', $id)->first();
            $user = User::findorfail($id);
            return view('backend.users.edit', compact('roles', 'userrole', 'user'));
        }else{
            return view('backend.permission.permission');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        if($request->user()->can('edit-user')){
            $user = User::findorfail($id);
            if(isset($_POST['updatedetails'])){
                $data = $this->validate($request, [
                    'name'=>'required|string|max:255',
                    'email'=>'required|string|email|max:255|unique:users,email,'.$user->id,
                    'role_id'=>'required',
                ]);
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);
                $user->roles()->sync($data['role_id']);
                $permissions = RolePermission::where('role_id', $data['role_id'])->get();
                $selectedperm = array();
                    foreach($permissions as $permission){
                        $selectedperm[] = $permission->permission_id;
                    }
                $user->permissions()->sync($selectedperm);
                $user->save();
                return redirect()->route('user.index')->with('success', 'UserDetails Successfully updated');
            }
            elseif(isset($_POST['updatepassword'])){
                $data = $this->validate($request, [
                    'oldpassword' => 'required',
                    'new_password' => 'sometimes|min:8|confirmed|different:password',
                ]);

                if (Hash::check($data['oldpassword'], $user->password)) {
                    if (!Hash::check($data['new_password'] , $user->password)) {
                        $newpass = Hash::make($data['new_password']);

                        $user->update([
                            'password' => $newpass,
                        ]);
                        $user->save;
                        session()->flash('success','password updated successfully');
                        return redirect()->route('user.index');
                    }

                    else{
                            session()->flash('error','new password can not be the old password!');
                            return redirect()->back();
                        }

                    }

                else {
                    session()->flash('errorpass', 'Password does not match');
                    return redirect()->back();
                }
            }

        }else{
            return view('backend.permission.permission');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
        if($request->user()->can('remove-user')){
            $user = User::findorfail($id);
            $user->delete();
            return redirect()->route('user.index')->with('success', "User Successfully Deleted");
        }else{
            return view('backend.permission.permission');
        }
    }
}
