<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    protected $module = 'roles';

    public function index()
    {
        try {
            if (!Auth::user()->can('roles-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Role Management',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    'role' => [
                        'url' => route('admin.settings.roles.index'),
                        'name' => 'Role Management'
                    ]
                ]
            ]);

            return view('admin.pages.roles.index');
        } catch (Exception $e) {
            Log::error("Error Loading Role Management", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Role Management.'
            ]);
        }
    }

    public function tableLoader(Request $request){
        try{
            if (!Auth::user()->can('roles-view')) {
                throw new Exception('Unauthorized Access');
            }

            $roles = Role::all();

            $formattedData =  $roles->map(function($item){
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'grantedPermissions' => $item->permissions()->count() . ' / ' . Permission::all()->count(),
                    'status' => $item->status,
                    'viewUrl' => route('admin.settings.roles.show', $item->name),
                ];
            });

            return response()->json(['data' => $formattedData]);
        } catch(Exception $e) {
            Log::error("Error Loading Role Management" , [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Role Management.'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Permission check
            if (!Auth::user()->can('roles-create')) {
                throw new Exception('Unauthorized Access');
            }

            // Validate input
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
            ]);

            // Create role using Spatie's Role model
            $role = Role::firstOrCreate(
                ['name' => strtolower($request->input('name'))],
                ['guard_name' => 'web']
            );

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Role has been saved successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error saving Role", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error saving Role.'
            ]);
        }
    }

    public function show($slug){
        try{
            if (!Auth::user()->can('permissions-view')) {
                throw new Exception('Unauthorized Permission');
            }

            // Find role by name or slug
            $role = Role::where('name', $slug)->first();

            if ($role) {
                // Set page title & breadcrumbs
                session([
                    'title' => 'Permissions',
                    'breadcrumbs' => [
                        'home' => [
                            'url' => route('admin.dashboard'),
                            'name' => 'Dashboard'
                        ],
                        'role' => [
                            'url' => route('admin.settings.roles.index'),
                            'name' => 'Role Management'
                        ],
                        ucfirst($role->name) => [
                            'url' => '',
                            'name' => ucfirst($role->name)
                        ]
                    ]
                ]);

                // Get list of modules from config file
                $moduleList = config('modules', []);

                // Get all available permissions
                $permissions = \Spatie\Permission\Models\Permission::all();

                // Group permissions by module (assuming naming convention: module-action)
                $groupedPermissions = [];
                foreach ($permissions as $permission) {
                    $parts = explode('-', $permission->name);
                    $module = $parts[0] ?? 'other';
                    $groupedPermissions[$module][] = $permission;
                }

                // Get assigned permissions for this role
                $rolePermissions = $role->permissions->pluck('name')->toArray();
                $actions = ['view', 'create', 'edit', 'delete'];

                return view('admin.pages.roles.show', compact('role', 'groupedPermissions', 'rolePermissions', 'moduleList', 'actions'));
            }

            throw new Exception('Role Not Found for ' . $slug);
        } catch(Exception $e) {
            Log::error("Error Loading Role Management" , [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Role Management.'
            ]);
        }
    }

    public function update(Request $request, $id){
        try{
            $validated = Validator::make($request->all(), [
                'name' => 'string',
                'status' => 'string'
            ]);

            if($validated->fails()){
                throw new Exception('Error Saving Role.' , 500);
                return response()->json([
                    'success' => false,
                    'message' => 'There has been a error while saving role. Please check log.'
                ]);
            }

            $role = Role::findOrFail($id);

            if($role){
                $role->name = $request->name;
                $role->slug = Str::slug($request->name);
                $role->description = null;
                $role->status =  $request->status;
                $role->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Role has been saved successfully.'
                ]);
            }
            throw new Exception('Error Updating Role.' , 500);
        } catch(Exception $e) {
            Log::error("Error saving Role" , [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error saving Role'
            ]);
        }
    }

    public function destroy($id){
        try{
            $role = Role::where('id', $id)->first();
            if($role){
                return response()->json([
                    'success' => true,
                    'message' => 'Role has been deleted successfully.'
                ]);
            }

            throw new Exception('Role Not Found for ' . $id);
        } catch(Exception $e) {
            Log::error("Error Deleting Role" , [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Deleting Role.'
            ]);
        }
    }

    public function permissionUpdate(Request $request)
    {
        try {
            $validate = $request->validate([
                'module'  => 'required|string',
                'action'  => 'required|string',
                'role_id' => 'required|integer|exists:roles,id',
                'checked' => 'required',
            ],[
                'module.required' => 'Module is required',
                'action.required' => 'Action is required',
                'role_id.required' => 'Role is required',
                'checked.required' => 'Status is required',
            ]);

            $module = $request->input('module');
            $action = $request->input('action');
            $roleId = $request->input('role_id');
            $checked = filter_var($request->input('checked'), FILTER_VALIDATE_BOOLEAN);

            // Find the role
            $role = Role::findOrFail($roleId);

            // Permission name convention: module-action, e.g. users-create
            // If action = 'all', assign/remove all module's permissions
            if ($action === 'all') {
                // Get all permissions that belong to this module
                $permissions = Permission::where('name', 'like', "$module-%")->get();

                if ($checked) {
                    $role->givePermissionTo($permissions);
                } else {
                    $role->revokePermissionTo($permissions);
                }
            } else {
                $permissionName = "$module-$action";

                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName],
                    ['guard_name' => 'web'] // adjust guard if needed
                );

                if ($checked) {
                    $role->givePermissionTo($permission);
                } else {
                    $role->revokePermissionTo($permission);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully.',
            ]);
        } catch (Exception $e) {
            Log::error("Error Updating Permissions", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating permissions.',
            ], 500);
        }
    }

    public function seedDatabase(Request $request){
        try{
            Artisan::call('db:seed', [
                '--class' => 'PermissionSeeder',
                '--force' => true
            ]);

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Database seeded successfully.'
            ]);
        } catch (Exception $e) {
            Log::error("Error Seeding Database" , [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Seeding Database'
            ]);
        }
    }
}
