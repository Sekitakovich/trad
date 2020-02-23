<?php

//$debug = true;
$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
    $query = sprintf("select * from staff where vf=true order by id");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++,$id++){
	$qo = pg_fetch_array($qr,$a);
	$query = sprintf("update staff set md5='%s' where id='%d'",md5($qo['password']),$qo['id']);
	printf("%s\n",$query);
	$ur = pg_query($handle,$query);
	if($ur){
	}
	else break;
    }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
