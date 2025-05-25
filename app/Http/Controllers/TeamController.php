<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Display a listing of teams.
     */
    public function index(Request $request)
    {
        $query = Team::query()
            ->with(['leader', 'members'])
            ->withCount(['members', 'tasks']);

        // Filter by search term
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by leader
        if ($request->has('leader')) {
            $query->where('leader_id', $request->leader);
        }

        // Show only teams where user is member or leader
        if (!$request->user()->hasPermission('manage_teams')) {
            $query->where(function ($q) use ($request) {
                $q->where('leader_id', $request->user()->id)
                    ->orWhereHas('members', function ($q) use ($request) {
                        $q->where('user_id', $request->user()->id);
                    });
            });
        }

        $teams = $query->paginate(10);

        return view('teams.index', [
            'teams' => $teams,
            'leaders' => User::whereHas('ledTeams')->get(),
        ]);
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        $this->authorize('create', Team::class);

        $users = User::where('status', 'active')->get();

        return view('teams.create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(CreateTeamRequest $request)
    {
        try {
            DB::beginTransaction();

            $team = Team::create([
                'name' => $request->name,
                'description' => $request->description,
                'leader_id' => $request->leader_id,
            ]);

            // Add members
            if ($request->has('members')) {
                $team->members()->attach($request->members);
            }

            // Always add leader as a member if not already included
            if (!in_array($request->leader_id, $request->members ?? [])) {
                $team->members()->attach($request->leader_id);
            }

            // Log team creation
            activity_log('team_created', [
                'team_id' => $team->id,
                'created_by' => $request->user()->id,
            ]);

            DB::commit();

            return redirect()->route('teams.show', $team)
                ->with('status', 'Team created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->withErrors([
                'error' => 'An error occurred while creating the team.',
            ]);
        }
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);

        $team->load([
            'leader',
            'members',
            'tasks' => function ($query) {
                $query->latest()->take(5);
            },
        ]);

        $statistics = [
            'total_tasks' => $team->tasks()->count(),
            'completed_tasks' => $team->tasks()
                ->whereHas('status', function ($query) {
                    $query->where('status_name', 'completed');
                })
                ->count(),
            'active_tasks' => $team->tasks()
                ->whereNull('archived_at')
                ->count(),
            'overdue_tasks' => $team->tasks()
                ->where('due_date', '<', now())
                ->whereHas('status', function ($query) {
                    $query->whereNotIn('status_name', ['completed']);
                })
                ->count(),
        ];

        return view('teams.show', [
            'team' => $team,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        $this->authorize('update', $team);

        $users = User::where('status', 'active')->get();

        return view('teams.edit', [
            'team' => $team,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified team.
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        try {
            DB::beginTransaction();

            // Update basic info
            $team->update([
                'name' => $request->name,
                'description' => $request->description,
                'leader_id' => $request->leader_id,
            ]);

            // Handle member changes
            if ($request->has('members')) {
                $team->members()->attach($request->members);
            }

            if ($request->has('remove_members')) {
                $team->members()->detach($request->remove_members);
            }

            // Ensure leader is always a member
            if (!$team->members()->where('user_id', $request->leader_id)->exists()) {
                $team->members()->attach($request->leader_id);
            }

            // Log team update
            activity_log('team_updated', [
                'team_id' => $team->id,
                'updated_by' => $request->user()->id,
            ]);

            DB::commit();

            return redirect()->route('teams.show', $team)
                ->with('status', 'Team updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->withErrors([
                'error' => 'An error occurred while updating the team.',
            ]);
        }
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Request $request, Team $team)
    {
        $this->authorize('delete', $team);

        try {
            DB::beginTransaction();

            // Log team deletion
            activity_log('team_deleted', [
                'team_id' => $team->id,
                'deleted_by' => $request->user()->id,
            ]);

            // Delete team
            $team->delete();

            DB::commit();

            return redirect()->route('teams.index')
                ->with('status', 'Team deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while deleting the team.',
            ]);
        }
    }

    /**
     * Show team members management page.
     */
    public function showMembers(Team $team)
    {
        $this->authorize('update', $team);

        $team->load(['leader', 'members']);
        $availableUsers = User::where('status', 'active')
            ->whereNotIn('id', $team->members->pluck('id'))
            ->get();

        return view('teams.members', [
            'team' => $team,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Add members to the team.
     */
    public function addMembers(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $request->validate([
            'members' => ['required', 'array'],
            'members.*' => ['uuid', 'exists:users,id'],
        ]);

        try {
            DB::beginTransaction();

            $team->members()->attach($request->members);

            // Log members added
            activity_log('team_members_added', [
                'team_id' => $team->id,
                'added_by' => $request->user()->id,
                'members' => $request->members,
            ]);

            DB::commit();

            return back()->with('status', 'Team members added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while adding team members.',
            ]);
        }
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Request $request, Team $team, User $member)
    {
        $this->authorize('update', $team);

        if ($member->id === $team->leader_id) {
            return back()->withErrors([
                'error' => 'Cannot remove the team leader from the team.',
            ]);
        }

        try {
            DB::beginTransaction();

            $team->members()->detach($member->id);

            // Log member removal
            activity_log('team_member_removed', [
                'team_id' => $team->id,
                'removed_by' => $request->user()->id,
                'member_id' => $member->id,
            ]);

            DB::commit();

            return back()->with('status', 'Team member removed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while removing the team member.',
            ]);
        }
    }
}
