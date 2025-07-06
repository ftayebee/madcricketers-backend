<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    protected $module = 'roles';

    public function index()
    {
        try {
            if (!Auth::user()->can('roles-view')) {
                Log::info("Auth user is " . Auth::user()->full_name . " permission status is " . Auth::user()->can('roles-view'));
                abort(403, 'Unauthorized');
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
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $roles = Role::all();

            $formattedData =  $roles->map(function($item){
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'grantedPermissions' => $item->permissions()->count() . ' / ' . Permission::all()->count(),
                    'status' => $item->status,
                    'viewUrl' => route('admin.settings.roles.show', $item->slug),
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

    public function store(Request $request){
        try{
            $validated = Validator::make($request->all(), [
                'name' => 'string',
                'status' => 'string'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'There has been a error while saving role. Please check log.'
                ]);
            }

            $role = new Role();
            $role->name = $request->name;
            $role->slug = Str::slug($request->name);
            $role->description = null;
            $role->status =  $request->status;
            $role->save();

            return response()->json([
                'success' => true,
                'message' => 'Role has been saved successfully.'
            ]);
        } catch(Exception $e) {
            Log::error("Error saving Role" , [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving Role'
            ]);
        }
    }

    public function show($slug){
        try{
            // if (!auth()->user()->hasPermission($this->module, 'read')) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized Permission'
            //     ]);
            // }

            $role = Role::where('slug', $slug)->first();
            if($role){
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
                        $role->slug => [
                            'url' => '',
                            'name' => $role->name
                        ]
                    ]
                ]);
                $actions = ['read', 'write', 'create', 'delete', 'import', 'export', 'generate'];
                $permissions = $role->permissions->pluck('action', 'module')->toArray();
                return view('admin.pages.roles.show', compact('role', 'actions', 'permissions'));
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
}
