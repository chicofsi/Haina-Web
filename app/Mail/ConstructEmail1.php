<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConstructEmail1 extends Mailable
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
      return $this->subject('Verify Email Address')
                  ->from('info@hainaservice.com', 'Haina Security Team')
                  ->view('email.template1')
                  ->with([
                    'data' => $this->data
                  ]);
      /*
      // original from Larael
      return $this->view('view.name');
      */
    }
}
