<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('Player registration request received', [
                'request' => $request->all()
            ]);
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|mimes:jpg,jpeg,png|max:1024',
                'full_name'       => 'required|string|max:255',
                'nickname'        => 'nullable|string|max:255',
                'username'        => 'nullable|string|max:255',
                'email'           => 'required|email|unique:users,email|max:255',
                'phone'           => 'required|string|max:15',
                'blood_group'     => 'nullable|string|max:3',
                'password'        => 'required|string|min:8',
                'gender'          => 'nullable|in:male,female,other',
                'date_of_birth'   => 'nullable|date|before:today',
                'religion'        => 'nullable|string|max:255',
                'national_id'     => 'nullable|digits_between:10,17|unique:users,national_id',
                'address'         => 'nullable|string|max:500',
                'player_type'     => 'required|in:guest,registered',
                'player_role'     => 'required|string|max:50',
                'batting_style'   => 'required|string|max:50',
                'bowling_style'   => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $email = $request->input('email');
            $username = empty($request->input('username')) ? explode('@', $email)[0] : $request->input('username');

            $user = new User();
            $user->full_name    = $request->input('full_name');
            $user->nickname     = $request->input('nickname');
            $user->username     = $username;
            $user->email        = $email;
            $user->phone        = $request->input('phone');
            $user->blood_group  = $request->input('blood_group');
            $user->status       = 'active';

            $password           = $request->input('password');
            if (!empty($password)) {
                $user->password     = bcrypt($password);
                $user->visible_pass = $password;
            }

            $user->gender       = $request->input('gender');
            $user->date_of_birth = $request->input('date_of_birth');
            $user->religion     = $request->input('religion');
            $user->national_id  = $request->input('national_id');
            $user->address      = $request->input('address');
            $user->role_id      = 3;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'user_' . time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);

                // Resize if large
                if ($image->filesize() > 200 * 1024) {
                    $image->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode($file->getClientOriginalExtension(), 75);
                }

                $quality = 75;
                while (strlen((string) $image) > 200 * 1024 && $quality > 10) {
                    $image->encode($file->getClientOriginalExtension(), $quality);
                    $quality -= 5;
                }

                $uploadPath = storage_path('app/public/uploads/players');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }
                $image->save($uploadPath . '/' . $filename);
                $user->image = $filename;
            }

            $user->save();
            $role = Role::findOrFail($user->role_id);
            $user->syncRoles([$role]);

            $player                 = new Player();
            $player->user_id        = $user->id;
            $player->player_type    = $request->input('player_type');
            $player->player_role    = $request->input('player_role');
            $player->batting_style  = $request->input('batting_style');
            $player->bowling_style  = $request->input('bowling_style');
            $player->save();

            DB::commit();

            Log::info('Player registration successful', [
                'user_id' => $user->id,
                'player_id' => $player->id
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Player registered successfully.',
                'user' => $user,
                'player' => $player
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error Saving User", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllPlayers(Request $request)
    {
        try {
            $players = Player::with(['user', 'teams'])->get();
            return response()->json([
                'success' => true,
                'data' => $players
            ], 200);
        } catch (Exception $e) {
            Log::error("Error fetching players", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching players.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
