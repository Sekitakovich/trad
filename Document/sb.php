<?php

$debug = true;
//$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//
    $query = sprintf("select id,name,brand from shop where vf=true order by id");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++,$id++){
	$qo = pg_fetch_array($qr,$a);
	$sid = $qo['id'];
	$bset = getPGSQLarray($qo['brand']);
	for($b=0,$c=count($bset); $c--; $b++){
	    $bid = $bset[$b];
	    $query = sprintf("select * from brand where id='%d'",$bid);
	    printf("Query = [%s]\n",$query);
	    $br = pg_query($handle,$query);
	    $bo = pg_fetch_array($br);
	    $shop = getPGSQLarray($bo['shop']);
	    $shop[] = $sid;
	    $query = sprintf("update brand set shop='{%s}' where id='%d'",implode(",",$shop),$bid);
	    $br = pg_query($handle,$query);
	    printf("Query = [%s]\n",$query);
	}
    }
//
    pg_query($handle,"commit");
  pg_close($handle);
}
?>
