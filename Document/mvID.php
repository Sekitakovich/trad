<?php

//$debug = true;
$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
    $query = sprintf("select max(id) from staff");
    $qr = pg_query($handle,$query);
    $qo = pg_fetch_array($qr,$a);
    $id = $qo['max']+1;

    $query = sprintf("select * from shop where vf=true order by id");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++,$id++){
	$qo = pg_fetch_array($qr,$a);
	$query = sprintf("insert into staff(id,udate,ustaff) values('%d','%s','%d')",$id,$qo['udate'],$qo['ustaff']);
	$ur = pg_query($handle,$query);
	$query = sprintf("update staff set shop='%d',division='%d',aset='{%d}',account='%s',password='%s',nickname='%s',name[1]='%s',name[2]='%s',kana[1]='%s',kana[2]='%s' where id='%d'",
			 $qo['id'],$qo['division'],$qo['area'],$qo['account'],$qo['password'],$qo['account'],$qo['account'],$qo['account'],$qo['account'],$qo['account'],$id);
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
