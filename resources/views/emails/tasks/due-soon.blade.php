@component('mail::message')
# Task Due Soon: {{ $task->title }}

This is a reminder that you have a task due {{ $task->due_date->diffForHumans() }}.

**Task Details:**
- Priority: {{ ucfirst($task->priority) }}
- Status: {{ ucfirst($task->status) }}
- Due Date: {{ $task->due_date->format('M j, Y') }}

@if($task->description)
@component('mail::panel')
{{ $task->description }}
@endcomponent
@endif

@component('mail::button', ['url' => route('tasks.show', $task)])
View Task
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
