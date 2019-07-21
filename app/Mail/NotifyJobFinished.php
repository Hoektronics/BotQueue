<?php

namespace App\Mail;

use App\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyJobFinished extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var Job
     */
    private $job;

    /**
     * Create a new message instance.
     *
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject("Job #{$this->job->id} is done!")
            ->view('emails.jobs.finished', [
                'job' => $this->job,
            ]);
    }
}
