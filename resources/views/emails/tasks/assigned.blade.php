@component('mail::message')
# Task Assigned: {{ $task->title }}

You have been assigned a new task in {{ $task->team->name }}.

**Task Details:**
- Priority: {{ ucfirst($task->priority) }}
- Status: {{ ucfirst($task->status) }}
- Due Date: {{ $task->due_date ? $task->due_date->format('M j, Y') : 'No due date' }}

@component('mail::panel')
{{ $task->description }}
@endcomponent

@component('mail::button', ['url' => route('tasks.show', $task)])
View Task
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
