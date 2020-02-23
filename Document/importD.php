<?php

//$debug = true;
$debug = false;

/*
 0:店舗ID
 1:日付
 2:昨年実績
 3:日割予算
 4:売上実績
 5:取置計
 6:顧客計
 7:客数計
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
	$lbase = $line[2];
	  $last = $lbase*$rate;
	$tbase = $line[3];
	  $target = $tbase*$rate;
	$rbase = $line[4];
	  $result = $rbase*$rate;
	$bbase = $line[5];
	  $book = $bbase*$rate;
	$member = $line[6];
//	$visitor = $line[7]-$member;
	$visitor = $line[7];
	
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
	$query = sprintf("update daily set lbase=%f,tbase=%f,rbase=%f,bbase=%f,last=%d,target=%d,result=%d,book=%d,member=%d,visitor=%d where id=%d",
			 $lbase,$tbase,$rbase,$bbase,$last,$target,$result,$book,$member,$visitor,$id);
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
