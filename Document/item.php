<?php
  $debug = true;
//$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//
    $query = sprintf("select * from category where vf=true");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    $ooo = array();
    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);
	$query = sprintf("select count(*) from brand where '%d'=any(category)",$qo['id']);
	$br = pg_query($handle,$query);
	$bo = pg_fetch_array($br);
	if($bo['count']){
	    printf("%s -> %d\n",$qo['name'],$bo['count']);
	}
	else{
	    $query = sprintf("update category set vf=false where id='%d'",$qo['id']);
	    $ur = pg_query($handle,$query);
	}
    }
//
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
