<?php
include("hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	pg_query($handle,"begin");
	$query = $_REQUEST['query'];
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	$ooo = array();
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_row($qr,$a);
		$ooo[] = implode(",",$qo);
	}
	printf("%s",implode("\n",$ooo));
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
