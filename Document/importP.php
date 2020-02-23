<?php

//$debug = true;
$debug = false;

/*
 0:事業部ID
 1:ブランドコード
 2:通貨
 3:オーダー番号
 4:発注年月日
 5:数量
 6:金額
 7:シーズンID
*/
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
printf("---- FILE %s\n",$argv[$aa]);
  if($fp=fopen($argv[1],"r")){
      fgetcsv($fp);
      $id = 1000;
      for($a=0; $line=fgetcsv($fp); $a++,$id++){
	printf("CSV[%06d] = (%s)\n",$a,implode(",",$line));
	  $did = $line[0];
	  $bcd = $line[1];
	  $ono = $line[3];
	  $day = $line[4];
	  $cnt = $line[5];
	  $mny = $line[6];
	  $sss = $line[7];
	  
	  $query = sprintf("select brand.* from brand where code='%s'",$bcd);
	  $qr = pg_query($handle,$query);
	  $qo = pg_fetch_array($qr);
	  $bid = $qo['id'];

	  $query = sprintf("insert into purchase(id,ustaff) values(%d,%d)",$id,4);
	  $qr = pg_query($handle,$query);
	  
	  $set = array();
	  $set[] = sprintf("division=%d",$did);
	  $set[] = sprintf("brand=%d",$bid);
	  $set[] = sprintf("number='%s'",$ono);
	  $set[] = sprintf("pdate='%s'",$day);
	  $set[] = sprintf("volume=%d",$cnt);
	  $set[] = sprintf("amount=%f",$mny);
	  $set[] = sprintf("season=%d",$sss);
	  $query = sprintf("update purchase set %s where id=%d",implode(",",$set),$id);
	  printf("Query = [%s]\n",$query);
	  $qr = pg_query($handle,$query);
	  if($qr){
	  }
	  else break;
      }
    fclose($fp);
  }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
