<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeamManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePermission($request, 'teams-view');

        $teams = Team::with(['players.user', 'captain.user'])
            ->withCount('players')
            ->latest()
            ->get()
            ->map(fn (Team $team) => $this->teamPayload($team));

        return response()->json(['success' => true, 'data' => $teams]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission($request, 'teams-create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'slug' => ['nullable', 'string', 'max:255'],
            'coach_name' => ['nullable', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'captain_id' => ['nullable', 'integer', 'exists:players,id'],
            'player_ids' => ['nullable', 'array'],
            'player_ids.*' => ['integer', 'exists:players,id'],
        ]);

        $this->validateCaptainSelection($validated['captain_id'] ?? null, $validated['player_ids'] ?? null);

        $team = new Team();
        $team->name = $validated['name'];
        $team->slug = $this->uniqueSlug($validated['slug'] ?? $validated['short_name'] ?? $validated['name']);
        $team->coach_name = $validated['coach_name'] ?? null;
        $team->manager_name = $validated['manager_name'] ?? null;
        $team->description = $validated['description'] ?? null;
        $team->captain_id = $validated['captain_id'] ?? null;
        $this->storeLogo($request, $team);
        $team->save();

        if (array_key_exists('player_ids', $validated)) {
            $team->players()->sync($validated['player_ids'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully.',
            'data' => $this->teamPayload($team->load(['players.user', 'captain.user'])),
        ], 201);
    }

    public function show(Request $request, Team $team)
    {
        $this->authorizePermission($request, 'teams-view');

        return response()->json([
            'success' => true,
            'data' => $this->teamPayload($team->load(['players.user', 'captain.user'])),
        ]);
    }

    public function update(Request $request, Team $team)
    {
        $this->authorizePermission($request, 'teams-edit');

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'slug' => ['nullable', 'string', 'max:255'],
            'coach_name' => ['nullable', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'captain_id' => ['nullable', 'integer', 'exists:players,id'],
            'player_ids' => ['nullable', 'array'],
            'player_ids.*' => ['integer', 'exists:players,id'],
        ]);

        $this->validateCaptainSelection($validated['captain_id'] ?? null, $validated['player_ids'] ?? null, $team);

        if (array_key_exists('name', $validated)) {
            $team->name = $validated['name'];
        }

        if ($request->filled('slug') || $request->filled('short_name')) {
            $team->slug = $this->uniqueSlug($validated['slug'] ?? $validated['short_name'], $team->id);
        }

        foreach (['coach_name', 'manager_name', 'description'] as $field) {
            if (array_key_exists($field, $validated)) {
                $team->{$field} = $validated[$field];
            }
        }

        if (array_key_exists('captain_id', $validated)) {
            $team->captain_id = $validated['captain_id'];
        }

        $this->storeLogo($request, $team);
        $team->save();

        if (array_key_exists('player_ids', $validated)) {
            $team->players()->sync($validated['player_ids'] ?? []);
            $this->clearCaptainIfUnassigned($team);
        }

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully.',
            'data' => $this->teamPayload($team->load(['players.user', 'captain.user'])),
        ]);
    }

    public function destroy(Request $request, Team $team)
    {
        $this->authorizePermission($request, 'teams-delete');

        if ($team->matchesAsTeamA()->exists() || $team->matchesAsTeamB()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Teams linked to matches cannot be deleted.',
            ], 422);
        }

        $team->players()->detach();
        $team->delete();

        return response()->json(['success' => true, 'message' => 'Team deleted successfully.']);
    }

    public function assignPlayers(Request $request, Team $team)
    {
        $this->authorizePermission($request, 'teams-edit');

        $validated = $request->validate([
            'player_ids' => ['required', 'array'],
            'player_ids.*' => ['integer', 'exists:players,id'],
            'mode' => ['nullable', 'in:sync,attach'],
            'captain_id' => ['nullable', 'integer', 'exists:players,id'],
        ]);

        if (($validated['mode'] ?? 'sync') === 'attach') {
            $team->players()->syncWithoutDetaching($validated['player_ids']);
        } else {
            $team->players()->sync($validated['player_ids']);
        }

        if (array_key_exists('captain_id', $validated)) {
            $this->validateCaptainSelection($validated['captain_id'], $validated['player_ids']);
            $team->captain_id = $validated['captain_id'];
            $team->save();
        } else {
            $this->clearCaptainIfUnassigned($team);
        }

        return response()->json([
            'success' => true,
            'message' => 'Team players updated successfully.',
            'data' => $this->teamPayload($team->load(['players.user', 'captain.user'])),
        ]);
    }

    public function removePlayer(Request $request, Team $team, Player $player)
    {
        $this->authorizePermission($request, 'teams-edit');

        $team->players()->detach($player->id);
        if ((int) $team->captain_id === (int) $player->id) {
            $team->captain_id = null;
            $team->save();
        }

        return response()->json(['success' => true, 'message' => 'Player removed from team.']);
    }

    private function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()?->can($permission), 403, 'Unauthorized Access');
    }

    private function teamPayload(Team $team): array
    {
        return [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'logo' => $team->logo,
            'coach_name' => $team->coach_name,
            'manager_name' => $team->manager_name,
            'description' => $team->description,
            'captain_id' => $team->captain_id,
            'captain' => $team->relationLoaded('captain') && $team->captain ? [
                'id' => $team->captain->id,
                'name' => $team->captain->user?->full_name,
                'role' => $team->captain->player_role,
                'batting_style' => $team->captain->batting_style,
                'bowling_style' => $team->captain->bowling_style,
                'image' => $team->captain->user?->image,
            ] : null,
            'players_count' => $team->players_count ?? $team->players->count(),
            'players' => $team->relationLoaded('players') ? $team->players->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $player->user?->full_name,
                'role' => $player->player_role,
                'batting_style' => $player->batting_style,
                'bowling_style' => $player->bowling_style,
                'image' => $player->user?->image,
            ])->values() : [],
        ];
    }

    private function validateCaptainSelection(?int $captainId, ?array $playerIds, ?Team $team = null): void
    {
        if (!$captainId) {
            return;
        }

        if (is_array($playerIds)) {
            abort_unless(in_array($captainId, array_map('intval', $playerIds), true), 422, 'Captain must be one of the selected team players.');
            return;
        }

        if ($team) {
            abort_unless($team->players()->whereKey($captainId)->exists(), 422, 'Captain must be assigned to this team first.');
            return;
        }

        abort(422, 'Captain must be selected from assigned team players.');
    }

    private function clearCaptainIfUnassigned(Team $team): void
    {
        if ($team->captain_id && !$team->players()->whereKey($team->captain_id)->exists()) {
            $team->captain_id = null;
            $team->save();
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $counter = 1;

        while (Team::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function storeLogo(Request $request, Team $team): void
    {
        if (!$request->hasFile('logo')) {
            return;
        }

        $path = $request->file('logo')->store('public/uploads/teams');
        $team->logo = basename($path);
    }
}
