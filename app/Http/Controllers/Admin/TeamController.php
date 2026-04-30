<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Team;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    protected $module = 'teams';

    public function index()
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Team Management',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    $this->module => [
                        'url' => route('admin.teams.index'),
                        'name' => 'Team Management'
                    ]
                ]
            ]);

            return view('admin.pages.teams.index');
        } catch (Exception $e) {
            Log::error("Error Loading Team Management", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Team Management.'
            ]);
        }
    }

    public function tableLoader(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $teams = Team::with('captain.user')->orderBy('created_at', 'desc')->get();

            $formattedData =  $teams->map(function ($item) {
                return [
                    'id' => $item->id,
                    'logo' => $item->logo,
                    'name' => $item->name,
                    'coach_name' => $item->coach_name,
                    'manager_name' => $item->manager_name,
                    'captain_name' => $item->captain?->user?->full_name,
                    'players_count' => $item->players->count(),
                    'viewUrl' => route('admin.teams.show', $item->slug),
                ];
            });

            return response()->json(['data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Error Loading teams table", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading teams table.'
            ]);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $team = Team::with(['players.user', 'captain.user'])->where('slug', $id)->first();
            if ($team) {
                return view('admin.pages.teams.show', compact('team'));
            }
        } catch (Exception $e) {
            Log::error("Error showing team data: ", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error showing team data.'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-create')) {
                throw new Exception('Unauthorized Access');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'coach_name' => 'nullable|string|max:255',
                'manager_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'captain_id' => 'nullable|integer|exists:players,id',
                'player_ids' => 'nullable|array',
                'player_ids.*' => 'integer|exists:players,id',
            ]);

            $this->validateCaptainSelection($validated['captain_id'] ?? null, $validated['player_ids'] ?? null);

            // i want log info for invalid data and attribute is invalid

            $team = new Team();
            $team->name = $validated['name'];
            $team->coach_name = $validated['coach_name'] ?? null;
            $team->manager_name = $validated['manager_name'] ?? null;
            $team->description = $validated['description'] ?? null;
            $team->captain_id = $validated['captain_id'] ?? null;

            // Handle slug
            $slugInput = $validated['slug'] ?? $validated['name'];
            $team->slug = Str::slug($slugInput);

            // Handle logo
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'team_' . time() . '.' . $file->getClientOriginalExtension();

                $image = Image::make($file);

                $uploadPath = storage_path('app/public/uploads/teams');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }

                $image->save($uploadPath . '/' . $filename);
                $team->logo = $filename;
            }

            $team->save();

            if (array_key_exists('player_ids', $validated)) {
                $team->players()->sync($validated['player_ids'] ?? []);
            }

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Team has been created.',
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed in TeamController@store', [
                'errors' => $e->validator->errors()->toArray(),
                'input'  => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with([
                    'success' => false,
                    'message' => 'Invalid data submitted.',
                ]);
        } catch (Exception $e) {
            Log::error("Error saving team data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error saving team data.',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!Auth::user()->can($this->module . '-edit')) {
                throw new Exception('Unauthorized Access');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'coach_name' => 'nullable|string|max:255',
                'manager_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'captain_id' => 'nullable|integer|exists:players,id',
            ]);

            $team = Team::findOrFail($id);

            $team->name = $validated['name'];
            $team->coach_name = $validated['coach_name'] ?? null;
            $team->manager_name = $validated['manager_name'] ?? null;
            $team->description = $validated['description'] ?? null;
            if (array_key_exists('captain_id', $validated)) {
                $this->validateCaptainSelection($validated['captain_id'], null, $team);
                $team->captain_id = $validated['captain_id'];
            }

            // Slug fallback
            $slugInput = $validated['slug'] ?? $validated['name'];
            $team->slug = Str::slug($slugInput);

            // Handle logo
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'team_' . $team->id . '_' . time() . '.' . $file->getClientOriginalExtension();

                $image = Image::make($file);

                $uploadPath = storage_path('app/public/uploads/teams');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }

                $image->save($uploadPath . '/' . $filename);
                $team->logo = $filename;
            }

            $team->save();

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Team has been updated.',
            ]);
        } catch (Exception $e) {
            Log::error("Error updating team data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error updating team data.',
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::user()->can($this->module . '-delete')) {
                throw new Exception('Unauthorized Access');
            }

            $team = Team::findOrFail($id);

            if ($team->logo && file_exists(storage_path('app/public/uploads/teams/' . $team->logo))) {
                unlink(storage_path('app/public/uploads/teams/' . $team->logo));
            }

            if (method_exists($team, 'players')) {
                $team->players()->detach();
            }

            $team->delete();

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Team deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error("Error deleting team data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error deleting team data.',
            ]);
        }
    }

    public function assignPlayers(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-edit')) {
                throw new Exception('Unauthorized Access');
            }

            Log::info($request->all());
            $validated = Validator::make($request->all(), [
                'team_id' => 'required|exists:teams,id',
                'player_ids' => 'required|array',
                'player_ids.*' => 'exists:players,id',
                'captain_id' => 'nullable|integer|exists:players,id',
            ]);

            if ($validated->fails()) {
                Log::error($validated->errors());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Failed',
                    'errors' => $validated->errors()
                ], 422);
            }

            $team = Team::find($request->team_id);

            $team->players()->sync($request->player_ids);
            if ($request->filled('captain_id')) {
                $this->validateCaptainSelection((int) $request->captain_id, array_map('intval', $request->player_ids));
                $team->captain_id = (int) $request->captain_id;
                $team->save();
            } elseif ($team->captain_id && !$team->players()->whereKey($team->captain_id)->exists()) {
                $team->captain_id = null;
                $team->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Players assigned successfully',
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function validateCaptainSelection(?int $captainId, ?array $playerIds, ?Team $team = null): void
    {
        if (!$captainId) {
            return;
        }

        if (is_array($playerIds)) {
            if (!in_array($captainId, array_map('intval', $playerIds), true)) {
                throw ValidationException::withMessages([
                    'captain_id' => 'Captain must be one of the selected team players.',
                ]);
            }
            return;
        }

        if ($team && !$team->players()->whereKey($captainId)->exists()) {
            throw ValidationException::withMessages([
                'captain_id' => 'Captain must be assigned to this team first.',
            ]);
        }
    }
}
