<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class TeamInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $team;
    protected $invitation;

    public function __construct(Team $team, TeamInvitationModel $invitation)
    {
        $this->team = $team;
        $this->invitation = $invitation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $acceptUrl = URL::signedRoute('team-invitations.accept', [
            'invitation' => $this->invitation->id,
            'token' => $this->invitation->token,
        ]);

        return (new MailMessage)
            ->subject("Invitation to join {$this->team->name}")
            ->markdown('emails.teams.invitation', [
                'team' => $this->team,
                'invitation' => $this->invitation,
                'acceptUrl' => $acceptUrl,
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'invited_by' => $this->invitation->invited_by,
            'role' => $this->invitation->role,
            'expires_at' => $this->invitation->expires_at->format('Y-m-d H:i:s'),
            'type' => 'team_invitation',
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'invited_by' => $this->invitation->invited_by,
            'role' => $this->invitation->role,
        ];
    }
}
