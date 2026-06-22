<?php

namespace App\Mail;

use App\Models\School;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SchoolCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $school;
    public $user;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct(School $school, User $user, string $password)
    {
        $this->school = $school;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'School Admin Account Created - ' . $this->school->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.school_created',
        );
    }
}
