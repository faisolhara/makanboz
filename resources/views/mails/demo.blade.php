<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	Hello <i>{{ $demo->receiver }}</i>,
	<p>This is a demo email for testing purposes! Also, it's the HTML version.</p>
	 
	<p><u>Demo object values:</u></p>
	<p><u><a href="www.google.com">Link</a></u></p>
	 
	<div>
	<p><b>Demo One:</b>&nbsp;{{ $demo->demo_one }}</p>
	<p><b>Demo Two:</b>&nbsp;{{ $demo->demo_two }}</p>
	</div>
	 
	<p><u>Values passed by With method:</u></p>
	 
	<div>
	<p><b>testVarOne:</b>&nbsp;{{ $testVarOne }}</p>
	<p><b>testVarTwo:</b>&nbsp;{{ $testVarTwo }}</p>
	</div>
	 
	Thank You,
	<br/>
	<i>{{ $demo->sender }}</i>

</body>
</html>
