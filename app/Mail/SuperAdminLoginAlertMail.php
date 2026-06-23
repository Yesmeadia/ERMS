<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuperAdminLoginAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $ip;
    public $userAgent;
    public $time;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $ip, string $userAgent, string $time)
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->time = $time;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Super Admin Login Detected - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.super_admin_login_alert',
        );
    }
}
