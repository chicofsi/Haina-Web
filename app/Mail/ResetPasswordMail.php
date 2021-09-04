<?php 
namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
     
    /**
     * The demo object instance.
     *
     * @var Demo
     */
    public $resetmail;
 
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($resetmail)
    {
        $this->resetmail = $resetmail;
    }
 
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('info@hainaservice.com')
                    ->view('mails.reset');
                    
    }
}