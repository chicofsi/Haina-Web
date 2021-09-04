Dear {{ $verifymail->receiver }},
<p>Thank you for registering to Haina App. To be able to use all of our services, please verify your email by clicking on the button below:
</p>

<a href="/">
    <button type="button" class="verify_email">Verify Email</button>
</a>

<p>If you did not register to our system, please contact us to have your account terminated.</p>

Best Regards,
<br/>
<br/>
<i>{{ $verifymail->sender }}</i>