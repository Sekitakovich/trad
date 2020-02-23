<?php
include("../hpfmaster.inc");

//
// $parent以下のarea.idを再帰的に列挙する(注:$parent自身は配列に含まれない)
//
function areaTree($handle,$parent)
{
    $tree = array($parent);
    $tree = array();
    $query = sprintf("select * from area where vf=true and parent='%d'",$parent);
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);
	$id = $qo['id'];
	$tree[] = $id;
	$tree = array_merge($tree,areaTree($handle,$id));
    }
    return($tree);
}

if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//
    $tree=areaTree($handle,8);
    $tree[] = 8;
    var_dump($tree);
//
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
