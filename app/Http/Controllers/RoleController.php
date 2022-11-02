<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Str;

class RoleController extends Controller
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
        if($request->user()->can("view-role")){
            if ($request->ajax()) {
                $data = Role::with('roles_permissions')->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('permissions', function($row){
                        $permissions = RolePermission::where('role_id', $row->id)->get();
                        $perm = '';
                        foreach($permissions as $permission){
                            $permissi = Permission::where('id', $permission->permission_id)->first();
                            $perm .= '<span class="badge bg-green">'.$permissi->name. '</span>'. ' ' ;
                        }
                        return $perm;
                    })
                    ->addColumn('action', function($row){
                        $editurl = route('roles.edit', $row->id);
                        $deleteurl = route('roles.destroy', $row->id);
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
                    ->rawColumns(['permissions', 'action'])
                    ->make(true);
                }

            return view('backend.roles.index');
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
        if($request->user()->can("create-role")){
            $permissions = Permission::all();
            return view('backend.roles.create', compact('permissions'));
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
        if($request->user()->can("create-role")){
            $data = $this->validate($request, [
                'name' => 'required|string',
                'permissions' =>'required',
                'permissions.'=>'integer',
            ]);

            $slug = Str::slug($data['name']);
            $role= Role::create([
                'name' => $data['name'],
                'slug'=> $slug,
            ]);
            $permissions = $data['permissions'];
            foreach($permissions as $permission){
                $role->permissions()->attach($permission);
            }
            $role->save();
            return redirect()->route('roles.index')->with('success', 'Role Created Successfully');
        }else{
            return view("backend.permission.permission");
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
        if($request->user()->can("edit-role")){
            $role = Role::findorfail($id);
            $permissions = Permission::all();
            $roles_permissions = RolePermission::where('role_id', $id)->get();
            $selectedperm = array();
            foreach($roles_permissions as $rolepermission){
                $selectedperm[] = $rolepermission->permission_id;
            }
            return view('backend.roles.edit', compact('role', 'permissions', 'selectedperm'));
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
        if ($request->user()->can('edit-role')) {
            $role = Role::findorfail($id);
            $data = $this->validate($request, [
                'name' => 'required|string',
                'permissions' =>'required',
                'permissions.'=>'integer',
            ]);

            $slug = Str::slug($data['name']);
            $role->update([
                'name' => $data['name'],
                'slug'=> $slug,
            ]);
            $permissions = $data['permissions'];
            $perm = array();
            foreach($permissions as $permission){
                $perm[] = $permission;
                $role->permissions()->sync($perm);
            }
            $role->save();
            return redirect()->route('roles.index')->with('success', 'Role Updated Successfully');
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
        if ($request->user()->can('remove-role')) {
            $role = Role::findorFail($id);
            $userrolecount = UserRole::where('role_id', $id)->count();
            if($userrolecount>0){
                return redirect()->back()->with('error', "Can't Delete! There are users inside this role");
            }else{
                $role->delete();
                return redirect()->route('roles.index')->with('success', 'Role Deleted Successfully');
            }
        }else{
            return view('backend.permission.permission');
        }
    }
}
