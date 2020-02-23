<?php
  $debug = true;
//$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//
    $query = sprintf("select id,pnote[1] as p1,pnote[2] as p2 from brand where vf=true");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);

    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);
	$id = $qo['id'];
	$p1 = str_replace("\n","",$qo['p1']);
	$p2 = str_replace("\n","",$qo['p2']);
	$p1 = pg_escape_string($p1);
	$p2 = pg_escape_string($p2);
	$query = sprintf("update brand set pnote[1]='%s',pnote[2]='%s' where id='%d'",$p1,$p2,$id);
	printf("Query = [%s]\n",$query);
	$ur = pg_query($handle,$query);
	if($ur){
	}
	else break;
    }
//
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
