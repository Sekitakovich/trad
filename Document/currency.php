<?php

$debug = true;
//$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");

    if($fp = fopen("a","r")){
	$query = sprintf("select max(id) from currency");
	$qr = pg_query($handle,$query);
	$qo = pg_fetch_array($qr);
	$id = $qo['max']+1;

	$code = array();

	for($a=0; $csv=fgetcsv($fp,1024,",","'"); $a++){
/*
	    $size = count($csv);
	    for($b=0,$c=$size; $c--; $b++){
		$csv[$b] = sprintf("[%s]",$csv[$b]);
	    }
	    printf("%03d(%d) = %s\n",$a,$size,implode("+",$csv));
*/
	    if(in_array($csv[3],$code)){
		printf("%03d: Skip!\n",$a);
	    }
	    else{
		$query = sprintf("insert into currency(id,remark,denomination,code,number,name) values('%d','%s','%s','%s','%d','%s')",
			     $id++,
			     pg_escape_string($csv[1]),
			     pg_escape_string($csv[2]),
			     pg_escape_string($csv[3]),
			     pg_escape_string($csv[4]),
			     pg_escape_string($csv[2])
			     );
		$code[] = $csv[3];
		printf("Query = [%s]\n",$query);
		$ur = pg_query($handle,$query);
		if($ur){
		}
		else{
		    break;
		}
	    }
	}
	fclose($fp);
    }
  
    
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
