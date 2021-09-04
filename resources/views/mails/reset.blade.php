Dear {{ $resetmail->receiver }},
<p>We received a password reset request for account registered to this address. You can reset your password for Haina App by clicking on the button below:
</p>

<a href="/">
    <button type="button" class="reset_pass">Reset My Password</button>
</a>

<p>If you did not reset your password, you can simply ignore this email.</p>

Best Regards,
<br/>
<br/>
<i>{{ $resetmail->sender }}</i>