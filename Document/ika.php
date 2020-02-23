<?php

$debug = true;
$debug = false;

/*
 0:店舗ID
 1:日付
 2:昨年実績
 3:日割予算
*/
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
    $loop = true;
    for($aa=1; $aa<=$argc && $loop; $aa++){
printf("---- FILE %s\n",$argv[$aa]);
  if($fp=fopen($argv[$aa],"r")){
      fgetcsv($fp); 
      $shop = 0;
      $rate = 0.0;
      for($a=0; $line=fgetcsv($fp); $a++){
//	printf("CSV[%06d] = (%s)\n",$a,implode(",",$line));
	$sid = $line[0];
	  if($sid!=$shop){
	      $shop=$sid;
	      $query = sprintf("select currency.rate from shop,currency where shop.id='%d' and shop.currency=currency.id",$shop);
	      $qr = pg_query($handle,$query);
	      $qo = pg_fetch_array($qr);
	      $rate = $qo['rate'];
	      if($debug) printf("Shop(%d) rate=%f",$shop,$rate);
	  }
	$yyyymmdd = $line[1];
	$rbase = $line[2];
	  $result = $rbase*$rate;
	$bbase = $line[3];
	  $book = $bbase*$rate;
	$member = $line[4];
	  $visitor = $line[5];
	$query = sprintf("select id from daily where vf=true and shop='%d' and yyyymmdd='%s'",$shop,$yyyymmdd);
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	if($qs){
	    $qo = pg_fetch_array($qr);
	    $id = $qo['id'];
	}
	else{
	    $query = sprintf("select max(id) from daily");
	    $qr = pg_query($handle,$query);
	    $qo = pg_fetch_array($qr);
	    $id = $qo['max']+1;
	    $query = sprintf("insert into daily(id,shop,yyyymmdd,entered) values('%d','%d','%s',true)",$id,$shop,$yyyymmdd);
	    $qr = pg_query($handle,$query);
	}
	$query = sprintf("update daily set rbase=%f,bbase=%f,result=%d,book=%d,member='%d',visitor='%d' where id=%d",
			 $rbase,$bbase,$result,$book,$member,$visitor,$id);
	  if($debug==false){
	      $qr = pg_query($handle,$query);
	  }
	  else $qr = 1;
	printf("Query(%d) = [%s]\n",$qr,$query);
	if($qr==0){
	    $loop=false;
	    break;
	}
    }
    fclose($fp);
  }
    }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
