<?php

namespace App\Http\Controllers\Backend;

use App\Exports\PermissionExport;
use App\Http\Controllers\Controller;
use App\Imports\PermissionImport;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function AllPermission()
    {
        $permissions = Permission::latest()->get();

        return view('backend.pages.permission.all_permission', compact('permissions'));
    } // End Method

    public function AddPermission()
    {

        return view('backend.pages.permission.add_permission');

    } // End Method

    public function StorePermission(Request $request)
    {

        $permission = Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

        $notification = [
            'message' => 'Permission Created Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.permission')->with($notification);

    } // End Method

    public function EditPermission($id)
    {

        $permission = Permission::find($id);

        return view('backend.pages.permission.edit_permission', compact('permission'));

    }// End Method

    public function UpdatePermission(Request $request)
    {
        $per_id = $request->id;

        Permission::find($per_id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

        $notification = [
            'message' => 'Permission Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.permission')->with($notification);

    } // End Method

    public function DeletePermission($id)
    {

        Permission::find($id)->delete();

        $notification = [
            'message' => 'Permission Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }// End Method

    public function ImportPermission()
    {

        return view('backend.pages.permission.import_permission');

    }// End Method

    public function Export()
    {

        return Excel::download(new PermissionExport, 'permission.xlsx');

    }// End Method

    public function Import(Request $request)
    {

        Excel::import(new PermissionImport, $request->file('import_file'));

        $notification = [
            'message' => 'Permission Imported Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }// End Method

    /////////// All Roles Mehtod //////////////////////

    public function AllRoles()
    {

        $roles = Role::latest()->get();

        return view('backend.pages.roles.all_roles', compact('roles'));

    }// End Method

    public function AddRoles()
    {
        return view('backend.pages.roles.add_roles');
    }// End Method

    public function StoreRoles(Request $request)
    {

        Role::create([
            'name' => $request->name,
        ]);

        $notification = [
            'message' => 'Role Created Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.roles')->with($notification);

    }// End Method

    public function EditRoles($id)
    {

        $roles = Role::find($id);

        return view('backend.pages.roles.edit_roles', compact('roles'));

    }// End Method

    public function UpdateRoles(Request $request)
    {

        $role_id = $request->id;

        Role::find($role_id)->update([
            'name' => $request->name,
        ]);

        $notification = [
            'message' => 'Role Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.roles')->with($notification);

    }// End Method

    public function DeleteRoles($id)
    {

        Role::find($id)->delete();

        $notification = [
            'message' => 'Role Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }// End Method

    public function AddRolesPermission()
    {

        $roles = Role::all();
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();

        return view('backend.pages.rolesetup.add_roles_permission', compact('roles', 'permissions', 'permission_groups'));

    }// End Method

    public function RolePermissionStore(Request $request)
    {

        $data = [];
        $permissions = $request->permission;

        foreach ($permissions as $key => $item) {
            $data['role_id'] = $request->role_id;
            $data['permission_id'] = $item;

            DB::table('role_has_permissions')->insert($data);
        } // end foreach

        $notification = [
            'message' => 'Role Permission Added Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.roles.permission')->with($notification);

    }// End Method

    public function AllRolesPermission()
    {

        $roles = Role::all();

        return view('backend.pages.rolesetup.all_roles_permission', compact('roles'));

    }// End Method

    public function AdminEditRoles($id)
    {

        $role = Role::find($id);
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();

        return view('backend.pages.rolesetup.edit_roles_permission', compact('role', 'permissions', 'permission_groups'));

    }// End Method

    public function AdminRolesUpdate(Request $request, $id)
    {

        $role = Role::find($id);
        $permissions = $request->permission;

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        $notification = [
            'message' => 'Role Permission Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.roles.permission')->with($notification);

    }// End Method

    public function AdminDeleteRoles($id)
    {

        $role = Role::find($id);
        if (! is_null($role)) {
            $role->delete();
        }

        $notification = [
            'message' => 'Role Permission Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }// End Method

}
