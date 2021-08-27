Hello <i>{{ $demo->receiver }}</i>,
<p>We received your application for our company and we are pleased to invite you 
    for an interview at:
</p>
 
<div>
<p><b>Location:</b>&nbsp;{{ $demo->demo_one }}</p>
<p><b>Time:</b>&nbsp;{{ $demo->demo_two }}</p>
</div>
 
<p><u>Values passed by With method:</u></p>
 
<div>
<p><b>testVarOne:</b>&nbsp;{{ $testVarOne }}</p>
<p><b>testVarTwo:</b>&nbsp;{{ $testVarTwo }}</p>
</div>
 
Thank You,
<br/>
<i>{{ $demo->sender }}</i>