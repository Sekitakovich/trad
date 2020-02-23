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
	if($division = $qo['division']){
	    $ooo = array($division);
	    $ppp = getPGSQLarray($qo['dset']);
printf("ooo=[%s] ppp=[%s]\n",implode(",",$ooo),implode(",",$ppp));
	    $dset = array_merge($ooo,$ppp);
	    $query = sprintf("update staff set division=0,dset='{%s}' where id='%d'",implode(",",$dset),$qo['id']);
	    printf("%s\n",$query);

	    $ur = pg_query($handle,$query);
	    if($ur){
	    }
	    else break;

	}
    }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
