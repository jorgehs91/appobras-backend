@component('mail::message')
@if($alertType === 'overdue')
# {{ __('alerts.overdue.title') }}

{{ __('alerts.overdue.message', ['name' => $user->name]) }}

@component('mail::table')
| Tarefa | Projeto | Data de Vencimento |
|:-------|:--------|:-------------------|
@foreach($overdueTasks as $task)
| **{{ $task->title }}** | {{ $task->project?->name ?? 'N/A' }} | {{ $task->due_at?->format('d/m/Y') ?? 'N/A' }} |
@endforeach
@endcomponent

@else
# {{ __('alerts.near_due.title') }}

{{ __('alerts.near_due.message', ['name' => $user->name]) }}

@component('mail::table')
| Tarefa | Projeto | Data de Vencimento |
|:-------|:--------|:-------------------|
@foreach($nearDueTasks as $task)
| **{{ $task->title }}** | {{ $task->project?->name ?? 'N/A' }} | {{ $task->due_at?->format('d/m/Y') ?? 'N/A' }} |
@endforeach
@endcomponent
@endif

@component('mail::button', ['url' => $tasksUrl])
{{ __('alerts.view_tasks') }}
@endcomponent

{{ __('alerts.footer') }}

Obrigado,<br>
{{ config('app.name') }}
@endcomponent

