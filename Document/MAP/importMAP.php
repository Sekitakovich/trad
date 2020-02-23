<?php

$exec = true;
//$exec = false;

include("excell.inc");
include("../../hpfmaster.inc");

function getCategory($handle,$code)
{
    $ppp = array();
    $qqq = array();
    $ooo = explode("/",$code);
    for($a=0,$b=count($ooo); $b--; $a++){
		$ppp[] = sprintf("'%s'",$ooo[$a]);
    }
    $query = sprintf("select id from category where vf=true and name in (%s)",implode(",",$ppp));
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$qqq[] = $qo['id'];
    }
    return($qqq);
}

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

function getCurrency($handle,$code)
{
    $query = sprintf("select * from currency where vf=true and code='%s'",$code);
    $sr = pg_query($handle,$query);
    $so = pg_fetch_array($sr);
    return($so['id']);
}

function getStaff($handle,$code)
{
    $query = sprintf("select * from staff where vf=true and account='%s'",$code);
    $sr = pg_query($handle,$query);
    $so = pg_fetch_array($sr);
    return($so['id']);
}

if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
    printf("---- FILE %s\n",$argv[1]);
  if($fp=fopen($argv[1],"r")){
//
      fgetcsv($fp,1024,"\t");
      fgetcsv($fp,1024,"\t");
//
      for($a=0; $line=fgetcsv($fp,1024,"\t"); $a++){
		  $code = $line[ROW_E]; // 取り込み用ブランドコードだそうで
		  if($id = getID($handle,$code)){
			  $nation = getNation($handle,$line[ROW_K]);
			  $currency = getCurrency($handle,$line[ROW_U]);
			  $category = getCategory($handle,$line[ROW_AH]);
			  $clerk = getStaff($handle,$line[ROW_AP]);
	
			  $set = array();
	
			  $set[] = sprintf("vf=true");
			  $set[] = sprintf("nation='%d'",$nation);
			  $set[] = sprintf("currency='%d'",$currency);
			  $set[] = sprintf("clerk[1]='%d'",$clerk);
			  $set[] = sprintf("nickname='%s'",pg_escape_string($line[ROW_C]));
			  $set[] = sprintf("name='%s'",pg_escape_string($line[ROW_F]));
			  $set[] = sprintf("address='%s'",pg_escape_string($line[ROW_G]));
			  $set[] = sprintf("callme[1]='%s'",pg_escape_string($line[ROW_L]));
			  $set[] = sprintf("callme[2]='%s'",pg_escape_string($line[ROW_M]));
			  $set[] = sprintf("callme[3]='%s'",pg_escape_string($line[ROW_N]));
			  $set[] = sprintf("pic='%s'",pg_escape_string($line[ROW_O]));
			  $set[] = sprintf("mail[1]='%s'",pg_escape_string($line[ROW_P]));
			  $set[] = sprintf("pnote[1]='%s'",pg_escape_string($line[ROW_AD]));
			  $set[] = sprintf("pnote[2]='%s'",pg_escape_string($line[ROW_V]));
	
			  for($b=0,$c=count($category); $c--; $b++){
				  $set[] = sprintf("category[%d]='%d'",$b+1,$category[$b]);
			  }
			  
			  $query = sprintf("update brand set %s where id='%d'",implode(",",$set),$id);
			  printf("%s\n",$query);
			  if($exec){
				  $qr = pg_query($handle,$query);
				  if($qr){
				  }
				  else{
					  printf("Query failed\n");
					  break;
				  }
			  }
		  }
		  else{
			  printf("Not found %d\n",$code);
			  break;
		  }
      }
      fclose($fp);
  }
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
