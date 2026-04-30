<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $module = 'users';

    private function selectedRole(Request $request): Role
    {
        return Role::findOrFail($request->input('general.role_id'));
    }

    public function index()
    {
        try {
            if (!Auth::user()->can('users-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'User Management',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    $this->module => [
                        'url' => route('admin.settings.users.index'),
                        'name' => 'User Management'
                    ]
                ]
            ]);

            return view('admin.pages.users.index');
        } catch (Exception $e) {
            Log::error("Error Loading User Management", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading User Management.'
            ]);
        }
    }

    public function loader(Request $request)
    {
        try {
            if (!Auth::user()->can('users-view')) {
                throw new Exception('Unauthorized Access');
            }
            $users = User::query()
                ->when(!Auth::user()->hasRole('player'), function ($query) {
                    $query->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'player');
                    });
                })
                ->with(['role', 'roles'])
                ->whereNotIn('id', [Auth::id()])
                ->get();

            $formattedData = $users->map(function ($item) {
                $role = $item->primary_role;

                return [
                    'id' => $item->id,
                    'image' => $item->image,
                    'name' => $item->full_name,
                    'email' => $item->email,
                    'status' => $item->status,
                    'gender' => $item->gender,
                    'national_id' => $item->national_id,
                    'phone' => $item->phone,
                    'role' => optional($role)->name,
                    'roleSlug' => optional($role)->name,
                    'viewUrl' => route('admin.settings.users.show', $item->id),
                ];
            });

            return response()->json(['data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Error Loading users table", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading users table.'
            ]);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user()->can('users-view')) {
                throw new Exception('Unauthorized Access');
            }

            $user = User::with(['role'])->findOrFail($id);
            if ($user) {
                $roles = Role::all();
                return view('admin.pages.users.show', compact('user', 'roles'));
            }
        } catch (Exception $e) {
            Log::error("Error showing user", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error showing user.'
            ]);
        }
    }

    public function create()
    {
        try {
            if (!Auth::user()->can('users-create')) {
                throw new Exception('Unauthorized Access');
            }

            $roles = Role::all();
            return view('admin.pages.users.create', compact('roles'));
        } catch (Exception $e) {
            Log::error("Error loading create view", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error loading create view.'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can('users-create')) {
                throw new Exception('Unauthorized Access');
            }
            
            $validator = Validator::make($request->all(), [
                'general.profile_picture' => 'nullable|mimes:jpg,jpeg,png|max:1024',
                'general.full_name'       => 'required|string|max:255',
                'general.nickname'        => 'nullable|string|max:255',
                'general.email'           => 'required|email|unique:users,email|max:255',
                'general.phone'           => 'required|string|max:15',
                'general.status'          => 'required|in:active,inactive',
                'general.role_id'         => 'required|exists:roles,id',
                'general.blood_group'     => 'nullable|string|max:3',
                'general.religion'        => 'nullable|string|max:255',
                'general.gender'          => 'nullable|in:male,female,other',
                'general.date_of_birth'   => 'nullable|date|before:today',
                'general.password'        => 'required|string|min:8',
                'general.address'         => 'nullable|string|max:500',
                'general.national_id'     => 'nullable|digits_between:10,17',
                'hasPlayerInfo'           => 'required'
            ]);

            if ($validator->fails()) {
                Log::info("Validator Failed: ", ['data' => $validator->errors()]);
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();

            $email = $request->input('general.email');
            $username = explode('@', $email)[0];
            $role = $this->selectedRole($request);

            $user = new User();
            $user->full_name    = $request->input('general.full_name');
            $user->nickname     = $request->input('general.nickname');
            $user->username     = $username;
            $user->email        = $email;
            $user->phone        = $request->input('general.phone');
            $user->blood_group  = $request->input('general.blood_group');
            $user->status       = $request->input('general.status');
            $password           = $request->input('general.password');

            if (!empty($password)) {
                $user->password = Hash::make($password);
            }
            $user->national_id  = $request->input('general.national_id');
            $user->religion     = $request->input('general.religion');
            $user->gender       = $request->input('general.gender');
            $user->date_of_birth = $request->input('general.date_of_birth');
            $user->address      = $request->input('general.address');
            $user->role_id      = $role->id;

            // Handle profile picture
            if ($request->hasFile('general.profile_picture')) {
                $file = $request->file('general.profile_picture');
                $filename = 'user_' . time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);

                // Resize if large
                if ($image->filesize() > 200 * 1024) {
                    $image->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode($file->getClientOriginalExtension(), 75);
                }

                // Compress
                $quality = 75;
                while (strlen((string) $image) > 200 * 1024 && $quality > 10) {
                    $image->encode($file->getClientOriginalExtension(), $quality);
                    $quality -= 5;
                }

                // Save image — use players/ folder when creating a player user
                $uploadFolder   = $role->name === 'player' ? 'players' : 'users';
                $uploadPath     = storage_path('app/public/uploads/' . $uploadFolder);
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }
                $image->save($uploadPath . '/' . $filename);
                $user->image = $filename;
            }

            $user->save();
            $user->syncRoles([$role]);

            $hasPlayerInfo = filter_var($request->input('hasPlayerInfo'), FILTER_VALIDATE_BOOLEAN);
            if($role->name === 'player' || $hasPlayerInfo == true) {
                $player = new Player();
                $player->user_id        = $user->id;
                $player->player_type    = $request->input('player.type');
                $player->player_role    = $request->input('player.role');
                $player->batting_style  = $request->input('player.batting_style');
                $player->bowling_style  = $request->input('player.bowling_style');
                $player->jursey_number  = $request->input('player.jursey_number');
                $player->jursey_name  = $request->input('player.jursey_name');
                $player->jursey_size  = $request->input('player.jursey_size');
                $player->chest_measurement  = $request->input('player.chest_measurement');
                $player->save();
            }

            DB::commit();

            return redirect($request->input('redirect') ?? route('admin.settings.users.index'))->with([
                'success' => true,
                'message' => 'User Updated successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error Saving User", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return redirect()->back()->withInput()->with([
                'success' => false,
                'message' => 'An error occurred while saving the user.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function edit($id)
    {
        try {
            if (!Auth::user()->can('users-edit')) {
                throw new Exception('Unauthorized Access');
            }

            $roles = Role::all();
            $user = User::findOrFail($id);
            return view('admin.pages.users.edit', compact('roles', 'user'));
        } catch (Exception $e) {
            Log::error("Error loading create view", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error loading create view.'
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!Auth::user()->can('users-edit')) {
                throw new Exception('Unauthorized Access');
            }

            $validator = Validator::make($request->all(), [
                'general.profile_picture' => 'nullable|string',
                'general.full_name'       => 'required|string|max:255',
                'general.nickname'        => 'nullable|string|max:255',
                'general.email'           => 'required|email|max:255|unique:users,email,' . $id,
                'general.phone'           => 'required|string|max:15',
                'general.status'          => 'required|in:active,inactive',
                'general.role_id'         => 'required|exists:roles,id',
                'general.blood_group'     => 'nullable|string|max:3',
                'general.religion'        => 'nullable|string|max:255',
                'general.gender'          => 'nullable|in:male,female,other',
                'general.date_of_birth'   => 'nullable|date|before:today',
                'general.password'        => 'nullable|string|min:8',
                'general.address'         => 'nullable|string|max:500',
                'general.national_id'     => 'nullable|digits_between:10,17',
            ]);

            $user = User::findOrFail($id);

            if ($validator->fails()) {
                Log::info($validator->errors());
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if(!$user){
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'User not found.',
                ])->withInput();
            }

            DB::beginTransaction();

            $email = $request->input('general.email');
            $username = explode('@', $email)[0];
            $role = $this->selectedRole($request);

            $user->full_name    = $request->input('general.full_name');
            $user->nickname     = $request->input('general.nickname');
            $user->username     = $username;
            $user->email        = $email;
            $user->phone        = $request->input('general.phone');
            $user->blood_group  = $request->input('general.blood_group');
            $user->status       = $request->input('general.status');
            $password           = $request->input('general.password');

            if (!empty($password)) {
                $user->password = Hash::make($password);
            }

            $user->national_id  = $request->input('general.national_id');
            $user->religion     = $request->input('general.religion');
            $user->gender       = $request->input('general.gender');
            $user->date_of_birth = $request->input('general.date_of_birth');
            $user->address      = $request->input('general.address');
            $user->role_id      = $role->id;

            // Handle profile picture
            $profilePicture = $request->input('general.profile_picture');

            if ($profilePicture) {
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $profilePicture);
                $imageData = str_replace(' ', '+', $imageData);
                $imageBinary = base64_decode($imageData);

                $extension = explode('/', mime_content_type($profilePicture))[1]; // png, jpg, etc.
                $filename = 'user_' . time() . '.' . $extension;

                $basePath = storage_path('app/public/uploads');
                $uploadPath = $user->hasRole('player') ? $basePath . '/players' : $basePath . '/users';

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }

                file_put_contents($uploadPath . '/' . $filename, $imageBinary);

                $user->image = $filename;
            }

            $user->update();

            $user->syncRoles([$role]);

            if($user->hasRole('player')){
                $player                 = $user->player ?: new Player();
                $player->user_id        = $user->id;
                $player->player_type    = $request->input('player.player_type');
                $player->player_role    = $request->input('player.player_role');
                $player->batting_style  = $request->input('player.batting_style');
                $player->bowling_style  = $request->input('player.bowling_style');
                $player->jursey_number     = $request->input('player.jursey_number');
                $player->jursey_name       = $request->input('player.jursey_name');
                $player->jursey_size       = $request->input('player.jursey_size');
                $player->chest_measurement = $request->input('player.chest_measurement');
                $player->save();
            }
            DB::commit();

            return redirect($request->input('redirect') ?? route('admin.settings.users.index'))->with([
                'success' => true,
                'message' => 'User Updated successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error Updating User", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return redirect()->back()->withInput()->with([
                'success' => false,
                'message' => 'An error occurred while saving the user.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            if (!Auth::user()->can('users-delete')) {
                throw new Exception('Unauthorized Access');
            }

            $user = User::findOrFail($id);

            if($user->player){
                $user->player->delete();
            }

            $user->delete();

            return redirect()->back()->with([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error("Error Deleting User.", [
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "message" => $e->getMessage(),
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'An error occurred while deleting the user.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
