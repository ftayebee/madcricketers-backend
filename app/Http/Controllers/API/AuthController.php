<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    public function ping()
    {
        return response()->json([
            'success' => true,
            'message' => 'API connected',
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::with('roles')->where('email', $request->input('email'))->first();

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided email or password is incorrect.',
                ], 401);
            }

            if (($user->status ?? 'active') !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact an administrator.',
                ], 403);
            }

            $token = $user->createToken($request->input('device_name', 'flutter-mobile'))->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'token_type' => 'Bearer',
                'token' => $token,
                'user' => $this->userPayload($user),
            ]);
        } catch (Throwable $exception) {
            Log::error('API login failed', [
                'email' => $request->input('email'),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to login right now. Please try again.',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $this->userPayload($request->user()->load('roles')),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    private function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'image' => $user->image,
            'roles' => $user->roles->pluck('name')->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'can_manage_scoreboard' => $user->can('cricket-matches-scoreboard') || $user->can('scoreboard-edit'),
            'can_manage_teams' => $user->can('teams-create') || $user->can('teams-edit') || $user->can('teams-delete'),
            'can_manage_matches' => $user->can('cricket-matches-create') || $user->can('cricket-matches-edit'),
        ];
    }
}
