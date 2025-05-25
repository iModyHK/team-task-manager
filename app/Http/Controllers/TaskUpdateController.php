<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\CreateUpdateRequest;
use App\Models\Task;
use App\Models\TaskUpdate;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskUpdateController extends Controller
{
    /**
     * Display task updates.
     */
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $updates = $task->updates()
            ->with(['user', 'mentions', 'mentionedUsers'])
            ->latest()
            ->paginate(10);

        return view('tasks.updates.index', [
            'task' => $task,
            'updates' => $updates,
        ]);
    }

    /**
     * Store a new task update.
     */
    public function store(CreateUpdateRequest $request, Task $task)
    {
        try {
            DB::beginTransaction();

            // Create update
            $update = TaskUpdate::create([
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'content' => $request->content,
                'ai_generated' => $request->boolean('ai_generated', false),
            ]);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('task-attachments', 'public');
                    
                    TaskAttachment::create([
                        'task_id' => $task->id,
                        'filename' => $path,
                        'file_hash' => hash_file('sha256', $file->path()),
                    ]);
                }
            }

            // Process mentions
            if ($request->has('mentioned_users')) {
                foreach ($request->mentioned_users as $userId) {
                    $update->mentions()->create([
                        'mentioned_user_id' => $userId,
                    ]);

                    // Create notification for mentioned user
                    \App\Models\Notification::create([
                        'user_id' => $userId,
                        'type' => 'mention',
                        'data' => [
                            'task_id' => $task->id,
                            'update_id' => $update->id,
                            'mentioned_by' => $request->user()->id,
                        ],
                    ]);
                }
            }

            // Log update creation
            activity_log('task_update_created', [
                'task_id' => $task->id,
                'update_id' => $update->id,
                'created_by' => $request->user()->id,
            ]);

            DB::commit();

            // Return update partial view for AJAX requests
            if ($request->ajax()) {
                $update->load(['user', 'mentions', 'mentionedUsers']);
                return view('tasks.updates.partials.update', [
                    'update' => $update,
                ])->render();
            }

            return back()->with('status', 'Update added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while adding the update.',
                ], 500);
            }

            return back()->withInput()->withErrors([
                'error' => 'An error occurred while adding the update.',
            ]);
        }
    }

    /**
     * Update a task update.
     */
    public function update(Request $request, Task $task, TaskUpdate $update)
    {
        $this->authorize('update', $update);

        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
        ]);

        try {
            DB::beginTransaction();

            $update->update([
                'content' => $request->content,
            ]);

            // Log update modification
            activity_log('task_update_modified', [
                'task_id' => $task->id,
                'update_id' => $update->id,
                'modified_by' => $request->user()->id,
            ]);

            DB::commit();

            if ($request->ajax()) {
                $update->load(['user', 'mentions', 'mentionedUsers']);
                return view('tasks.updates.partials.update', [
                    'update' => $update,
                ])->render();
            }

            return back()->with('status', 'Update modified successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while modifying the update.',
                ], 500);
            }

            return back()->withErrors([
                'error' => 'An error occurred while modifying the update.',
            ]);
        }
    }

    /**
     * Delete a task update.
     */
    public function destroy(Request $request, Task $task, TaskUpdate $update)
    {
        $this->authorize('delete', $update);

        try {
            DB::beginTransaction();

            // Log update deletion
            activity_log('task_update_deleted', [
                'task_id' => $task->id,
                'update_id' => $update->id,
                'deleted_by' => $request->user()->id,
            ]);

            // Delete update
            $update->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Update deleted successfully.',
                ]);
            }

            return back()->with('status', 'Update deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while deleting the update.',
                ], 500);
            }

            return back()->withErrors([
                'error' => 'An error occurred while deleting the update.',
            ]);
        }
    }

    /**
     * Load more updates (AJAX).
     */
    public function loadMore(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $page = $request->input('page', 1);
        $updates = $task->updates()
            ->with(['user', 'mentions', 'mentionedUsers'])
            ->latest()
            ->paginate(10, ['*'], 'page', $page);

        return view('tasks.updates.partials.updates', [
            'updates' => $updates,
        ])->render();
    }

    /**
     * Generate AI suggestion for task update.
     */
    public function generateAiSuggestion(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        try {
            // This is a placeholder for AI integration
            // In a real application, you would integrate with an AI service
            $suggestion = "AI-generated update suggestion based on task context and history.";

            return response()->json([
                'suggestion' => $suggestion,
            ]);

        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'error' => 'An error occurred while generating the AI suggestion.',
            ], 500);
        }
    }
}
