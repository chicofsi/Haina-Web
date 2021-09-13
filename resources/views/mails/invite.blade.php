Dear {{ $demo->receiver }},
<p>{{ $demo->sender }} received your application for <b>{{ $demo->position }}</b> in their company and we are pleased to inform that you are invited 
    for an interview.
</p>
 
@if ($demo->method == "live")
    <div>
        <p><b>Method:</b>&nbsp;On-site Interview</p>
        <p><b>Location:</b>&nbsp;{{ $demo->location }}</p>
        <p><b>Time:</b>&nbsp;{{ $demo->time }}</p>
        <p><b>Contact Person:</b>&nbsp;{{ $demo->cp_name }} ({{ $demo->cp_phone }})</p>
        <p><b>Time:</b>&nbsp;{{ date('l, j F Y - H:i', strtotime($demo->time)) }} WIB</p>
    </div>
@elseif ($demo->method == "phone")
    <div>
        <p><b>Method:</b>&nbsp;Phone Interview</p>
        <p><b>Time:</b>&nbsp;{{ date('l, j F Y - H:i', strtotime($demo->time)) }} WIB</p>
        <p><b>Duration:</b>&nbsp;{{ $demo->duration }} minutes</p>
    </div>
@else
    <div>
        <p><b>Method:</b>&nbsp;Online Interview</p>
        <p><b>Time:</b>{{ date('l, j F Y - H:i', strtotime($demo->time)) }} WIB</p>
        <p><b>Link:</b>&nbsp;{{ $demo->location }}</p>
        <p><b>Duration:</b>&nbsp;{{ $demo->duration }} minutes</p>
    </div>
@endif

<p>We look forward to talk with you in this interview. Please do not hesitate to ask if you have any questions regarding the interview process.</p>

 
Thank You,
<br/>
<br/>
<i>{{ $demo->sender }}</i>