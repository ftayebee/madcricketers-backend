<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Tournament;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\ValidationException;

class TournamentController extends Controller
{
    protected $module = 'tournaments';

    public function index()
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Tournaments Management',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    $this->module => [
                        'url' => route('admin.tournaments.index'),
                        'name' => 'Tournaments Management'
                    ]
                ]
            ]);

            return view('admin.pages.tournaments.index');
        } catch (Exception $e) {
            Log::error("Error Loading tournaments Management", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading tournaments Management.'
            ]);
        }
    }

    public function tableLoader(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $teams = Tournament::orderBy('created_at', 'desc')->get();

            $formattedData =  $teams->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'location' => $item->location,
                    'description' => $item->description,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'status' => $item->status,
                    'trophy_image' => $item->trophy_image,
                    'logo' => $item->logo,
                    'format' => $item->format,
                    'viewUrl' => route('admin.tournaments.show', $item->slug),
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

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-create')) {
                throw new Exception('Unauthorized Access');
            }

            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'slug'          => 'nullable|string|max:255',
                'location'      => 'nullable|string|max:255',
                'description'   => 'nullable|string',
                'start_date'    => 'nullable|date',
                'end_date'      => 'nullable|date|after_or_equal:start_date',
                'status'        => 'required|in:upcoming,ongoing,completed',
                'logo'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'trophy_image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'format'        => 'required|in:group,round-robin,knockout',
            ]);

            $tournament = new Tournament();
            $tournament->name        = $validated['name'];
            $tournament->slug        = Str::slug($validated['slug'] ?? $validated['name']);
            $tournament->location    = $validated['location'] ?? null;
            $tournament->description = $validated['description'] ?? null;
            $tournament->start_date  = $validated['start_date'] ?? null;
            $tournament->end_date    = $validated['end_date'] ?? null;
            $tournament->status      = $validated['status'];
            $tournament->format      = $validated['format']; // group or regular

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoFilename = 'tournament_logo_' . time() . '.' . $logo->getClientOriginalExtension();

                $logoPath = storage_path('app/public/uploads/tournaments');
                if (!file_exists($logoPath)) {
                    mkdir($logoPath, 0775, true);
                }

                Image::make($logo)->save($logoPath . '/' . $logoFilename);
                $tournament->logo = $logoFilename;
            }

            // Handle trophy image upload
            if ($request->hasFile('trophy_image')) {
                $trophy = $request->file('trophy_image');
                $trophyFilename = 'trophy_' . time() . '.' . $trophy->getClientOriginalExtension();

                $trophyPath = storage_path('app/public/uploads/tournaments');
                if (!file_exists($trophyPath)) {
                    mkdir($trophyPath, 0775, true);
                }

                Image::make($trophy)->save($trophyPath . '/' . $trophyFilename);
                $tournament->trophy_image = $trophyFilename;
            }

            $tournament->save();

            return redirect()->route('admin.tournaments.index')->with([
                'success' => true,
                'message' => 'Tournament has been created.',
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed in TournamentController@store', [
                'errors' => $e->validator->errors()->toArray(),
                'input'  => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with([
                    'success' => false,
                    'message' => 'Invalid tournament data submitted.',
                ]);
        } catch (Exception $e) {
            Log::error("Error saving tournament data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error saving tournament data.',
            ]);
        }
    }

    public function show($slug)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $tournament = Tournament::with(['groups', 'matches', 'standings', 'groups.teams'])->where('slug', $slug)->first();
            if ($tournament) {
                return view('admin.pages.tournaments.show', compact('tournament'));
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

    public function destroy($id)
    {
        try {
            $tournament = Tournament::with('groups')->findOrFail($id);

            // Delete logo if exists
            if ($tournament->logo && file_exists(storage_path('app/public/uploads/tournaments/' . $tournament->logo))) {
                unlink(storage_path('app/public/uploads/tournaments/' . $tournament->logo));
            }

            // Delete trophy image if exists
            if ($tournament->trophy_image && file_exists(storage_path('app/public/uploads/tournaments/' . $tournament->trophy_image))) {
                unlink(storage_path('app/public/uploads/tournaments/' . $tournament->trophy_image));
            }

            // Delete associated groups (and cascade deletes group_teams)
            foreach ($tournament->groups as $group) {
                $group->delete();
            }

            // Delete the tournament
            $tournament->delete();

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Tournament deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error("Error deleting tournament data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error deleting tournament data.',
            ]);
        }
    }
}
