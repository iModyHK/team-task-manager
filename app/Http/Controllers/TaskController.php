<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskStatus;
use App\Models\TaskPriority;
use App\Models\TaskLabel;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        $query = Task::query()
            ->with([
                'team',
                'status',
                'priority',
                'label',
                'creator',
                'assignees',
            ])
            ->when(!$request->user()->hasPermission('view_all_tasks'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('created_by', $request->user()->id)
                        ->orWhereHas('assignees', function ($q) use ($request) {
                            $q->where('user_id', $request->user()->id);
                        })
                        ->orWhereHas('team', function ($q) use ($request) {
                            $q->where('leader_id', $request->user()->id);
                        });
                });
            });

        // Apply filters
        if ($request->has('team')) {
            $query->where('team_id', $request->team);
        }

        if ($request->has('status')) {
            $query->where('status_id', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority_id', $request->priority);
        }

        if ($request->has('label')) {
            $query->where('label_id', $request->label);
        }

        if ($request->has('assignee')) {
            $query->whereHas('assignees', function ($q) use ($request) {
                $q->where('user_id', $request->assignee);
            });
        }

        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('task_name', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('meeting_title', 'like', "%{$search}%");
            });
        }

        // Handle archived tasks
        if ($request->archived === 'true') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        // Sort tasks
        $sortField = $request->sort ?? 'created_at';
        $sortDirection = $request->direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $tasks = $query->paginate(20);

        return view('tasks.index', [
            'tasks' => $tasks,
            'teams' => Team::all(),
            'statuses' => TaskStatus::all(),
            'priorities' => TaskPriority::all(),
            'labels' => TaskLabel::all(),
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $this->authorize('create', Task::class);

        $user = auth()->user();
        $teams = $user->teams;

        return view('tasks.create', [
            'teams' => $teams,
            'statuses' => TaskStatus::all(),
            'priorities' => TaskPriority::all(),
            'labels' => TaskLabel::all(),
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(CreateTaskRequest $request)
    {
        try {
            DB::beginTransaction();

            // Create task
            $task = Task::create([
                'team_id' => $request->team_id,
                'source' => $request->source,
                'meeting_title' => $request->meeting_title,
                'meeting_date' => $request->meeting_date,
                'task_name' => $request->task_name,
                'status_id' => $request->status_id,
                'priority_id' => $request->priority_id,
                'label_id' => $request->label_id,
                'automation_rule_id' => $request->automation_rule_id,
                'due_date' => $request->due_date,
                'cco' => $request->cco,
                'created_by' => $request->user()->id,
            ]);

            // Assign users
            $task->assignees()->attach($request->assignees);

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

            // Create subtasks
            if ($request->has('subtasks')) {
                foreach ($request->subtasks as $subtaskTitle) {
                    $task->subtasks()->create([
                        'title' => $subtaskTitle,
                        'status' => 'pending',
                    ]);
                }
            }

            // Add dependencies
            if ($request->has('dependencies')) {
                $task->dependencies()->attach($request->dependencies);
            }

            // Handle custom fields
            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    $task->customFieldValues()->create([
                        'field_id' => $fieldId,
                        'value' => $value,
                    ]);
                }
            }

            // Log task creation
            activity_log('task_created', [
                'task_id' => $task->id,
                'created_by' => $request->user()->id,
            ]);

            DB::commit();

            return redirect()->route('tasks.show', $task)
                ->with('status', 'Task created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->withErrors([
                'error' => 'An error occurred while creating the task.',
            ]);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'team',
            'status',
            'priority',
            'label',
            'creator',
            'assignees',
            'attachments',
            'subtasks',
            'dependencies',
            'updates' => function ($query) {
                $query->latest();
            },
            'nextSteps',
            'pmoComments',
        ]);

        return view('tasks.show', [
            'task' => $task,
            'statuses' => TaskStatus::all(),
            'priorities' => TaskPriority::all(),
            'labels' => TaskLabel::all(),
        ]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $task->load([
            'team',
            'assignees',
            'attachments',
            'subtasks',
            'dependencies',
        ]);

        return view('tasks.edit', [
            'task' => $task,
            'teams' => auth()->user()->teams,
            'statuses' => TaskStatus::all(),
            'priorities' => TaskPriority::all(),
            'labels' => TaskLabel::all(),
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        try {
            DB::beginTransaction();

            // Update basic info
            $task->update($request->only([
                'team_id',
                'source',
                'meeting_title',
                'meeting_date',
                'task_name',
                'status_id',
                'priority_id',
                'label_id',
                'automation_rule_id',
                'due_date',
                'cco',
            ]));

            // Handle assignee changes
            if ($request->has('add_assignees')) {
                $task->assignees()->attach($request->add_assignees);
            }

            if ($request->has('remove_assignees')) {
                $task->assignees()->detach($request->remove_assignees);
            }

            // Handle attachment changes
            if ($request->hasFile('add_attachments')) {
                foreach ($request->file('add_attachments') as $file) {
                    $path = $file->store('task-attachments', 'public');
                    
                    TaskAttachment::create([
                        'task_id' => $task->id,
                        'filename' => $path,
                        'file_hash' => hash_file('sha256', $file->path()),
                    ]);
                }
            }

            if ($request->has('remove_attachments')) {
                $attachments = TaskAttachment::whereIn('id', $request->remove_attachments)
                    ->where('task_id', $task->id)
                    ->get();

                foreach ($attachments as $attachment) {
                    Storage::disk('public')->delete($attachment->filename);
                    $attachment->delete();
                }
            }

            // Handle subtask changes
            if ($request->has('add_subtasks')) {
                foreach ($request->add_subtasks as $subtaskTitle) {
                    $task->subtasks()->create([
                        'title' => $subtaskTitle,
                        'status' => 'pending',
                    ]);
                }
            }

            if ($request->has('update_subtasks')) {
                foreach ($request->update_subtasks as $subtask) {
                    $task->subtasks()
                        ->where('id', $subtask['id'])
                        ->update([
                            'title' => $subtask['title'],
                            'status' => $subtask['status'],
                        ]);
                }
            }

            if ($request->has('remove_subtasks')) {
                $task->subtasks()
                    ->whereIn('id', $request->remove_subtasks)
                    ->delete();
            }

            // Handle dependency changes
            if ($request->has('add_dependencies')) {
                $task->dependencies()->attach($request->add_dependencies);
            }

            if ($request->has('remove_dependencies')) {
                $task->dependencies()->detach($request->remove_dependencies);
            }

            // Handle custom field changes
            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    $task->customFieldValues()
                        ->updateOrCreate(
                            ['field_id' => $fieldId],
                            ['value' => $value]
                        );
                }
            }

            // Log task update
            activity_log('task_updated', [
                'task_id' => $task->id,
                'updated_by' => $request->user()->id,
            ]);

            DB::commit();

            return redirect()->route('tasks.show', $task)
                ->with('status', 'Task updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->withErrors([
                'error' => 'An error occurred while updating the task.',
            ]);
        }
    }

    /**
     * Archive the specified task.
     */
    public function archive(Request $request, Task $task)
    {
        $this->authorize('archive', $task);

        try {
            $task->archive();

            // Log task archival
            activity_log('task_archived', [
                'task_id' => $task->id,
                'archived_by' => $request->user()->id,
            ]);

            return back()->with('status', 'Task archived successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while archiving the task.',
            ]);
        }
    }

    /**
     * Unarchive the specified task.
     */
    public function unarchive(Request $request, Task $task)
    {
        $this->authorize('archive', $task);

        try {
            $task->unarchive();

            // Log task unarchival
            activity_log('task_unarchived', [
                'task_id' => $task->id,
                'unarchived_by' => $request->user()->id,
            ]);

            return back()->with('status', 'Task unarchived successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while unarchiving the task.',
            ]);
        }
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        try {
            DB::beginTransaction();

            // Log task deletion
            activity_log('task_deleted', [
                'task_id' => $task->id,
                'deleted_by' => $request->user()->id,
            ]);

            // Delete task
            $task->delete();

            DB::commit();

            return redirect()->route('tasks.index')
                ->with('status', 'Task deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while deleting the task.',
            ]);
        }
    }
}
