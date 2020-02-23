<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){

function getPyramid($handle,$id)
{
  $ooo = array();
  for($loop=true; $loop; ){
	$query = sprintf("select * from division where id='%d'",$id);
	$qr = pg_query($handle,$query);
	$qo = pg_fetch_array($qr);
	$ooo[] = array('id'=>$qo['id'],'name'=>$qo['name']);
	if($parent = $qo['parent']){
	  $id=$parent;
	}
	else break;
  }
  return(array_reverse($ooo));
}

function getName($handle,$id)
  {
  $name = array();
  for($loop=true; $loop; ){
	$query = sprintf("select * from division where id='%d'",$id);
	$qr = pg_query($handle,$query);
	$qo = pg_fetch_array($qr);
	$name[] = $qo['name'];
	if($parent = $qo['parent']){
	  $id=$parent;
	}
	else break;
  }
  return(implode(" > ",array_reverse($name)));
}
 $name = getName($handle,20);
 printf("name = [%s]\n",$name);
  pg_close($handle);
}
?>
