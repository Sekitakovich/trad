<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>jscalendar - sample</title>
<link href="jscalendar/calendar-green.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript" src="jscalendar/calendar.js"></script>
<script type="text/javascript" src="jscalendar/calendar-setup.js"></script>
<script type="text/javascript" src="jscalendar/lang/calendar-jp-utf8.js"></script>
sample
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit">
		<label><input name="ps" type="text" id="ps" value="YYYY-MM-DD" size="32" maxlength="32" readonly="true" /></label>
～		
<label><input name="pe" type="text" id="pe" value="YYYY-MM-DD" size="32" maxlength="32" /></label>
<input name="popup" type="button" id="popup" value="click!" />
</form>
<script type="text/javascript">
window.onload = function()
{
	Calendar.setup({inputField:"ps",ifFormat:"%Y-%m-%d",button:"ps",eventName:"mouseover"});
	Calendar.setup({inputField:"pe",ifFormat:"%Y-%m-%d",button:"pe"});
}
</script>
</body>
</html>
