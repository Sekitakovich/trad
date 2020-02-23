<?php

$debug = true;
//$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
    $query = sprintf("select * from map");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);
	if($qo['Brand']){
	    $id = $qo['ID'];
	    $ps = $qo['Payment(Sample)'];
	    $pb = $qo['Payment(Bulk)'];
	    $ds = $qo['Disc(Sample)'];
	    $db = $qo['Disc(Bulk)'];
	    if($ps){
		$query = sprintf("update brand set pnote[1]='%s' where id='%d'",pg_escape_string($ps),$id);
		printf("%s\n",$query);
		$ur = pg_query($handle,$query);
		if($ur){
		}
		else break;
	    }
	    if($pb){
		$query = sprintf("update brand set pnote[2]='%s' where id='%d'",pg_escape_string($pb),$id);
		printf("%s\n",$query);
		$ur = pg_query($handle,$query);
		if($ur){
		}
		else break;
	    }
	    if($ds){
		$query = sprintf("update brand set dnote[1]='%s' where id='%d'",pg_escape_string($ds),$id);
		printf("%s\n",$query);
		$ur = pg_query($handle,$query);
		if($ur){
		}
		else break;
	    }
	    if($db){
		$query = sprintf("update brand set dnote[2]='%s' where id='%d'",pg_escape_string($db),$id);
		printf("%s\n",$query);
		$ur = pg_query($handle,$query);
		if($ur){
		}
		else break;
	    }
	}
    }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
