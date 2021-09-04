<?php 
namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class VerifyMail extends Mailable
{
    use Queueable, SerializesModels;
     
    /**
     * The demo object instance.
     *
     * @var Demo
     */
    public $verifymail;
 
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verifymail)
    {
        $this->verifymail = $verifymail;
    }
 
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('info@hainaservice.com')
                    ->view('mails.verify');
                    
    }
}