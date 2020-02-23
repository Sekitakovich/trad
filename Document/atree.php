<?php

//$debug = true;
$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
    $query = sprintf("select * from area where vf=true order by id");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++,$id++){
	$qo = pg_fetch_array($qr,$a);
	$tree = AreaTree($handle,$qo['id']);
	$tree[] = $qo['id'];
	printf("%03d (%s): id=[%d] -> [%s]\n",$a,$qo['name'],$qo['id'],implode(",",$tree));
    }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
