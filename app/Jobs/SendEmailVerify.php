<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\NewMail;
use Illuminate\Support\Facades\Mail;
// use Mail;

class SendEmailVerify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    public $mail;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details,$mail)
    {
        $this->details = $details;
        $this->mail = $mail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // dd($this->details);
        $email = new NewMail($this->mail);
        Mail::to($this->details)->send($email);
    }
}
