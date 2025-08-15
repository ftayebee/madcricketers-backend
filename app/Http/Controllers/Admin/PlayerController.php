<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{
    protected $module = 'players';

    public function index()
    {
        try {
            if (!Auth::user()->can($this->module.'-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Player Management',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    $this->module => [
                        'url' => route('admin.players.index'),
                        'name' => 'Player Management'
                    ]
                ]
            ]);

            return view('admin.pages.players.index');
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

    public function tableLoader(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module .'-view')) {
                throw new Exception('Unauthorized Access');
            }

            $users = User::query()
                ->when(Auth::user()->hasRole('player') === false, function ($query) {
                    $query->role('player'); // uses Spatie's `role` scope
                })
                ->get();

            $formattedData =  $users->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image' => $item->image,
                    'name' => $item->full_name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'status' => $item->status,
                    'gender' => $item->gender,
                    'national_id' => $item->national_id,
                    'role' => $item->role->name,
                    'roleSlug' => $item->role->name,
                    'playerType' => $item->player ? $item->player->player_type : 'guest',
                    'playerRole' => $item->player ? $item->player->player_role : '-',
                    'viewUrl' => route('admin.players.show', $item->id),
                ];
            });

            return response()->json(['data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Error Loading players table", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading players table.'
            ]);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user()->can($this->module .'-view')) {
                throw new Exception('Unauthorized Access');
            }

            $user = User::with(['role', 'player'])->findOrFail($id);
            if ($user) {
                $roles = Role::all();
                return view('admin.pages.players.show', compact('user', 'roles'));
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

    public function approve(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $isApproved = $request->has('approve') && $request->input('approve') == 'on';

            if ($user->player) {
                $user->player->update([
                    'player_type' => $isApproved ? 'registered' : 'guest'
                ]);
            }

            return redirect()->route('admin.players.show', $id)->with([
                'success' => true,
                'message' => 'Player status updated successfully.'
            ]);

        } catch (Exception $e) {
            Log::error("Error updating player status", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error updating player status.'
            ]);
        }
    }
}
