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

            $teams = Team::orderBy('created_at', 'desc')->get();

            $formattedData =  $teams->map(function ($item) {
                Log::info('Logo: ' . $item->logo);
                return [
                    'id' => $item->id,
                    'logo' => $item->logo,
                    'name' => $item->name,
                    'coach_name' => $item->coach_name,
                    'manager_name' => $item->manager_name,
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

            $team = Team::with('players')->where('slug',$id)->first();
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
            ]);

            // i want log info for invalid data and attribute is invalid

            $team = new Team();
            $team->name = $validated['name'];
            $team->coach_name = $validated['coach_name'] ?? null;
            $team->manager_name = $validated['manager_name'] ?? null;
            $team->description = $validated['description'] ?? null;

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
            ]);

            $team = Team::findOrFail($id);

            $team->name = $validated['name'];
            $team->coach_name = $validated['coach_name'] ?? null;
            $team->manager_name = $validated['manager_name'] ?? null;
            $team->description = $validated['description'] ?? null;

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
}
