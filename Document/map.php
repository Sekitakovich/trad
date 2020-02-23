<?php
/*
   "ID                ",
   "Last Season       ",
   "up                ",
   "略称              ",
   "ABA               ",
   "CODE              ",
   "Brand             ",
   "Company           ",
   "Theatre           ",
   "HPBQ              ",
   "Shipper           ",
   "Address           ",
   "Division 6        ",
   "Country (brand)   ",
   "Division 5        ",
   "Country (shipper) ",
   "Tel               ",
   "Wut               ",
   "Fax               ",
   "Mobile            ",
   "PIC               ",
   "email             ",
   "Contact           ",
   "STS               ",
   "Laichi            ",
   "Division 1        ",
   "Division 2        ",
   "Division 3        ",
   "Division 4        ",
   "Kioku             ",
   "W                 ",
   "goldie            ",
   "bijoux            ",
   "fec               ",
   "ex                ",
   "concento          ",
   "mican             ",
   "lamp              ",
   "Denim             ",
   "usagi             ",
   "hph               ",
   "antenna           ",
   "deco              ",
   "Cannabis          ",
   "Factory           ",
   "dny               ",
   "Suikin            ",
   "ITEM              ",
   "Payment(Bulk)     ",
   "Disc(Bulk)        ",
   "Payment(Sample)   ",
   "hp tel            ",
   "hp e-mail         ",
   "hp fax            ",
   "O-Jewel           ",
   "Disc(Sample)      ",
*/
  $debug = true;
//$debug = false;

include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//
    $query = sprintf("select * from map where \"Brand\"<>''");
    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);
    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);
// maker link
	$query = sprintf("select * from maker where name='%s'",pg_escape_string($qo['Company']));
	$mr = pg_query($handle,$query);
	$mo = pg_fetch_array($mr);

	$id = $qo['ID'];
	$name = $qo['Brand'];
	$nickname = $qo['略称'];
	$code = $qo['CODE'];
	$pic = $qo['PIC'];
	$tel = $qo['Tel'];
	$fax = $qo['Fax'];
	$mobile = $qo['Mobile'];
	$email = $qo['email'];
	$maker = $mo['id'];

//	printf("%03d: %s [%s:%s] %s:%s:%s:%s) -> %d\n",$id,$name,$nickname,$code,$pic,$tel,$fax,$mobile,$maker);
	$query = sprintf("insert into brand(id,name,nickname,code,pic,callme[1],callme[2],callme[3],mail[1],maker) values('%d','%s','%s','%s','%s','%s','%s','%s','%s','%d')",
			 $id,
			 pg_escape_string($name),
			 pg_escape_string($nickname),
			 pg_escape_string($code),
			 pg_escape_string($pic),
			 $tel,$fax,$mobile,
			 $email,
			 $maker
			 );
	printf("%s\n",$query);
	$ir = pg_query($handle,$query);
	if($ir){
	}
	else{
	    break;
	}
    }
//
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
