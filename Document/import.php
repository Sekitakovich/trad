<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
  if($fp=fopen("200707.csv","r")){
    $id = 1;
    for($a=0; $line=fgetcsv($fp); $a++,$id++){
      $query = sprintf("insert into daily(id,shop,yyyymmdd,result) values('%d','%d','%s','%d')",$id,$line[0],$line[1],$line[2]);
      $qr = pg_query($handle,$query);
      printf("Query(%d)] = %s\n",$qr,$query);
      if($qr==0) break;
    }
    fclose($fp);
  }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
