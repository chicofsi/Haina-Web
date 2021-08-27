Dear, {{ $demo->receiver }},
<p>We received your application for {{ $demo->position }} in our company and we are pleased to invite you 
    for an interview at:
</p>
 
@if ($demo->method == "live")
    <div>
        <p><b>Method:</b>&nbsp;{{ $demo->method }}</p>
        <p><b>Location:</b>&nbsp;{{ $demo->location }}</p>
        <p><b>Time:</b>&nbsp;{{ $demo->time }}</p>
        <p><b>Contact Person:</b>&nbsp;{{ $demo->cp_name }} ({{ $demo->cp_phone }})</p>
        <p><b>Time:</b>&nbsp;{{ $demo->time }} minutes</p>
    </div>
@elseif ($demo->method == "phone")
    <div>
        <p><b>Method:</b>&nbsp;{{ $demo->method }}</p>
        <p><b>Time:</b>&nbsp;{{ $demo->time }}</p>
        <p><b>Duration:</b>&nbsp;{{ $demo->duration }}</p>
    </div>
@else
    <div>
        <p><b>Method:</b>&nbsp;{{ $demo->method }}</p>
        <p><b>Time:</b>&nbsp;{{ $demo->time }}</p>
        <p><b>Link:</b>&nbsp;{{ $demo->location }}</p>
        <p><b>Duration:</b>&nbsp;{{ $demo->duration }} minutes</p>
    </div>
@endif

<p>We look forward to talk with you in this interview. Please do not hesitate to ask if you have any questions regarding the interview process.</p>

 
Thank You,
<br/>
<br/>
<i>{{ $demo->sender }}</i>