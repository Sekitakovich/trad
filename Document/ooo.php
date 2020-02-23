<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//
    $query = sprintf("select * from staff where vf=true and division=any(dset)");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);
	$id = $qo['id'];
	$division = $qo['division'];
	$dset = getPGSQLarray($qo['dset']);
	$nset = array();
	for($b=0; $b<count($dset); $b++){
	    if($dset[$b]!=$division){
		$nset[] = $dset[$b];
	    }
	}
	$__dset = implode(",",$dset);
	$__nset = implode(",",$nset);
	$query = sprintf("update staff set dset='{%s}' where id=%d",$__nset,$id);
	$ur = pg_query($handle,$query);
	if($ur){
	    printf("Query(%d) = [%s]\n",$ur,$query);
	}
	else break;
    }
//
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
