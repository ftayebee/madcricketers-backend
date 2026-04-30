<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Player;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
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
            if (!Auth::user()->can($this->module . '-view')) {
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
            if (Auth::user() && !Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $users = User::query()
                ->when(Auth::user()->hasRole('player') === false, function ($query) {
                    $query->role('player'); // uses Spatie's `role` scope
                })
                ->with(['role', 'roles'])
                ->get();

            $formattedData =  $users->map(function ($item) {
                $role = $item->primary_role;

                return [
                    'id' => $item->id,
                    'image' => $item->image,
                    'name' => $item->full_name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'status' => $item->status,
                    'gender' => $item->gender,
                    'national_id' => $item->national_id,
                    'role' => optional($role)->name,
                    'roleSlug' => optional($role)->name,
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
            if (!Auth::user()->can($this->module . '-view')) {
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
            if (!Auth::user()->can($this->module . '-edit')) {
                throw new Exception('Unauthorized Access');
            }

            $user = User::findOrFail($id);
            $isApproved = $request->has('approve') && $request->input('approve') == 'on' || $request->input('approve') == 'registered';

            if ($user->player) {
                $user->player->update([
                    'player_type' => $isApproved ? 'registered' : 'guest'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Player status updated successfully.',
                'redirect' => $request->redirection ?? url()->current()
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

    public function destroy(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-delete')) {
                throw new Exception('Unauthorized Access');
            }

            $user = User::findOrFail($request->input('id'));

            if ($user->player) {
                $user->player->delete();
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Player deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error("Error deleting player", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting player.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $type = $request->input('type', 'full'); // full or jersey
            $format = $request->input('format', 'excel'); // excel or pdf

            $fileName = "player_{$type}_list_" . date('Y_m_d_His');
            $export = new \App\Exports\PlayerListExport($type);

            if ($format === 'pdf') {
                return \Maatwebsite\Excel\Facades\Excel::download($export, "{$fileName}.pdf", \Maatwebsite\Excel\Excel::DOMPDF);
            }

            return \Maatwebsite\Excel\Facades\Excel::download($export, "{$fileName}.xlsx");

        } catch (Exception $e) {
            Log::error("Error exporting player list", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error exporting player list.'
            ]);
        }
    }
}
