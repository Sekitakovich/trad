<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//
function makeFamily($handle,$table,$parent,$level)
{
    $tree = array();
    $query = sprintf("select * from %s where vf=true and parent='%d' order by weight desc",$table,$parent);
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);
	$tree=array_merge($tree,makeFamily($handle,$table,$qo['id'],$level+1));
    }
    if($parent) $tree[] = $parent;
    printf("Level %d: (%s)\n",$level,implode(",",$tree));
    return($tree);
}
//

    $tree=makeFamily($handle,'division',0,0);
    printf("final (%s)\n",implode(",",$tree));
    for($a=0,$b=count($tree); $b--; $a++){
	printf("%02d: %s\n",$tree[$a],getDivisionName($handle,$tree[$a]));
    }
    pg_query($handle,"commit");
  pg_close($handle);
}
?>
