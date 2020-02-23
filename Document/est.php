<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
  pg_query($handle,"begin");
//-----------------------------------------------------------------------------

    function __ymSort($a,$b)
    {
	if($a['Y']==$b['Y']){
	    return($a['M']-$b['M']);
	}
	else{
	    return($a['Y']-$b['Y']);
	}
    }
    
    $qq = array();

    $qq[] = sprintf("SELECT purchase.pdate,season.year,stype.nickname,brand.name as bname,purchase.amount,currency.rate,currency.code as ccode,estimate.pays,estimate.yoffset,estimate.month,estimate.percentage");
    $qq[] = sprintf("from purchase join (season join stype on season.stype=stype.id) on purchase.season=season.id join (belink join (brand join currency on brand.currency=currency.id) on belink.brand=brand.id join estimate on belink.estimate=estimate.id) on purchase.brand=belink.brand");
    $qq[] = sprintf("where purchase.vf=true");
    $qq[] = sprintf("and estimate.stype=stype.id");

    $query = implode(" ",$qq);

    printf("Query = [%s]\n",$query);

    $qr = pg_query($handle,$query);
    $qs = pg_num_rows($qr);

    $schedule = array();

    for($a=0; $a<$qs; $a++){
	$qo = pg_fetch_array($qr,$a);

	$bname = $qo['bname'];
	$amount = $qo['amount'];
	$rate = $qo['rate'];
	$ccode = $qo['ccode'];
	$year = $qo['year'];
	$pays = $qo['pays'];
	$yoffset = getPGSQLarray($qo['yoffset']);
	$month = getPGSQLarray($qo['month']);
	$percentage = getPGSQLarray($qo['percentage']);
//	printf("%d: %s %s:%s -> %d(%s)\n",$a+1,$bname,$year,$nickname,$amount,$ccode);
	for($b=0; $b<$pays; $b++){
	    $per = $percentage[$b];
	    $pay = ($amount/100)*$per;
	    $yen = $pay*$rate;
	    $note = sprintf("%d/%d - %d%% of %d",$b+1,$pays,$per,$amount);
//	    printf("\t%d-%d %d(%s) - \\%d %s\n",$year+$yoffset[$b],$month[$b],$pay,$ccode,$yen,$note);

	    $schedule[] = array('Y'=>$year+$yoffset[$b],'M'=>$month[$b],'pay'=>$pay,'cur'=>$ccode,'yen'=>$yen,'brand'=>$bname,'pays'=>$pays,'count'=>$b+1);
	}

    }
    usort($schedule,__ymSort);
    for($a=0,$b=count($schedule); $b--; $a++){
	$s = $schedule[$a];
	printf("%04d: Y=%d M=%d P=%d %d/%d %s\n",$a+1,$s['Y'],$s['M'],$s['pay'],$s['count'],$s['pays'],$s['brand']);
    }
//-----------------------------------------------------------------------------
  pg_query($handle,"commit");
  pg_close($handle);
}
?>
