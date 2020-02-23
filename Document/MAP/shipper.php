<?php

$exec = true;
//$exec = false;

include("excell.inc");
include("../../hpfmaster.inc");

function getNation($handle,$code)
{
    $query = sprintf("select * from nation where vf=true and code3='%s'",$code);
    $sr = pg_query($handle,$query);
    $so = pg_fetch_array($sr);
    return($so['id']);
}

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
	    $idM = 1000;
	    $shipper = array();
	    for($a=0; $line=fgetcsv($fp,1024,"\t"); $a++){
		$code = $line[ROW_E]; // 取り込み用ブランドコードだそうで
		if($id = getID($handle,$code)){
		    $name = $line[ROW_R];
		    if($name){ // shipperが明記されていれば
			$nation = getNation($handle,$line[ROW_T]);
			$found = false;
			for($b=0,$c=count($shipper); $c--; $b++){
			    if($shipper[$b]['name']==$name){ // found!
				$sid = $shipper[$b]['id'];
				$found = true;
				break;
			    }
			}
			if($found){
//			    printf("Found!\n");
			}
			else{
			    $shipper[] = array('id'=>$idM,'name'=>$name,'nation'=>$nation);
			    $sid = $idM;
			    $idM++;
			}
			$query = sprintf("update brand set shipper[1]='%d' where id='%d'",$sid,$id);
			printf("%s;\n",$query);
		    }
		}
	    }
	    for($a=0,$b=count($shipper); $b--; $a++){
		$query = sprintf("insert into maker(id,name,nation,attribute) values('%d','%s','%d','2')",
				 $shipper[$a]['id'],
				 pg_escape_string($shipper[$a]['name']),
				 $shipper[$a]['nation']
				 );
		printf("%s;\n",$query);
	    }
	    fclose($fp);
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
