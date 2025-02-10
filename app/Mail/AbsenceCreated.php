<?php

namespace App\Mail;

use App\Models\Absence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbsenceCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $absence;

    /**
     * Create a new message instance.
     */
    public function __construct(Absence $absence)
    {
        $this->absence = $absence;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        if (!$this->absence) {
            throw new \Exception('Absence model is null in AbsenceCreated Mailable.');
        }

        return $this->subject('New Absence Created')
                    ->view('emails.absence_created') // This is the correct path
                    ->with([
                        'absence' => $this->absence,
                    ]);
    }

    
}

