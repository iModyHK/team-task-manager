<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Team;
use App\Models\Task;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// User's private channel for notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Team channel for team-wide updates
Broadcast::channel('team.{teamId}', function ($user, $teamId) {
    return $user->teams()->where('id', $teamId)->exists();
});

// Task channel for real-time task updates
Broadcast::channel('task.{taskId}', function ($user, $taskId) {
    $task = Task::find($taskId);
    if (!$task) return false;
    
    // Allow access if user is in the task's team
    return $user->teams()->where('id', $task->team_id)->exists();
});

// Admin channel for system-wide updates
Broadcast::channel('admin', function ($user) {
    return $user->hasRole('admin');
});

// Presence channel for team members online status
Broadcast::channel('team.{teamId}.presence', function ($user, $teamId) {
    if ($user->teams()->where('id', $teamId)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
        ];
    }
});
