<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail_ResetPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data) {
      $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
      return $this->subject('Reset Password Request')
                  ->from('info@hainaservice.com', 'Haina App Team')
                  ->view('mails.reset-password')
                  ->with([
                    'data' => $this->data
                  ]);
      /*
      // original from Larael
      return $this->view('view.name');
      */
    }
}
