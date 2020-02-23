<?php

$exec = true;
//$exec = false;

include("excell.inc");
include("../../hpfmaster.inc");

function getID($handle,$code)
{
	$query = sprintf("select * from brand where vf=true and code='%s'",$code);
	$sr = pg_query($handle,$query);
	$so = pg_fetch_array($sr);
	return($so['id']);
}

if($handle=pg_connect($pgconnect)){
	pg_query($handle,"begin");
//	printf("---- FILE %s\n",$argv[1]);
	if($fp=fopen($argv[1],"r")){
//
	    fgetcsv($fp,1024,"\t");
	    fgetcsv($fp,1024,"\t");
//
	    for($a=0; $line=fgetcsv($fp,1024,"\t"); $a++){
		$code = $line[ROW_E];
		$ptS = $line[ROW_AE];
		$ptB = $line[ROW_W];
		if($id = getID($handle,$code)){
		    $query = sprintf("update brand set payterm='{%d,%d}' where id='%d'",$ptS,$ptB,$id);
		    printf("%s;\n",$query);
		}
	    }
	    fclose($fp);
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
