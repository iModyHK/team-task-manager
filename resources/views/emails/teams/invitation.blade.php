@component('mail::message')
# Team Invitation

You have been invited to join **{{ $team->name }}** on {{ config('app.name') }}.

@if($invitation->role)
You will be joining as a **{{ $invitation->role }}**.
@endif

@component('mail::button', ['url' => $acceptUrl])
Accept Invitation
@endcomponent

This invitation will expire in {{ config('auth.invitation_expires_in', 7) }} days.

If you did not expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
