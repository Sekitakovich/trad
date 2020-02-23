<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>report</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
<link href="../proto.menu.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
</head>

<body>
<script type="text/javascript" src="../common.js"></script>
<script type="text/javascript" src="../php.js"></script>
<script type="text/javascript" src="../prototype.js"></script>
<script type="text/javascript" src="../proto.menu.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle); // var_dump($whoami);
	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	case "shop":
		$id = $_REQUEST['id'];
		$ps = $_REQUEST['ps'];
		$pe = $_REQUEST['pe'];
		$secDay = (60*60*24);
		$us = strtotime($ps);
		$ue = strtotime($pe);
		$days = ($ue-$us)/$secDay; // 通算日数
		$week = date("w",$us); // 開始曜日
		$csv = array(); // 一覧
		$csv[] = "店舗名,日付,昨年,予算,売上,取りおき,顧客,客数";

?>
<p class="title1"><a href="javascript:history.back()">店舗別日別詳細 <?php printf("%s ～ %s",date("Y年n月j日",$us),date("Y年n月j日",$ue)); ?> (クリックで戻る)</a> 
		<?php
if($whoami['perm']&PERM_EXPORT_CSV_MAX){
?>
		<input name="export" type="button" id="export" onclick="MM_goToURL('parent','../csvout.php');return document.MM_returnValue" value="CSV形式での出力" />
		<?php
}
?>
</p>
<?php
	for($aa=0,$bb=count($id); $bb--; $aa++){
		$query = sprintf("select shop.* from shop where shop.id='%d'",$id[$aa]);
		$sr = pg_query($handle,$query);
		$so = pg_fetch_array($sr);
//------------------------------------------------------------------
		$query = sprintf("select sum(target) as mvalue from daily where vf=true and shop='%d' and yyyymmdd between '%s' and '%s'",$id[$aa],$ps,$pe);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$mvalue = $qo['mvalue']; // 期間内売上目標金額
//------------------------------------------------------------------
?>
<a name="<?php printf("shop%06d",$id[$aa]); ?>" id="<?php printf("shop%06d",$id[$aa]); ?>"></a>
<p class="title1"><a href="javascript:history.back()">★<?php printf("%s ",$so['name']); ?> (クリックで戻る)</a></p>

<form action="" method="post" enctype="application/x-www-form-urlencoded" name="shop" target="_self" id="shop">
		<table width="100%">
				<tr>
						<td width="3%" class="th-edit">日付</td>
						<td width="3%" class="th-editDigit">昨年</td>
						<td width="6%" class="th-editDigit">日割予算</td>
						<td width="3%" class="th-editDigit">売上</td>
						<td width="5%" class="th-editDigit">達成率</td>
						<td width="5%" class="th-editDigit">昨対比</td>
						<td width="5%" class="th-editDigit">取りおき</td>
						<td width="3%" class="th-editDigit">顧客</td>
						<td width="3%" class="th-editDigit">客数</td>
						<td width="5%" class="th-editDigit">客単価</td>
						<td width="9%" class="th-edit">最終更新日時</td>
						<td width="5%" class="th-edit">イベント</td>
						<td width="45%" class="th-edit">特記事項</td>
				</tr>
				<?php
		$rSum = 0;
		$tsum = 0;
		$bSum = 0;
		$lSum = 0;
		$mSum = 0;
		$vSum = 0;
		
		for($a=0; $a<=$days; $a++){
//
			$query = sprintf("select * from event where vf=true and '%d'=any(shop) and (cast('%s' as date)+cast('%d day' as interval)) between ps and pe",$id[$aa],$ps,$a);
			$er = pg_query($handle,$query);
			$es = pg_num_rows($er);
			if($es){
				$ooo = array();
				for($aaa=0; $aaa<$es; $aaa++){
					$eo = pg_fetch_array($er,$aaa);
					$ooo[] = sprintf("<span title=\"%s\">%s</span>",$eo['remark'],$eo['name']);
				}
				$evName = implode("<br />",$ooo);
			}
			else{
				$evName = "　";
			}
//		
			$query = sprintf("select daily.* from daily where daily.vf=true and daily.shop='%d' and daily.yyyymmdd=(cast('%s' as date)+cast('%d day' as interval))",$id[$aa],$ps,$a);
			$qr = pg_query($handle,$query);
			$qs = pg_num_rows($qr);
			if($qs){
				$qo = pg_fetch_array($qr);
				$result = $qo['result'];
				$target = $qo['target'];
				$last = $qo['last'];
				$book = $qo['book'];
				$member = $qo['member'];
				$visitor = $qo['visitor'];
				$cavg = $visitor? $result/$visitor:0;
				$dwin = $target? ((float)$result/(float)$target)*100.0:0;
				$ppy = $last? ((float)$result/(float)$last)*100.0:0;
				$rSum += $result;
				$tSum += $target;
				$bSum += $book;
				$lSum += $last;
				$mSum += $member;
				$vSum += $visitor;
//				$win = $mvalue? ((float)$rSum/(float)$mvalue)*100.0:0;
				$win = $tSum? ((float)$rSum/(float)$tSum)*100.0:0;
				$note = $qo['note']? nl2br($qo['note']):"　";
				$etime = ts2JP($qo['etime']);
				$ustaff = $qo['ustaff'];
				if($ustaff){
					$ooo = getStaffInfo($handle,$ustaff);
					$etime = sprintf("%s by %s",$etime,$ooo['nickname']);
				}
			}
			else{
				$result = 0;
				$target = 0;
				$last = 0;
				$book = 0;
				$member = 0;
				$visitor = 0;
				$cavg = 0;
				$win = 0;
				$ppy = 0;
				$note = "　";
				$etime = "---";
				$ustaff=0;
			}

			$line = array($so['name'],date("Y/m/d",$us+($secDay*$a)),$last,$target,$result,$book,$member,$visitor);
			$csv[] = implode(",",$line);
//Var_dump::display($line);
			$query = sprintf("select * from holiday where vf=true and yyyymmdd=(cast('%s' as date)+cast('%d day' as interval))",$ps,$a);
			$hr = pg_query($handle,$query);
			$hs = pg_num_rows($hr);
			if($hs){
				$ho = pg_fetch_array($hr);
			}
			$isHoliday = $hs;
			$hName = sprintf("%s",$hs? $ho['name']:"");
?>
				<tr onclick="zoomElement(this,true)" elmtype="daily" win="<?php printf("%d",$dwin); ?>" registered="<?php printf("%d",$qs); ?>" ustaff="<?php printf("%d",$ustaff); ?>">
						<td class="td-edit" title="<?php printf("%s",$hName); ?>" week="<?php printf("%d",($week+$a)%7); ?>" isHoliday="<?php printf("%d",$isHoliday); ?>"><?php printf("%s",dt2JP(date("Y-m-d",$us+($secDay*$a)))); ?></td>
						<td class="td-editDigit" title="昨年"><?php printf("%s",number_format($last,0)); ?></td>
						<td class="td-editDigit" title="日割予算"><?php printf("%s",number_format($target,0)); ?></td>
						<td class="td-editDigit" title="売上"><?php printf("%s",number_format($result,0)); ?></td>
						<td class="td-editDigit" title="達成率"><?php printf("%s",number_format($win,2)); ?>％</td>
						<td class="td-editDigit" title="昨対比"><?php printf("%s",number_format($ppy,2)); ?>％</td>
						<td class="td-editDigit" title="取りおき"><?php printf("%s",number_format($book,0)); ?></td>
						<td class="td-editDigit" title="顧客"><?php printf("%s",number_format($member)); ?></td>
						<td class="td-editDigit" title="客計"><?php printf("%s",number_format($visitor,0)); ?></td>
						<td class="td-editDigit" title="客単価"><?php printf("%s",number_format($cavg,0)); ?></td>
						<td class="td-edit" title="特記事項"><?php printf("%s",$etime); ?></td>
						<td class="td-edit" title="イベント"><?php printf("%s",$evName); ?></td>
						<td class="td-editWrap" title="特記事項"><?php printf("%s",$note); ?></td>
				</tr>
				<?php
		}
		$wSum = $tSum? ((float)$rSum/(float)$tSum)*100.0:0;
		$pSum = $lSum? ((float)$rSum/(float)$lSum)*100.0:0;

?>
				<tr onclick="zoomElement(this,true)">
						<td class="th-edit">計</td>
						<td class="th-editDigit"><?php printf("%s",number_format($lSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($tSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($wSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($pSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($bSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($mSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vSum,0)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
				</tr>
		</table>
<?php
	}
	$_SESSION['csv'] = $csv;
?>
</form>
<script type="text/javascript">
window.onload = function()
{
	var elm = document.getElementsByTagName('TD');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		switch(elm[a].getAttribute("week")){
			case '6':
				elm[a].style.color = "#0000FF";
				elm[a].style.fontWeight = "bold";
				break;
			case '0':
				elm[a].style.color = "#FF0000";
				elm[a].style.fontWeight = "bold";
				break;
			default:
				if(elm[a].getAttribute("isHoliday")=='1'){
					elm[a].style.color = "#00FF00";
					elm[a].style.fontWeight = "bold";
				}
				break;
		}
	}
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("elmtype")=='daily'){
			if(elm[a].getAttribute("ustaff")!=0){
				elm[a].style.fontStyle='italic';
			}
			if(elm[a].getAttribute("registered")==0){
				elm[a].style.backgroundColor = "#CCCCCC";
			}
			else{
				if(elm[a].getAttribute("win")<100){
					elm[a].className = 'notWin';
				}
			}
		}
	}	
}
</script>
<?php
		break;
//--------------------------------------------------------------------
//--------------------------------------------------------------------
	default:

	$dSec = (60*60*24); // 1日の秒数
	$ut = strtotime(date("Y-n-j")); // 本日(午前0時)のUNIXtime
	$_yy = date("Y",$ut); // 今日の日付
	$_mm = date("n",$ut); // 今日の日付
	$_dd = date("j",$ut); // 今日の日付
	$_ww = date("w",$ut); // 今日の曜日
	$_tt = date("t",$ut); // 今月末日
	
	$_tds = $ut; // 本日
	$_tde = $ut; // 本日
	
	$_lds = $ut-$dSec; // 昨日
	$_lde = $ut-$dSec; // 昨日
	
	$_tws = $ut-($dSec*(($_ww+6)%7)); // 今週初日 (週の始まりは月曜とする)
	$_twe = $_dd==1? $ut:$ut-$dSec;
//	$_twe = $ut-$dSec; // 昨日
//	$_twe = $ut;
	
	$_lws = $_tws-($dSec*7); // 先週初日
	$_lwe = $_lws+($dSec*6);
	
	$_tms = strtotime(sprintf("%d-%d-%d",$_yy,$_mm,1)); // 今月初日
	$_tme = $_dd==1? $ut:$ut-$dSec;
//	$_tme = $_lde; // 昨日まで
//	$_tme = $ut;
//	$_tme = strtotime(sprintf("%d-%d-%d",$_yy,$_mm,$_tt)); // 今月最終日
	
	$_lme = $ut-($dSec*$_dd); // 先月末日
	$_lms = strtotime(date("Y-n-1",$_lme)); //先月初日
	
	
	$pspe = array();
	$pspe['TD'] = array('ps'=>explode("-",date("Y-n-j",$_tds)),'pe'=>explode("-",date("Y-n-j",$_tde)));
	$pspe['LD'] = array('ps'=>explode("-",date("Y-n-j",$_lds)),'pe'=>explode("-",date("Y-n-j",$_lde)));
	$pspe['TW'] = array('ps'=>explode("-",date("Y-n-j",$_tws)),'pe'=>explode("-",date("Y-n-j",$_twe)));
	$pspe['LW'] = array('ps'=>explode("-",date("Y-n-j",$_lws)),'pe'=>explode("-",date("Y-n-j",$_lwe)));
	$pspe['TM'] = array('ps'=>explode("-",date("Y-n-j",$_tms)),'pe'=>explode("-",date("Y-n-j",$_tme)));
	$pspe['LM'] = array('ps'=>explode("-",date("Y-n-j",$_lms)),'pe'=>explode("-",date("Y-n-j",$_lme)));

//	Var_dump::display($pspe);
//
//
	function sortFunc00($a,$b)
	{
		if($a['dw']==$b['dw']){
			return($b['aw']-$a['aw']);
		}
		else{
			return($b['dw']-$a['dw']);
		}
	}
	function sortFunc01($a,$b)
	{
		return($b['result']-$a['result']);
	}
	function sortFunc03($a,$b)
	{
		if($b['win']==$a['win']){
			return(0);
		}
		else{
			return($b['win']>$a['win']? 1:-1);
		}
	}
	function sortFunc04($a,$b)
	{
		if($b['ppy']==$a['ppy']){
			return(0);
		}
		else{
			return($b['ppy']>$a['ppy']? 1:-1);
		}
	}
	function sortFunc06($a,$b)
	{
		return($b['target']-$a['target']);
	}
	function sortFunc07($a,$b)
	{
		return($b['cavg']-$a['cavg']);
	}
	function sortFunc08($a,$b)
	{
		return(strtotime($b['ps'])-strtotime($a['ps']));
	}
	function sortFuncShopName($a,$b)
	{
		return(strcmp($a['name'],$b['name']));
	}
	function sortFuncVisitor($a,$b)
	{
		return($b['visitor']-$a['visitor']);
	}
	$__oList = array(
		array('name'=>'売上','func'=>'sortFunc01'),
		array('name'=>'客数','func'=>'sortFuncVisitor'),
		array('name'=>'事業部','func'=>'sortFunc00'),
		array('name'=>'達成率','func'=>'sortFunc03'),
		array('name'=>'昨対比','func'=>'sortFunc04'),
		array('name'=>'予算','func'=>'sortFunc06'),
		array('name'=>'客単価','func'=>'sortFunc07'),
		array('name'=>'開店時期','func'=>'sortFunc08'),
		array('name'=>'店名','func'=>'sortFuncShopName'),
	);
//
		$query = sprintf("SELECT max(date_part('year',age(ps))) from shop where vf=true");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		
		$query = sprintf("select min(yyyymmdd),max(yyyymmdd) from daily");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$__dMin = explode("-",$qo['min']); $__dMin = $__dMin[0];
		$__dMax = explode("-",$qo['max']); $__dMax = $__dMax[0];

		if($whoami['perm']&PERM_REPORT_FULL){
			$query = sprintf("select id from division where vf=true order by weight desc");
			$qr = pg_query($handle,$query);
			$qs = pg_num_rows($qr);
			$dset = array();
			$dopt = $qs;
			for($a=0; $a<$qs; $a++){
				$qo = pg_fetch_array($qr,$a);
				$dset[] = $qo['id'];
			}
			$query = sprintf("select id from area where vf=true order by weight desc");
			$qr = pg_query($handle,$query);
			$qs = pg_num_rows($qr);
			$aset = array();
			$aopt = $qs;
			for($a=0; $a<$qs; $a++){
				$qo = pg_fetch_array($qr,$a);
				$aset[] = $qo['id'];
			}
		}
		else{
			$dset = getPGSQLarray($whoami['dset']); $dopt = count($dset);
			$aset = getPGSQLarray($whoami['aset']); $aopt = count($aset);
		}
		if(isset($_REQUEST['exec'])){
			$ps = $_REQUEST['ps'];
			$pe = $_REQUEST['pe'];
			$order = $_REQUEST['order'];
			$__dset = $_REQUEST['dset'];
			$__aset = $_REQUEST['aset'];
			$area = $_REQUEST['area'];
			$desc = $_REQUEST['desc'];
			$fromOpen = $_REQUEST['fromOpen'];
			$event = isset($_REQUEST['event'])? "t":"f";
			$division = $_REQUEST['division'];
		}
		else{
			$ps = $pspe['TM']['ps']; // 内田要望
			$pe = $pspe['TM']['pe']; // 内田要望
			$order = 0; // 内田要望
			$__dset = ($whoami['dcheck']=='t')? $dset:array();
			$__aset = ($whoami['acheck']=='t')? $aset:array();
			$area = 0;
			$desc = 't';
			$fromOpen =1;
			$event = 'f';
			$division = 0;
		}

?>
<p class="title1">レポート(期間集計)
		<script type="text/javascript">
function __setPSPE(F,mode)
{
	var a;
	var ps;
	var pe;
	var ss;
	switch(mode){
		case 'LD':
			ps = new Array(<?php printf("%s",implode(',',$pspe['LD']['ps'])); ?>);
			pe = new Array(<?php printf("%s",implode(',',$pspe['LD']['pe'])); ?>);
			ss = "昨日";
			break;
		case 'TW':
			ps = new Array(<?php printf("%s",implode(',',$pspe['TW']['ps'])); ?>);
			pe = new Array(<?php printf("%s",implode(',',$pspe['TW']['pe'])); ?>);
			ss = "今週";
			break;
		case 'LW':
			ps = new Array(<?php printf("%s",implode(',',$pspe['LW']['ps'])); ?>);
			pe = new Array(<?php printf("%s",implode(',',$pspe['LW']['pe'])); ?>);
			ss = "先週";
			break;
		case 'TM':
			ps = new Array(<?php printf("%s",implode(',',$pspe['TM']['ps'])); ?>);
			pe = new Array(<?php printf("%s",implode(',',$pspe['TM']['pe'])); ?>);
			ss = "今月";
			break;
		case 'LM':
			ps = new Array(<?php printf("%s",implode(',',$pspe['LM']['ps'])); ?>);
			pe = new Array(<?php printf("%s",implode(',',$pspe['LM']['pe'])); ?>);
			ss = "先月";
			break;
		default:
			ps = new Array(<?php printf("%s",implode(',',$pspe['TD']['ps'])); ?>);
			pe = new Array(<?php printf("%s",implode(',',$pspe['TD']['pe'])); ?>);
			ss = "本日";
			break;
	}
/*
	var mes = sprintf("集計対象期間を %s (%s ～ %s) として一覧を更新します",ss,implode("-",ps),implode("-",pe));
	alert(mes);
*/
	for(a=0; a<3; a++){
		F.elements['ps['+a+']'].value = ps[a]; leapAdjust(F,'ps');
		F.elements['pe['+a+']'].value = pe[a]; leapAdjust(F,'pe');
	}
	F.submit();
}
		</script>
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	var a;
	var b;
	var elm;

	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else{
		return true;
	}
}
		</script>
		<script type="text/javascript">
function cbAlter(which)
{
	var a;
	var b;
	var elm = document.getElementsByTagName('INPUT');
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute('elmtype')==which){
			elm[a].checked = elm[a].checked? false:true;
		}
	}
	document.menu.submit();
}
		</script>
</p>
<form action="" method="get" enctype="application/x-www-form-urlencoded" name="menu" target="_self" id="menu" onsubmit="return checkTheForm(this)">
		<table width="52%">

				<tr>
						<td width="7%" class="th-edit">対象期間</td>
						<td width="45%" class="td-edit"><label>
						<select name="ps[0]" id="ps[0]" onchange="leapAdjust(this.form,'ps')">
								<?php
for($a=$__dMin; $a<=$__dMax; $a++){
	$selected=sprintf("%s",$a==$ps[0]? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
								<?php
}
?>
						</select></label><label>
年
<select name="ps[1]" id="ps[1]" onchange="leapAdjust(this.form,'ps')">
		<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$ps[1]? " selected":"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label><label>
月
<select name="ps[2]" id="ps[2]">
		<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$ps[2]? " selected":"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select></label>
日 ～</label> <label>
<select name="pe[0]" id="pe[0]" onchange="leapAdjust(this.form,'pe')">
		<?php
for($a=$__dMin; $a<=$__dMax; $a++){
	$selected=sprintf("%s",$a==$pe[0]? " selected":"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select>
</label>
<label> 年
<select name="pe[1]" id="pe[1]" onchange="leapAdjust(this.form,'pe')">
		<?php
for($a=1; $a<=12; $a++){
	$selected=sprintf("%s",$a==$pe[1]? " selected":"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select>
</label>
<label> 月
<select name="pe[2]" id="pe[2]">
		<?php
for($a=1; $a<=31; $a++){
	$selected=sprintf("%s",$a==$pe[2]? " selected":"");
?>
		<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
		<?php
}
?>
</select>
</label>
日
<label></label></td>
						<td width="7%" class="th-edit">jump</td>
						<td width="41%" class="td-edit"><label>
								<input name="setTD" type="button" id="setTD" onclick="__setPSPE(this.form,'TD')" value="本日" />
								</label>
								<label>
								<input name="setLD" type="button" id="setLD" onclick="__setPSPE(this.form,'LD')" value="昨日" />
								</label>
								<label>
								<input name="setTW" type="button" id="setTW" onclick="__setPSPE(this.form,'TW')" value="今週" />
								</label>
								<label>
								<input name="setLW" type="button" id="setLW" onclick="__setPSPE(this.form,'LW')" value="先週" />
								</label>
								<label>
								<input name="setTM" type="button" id="setTM" onclick="__setPSPE(this.form,'TM')" value="今月" />
								</label>
								<label>
								<input name="setLM" type="button" id="setLM" onclick="__setPSPE(this.form,'LM')" value="先月" />
						</label></td>
				</tr>
				<tr elmtype="full">
						<td class="th-edit">事業部</td>
						<td class="td-edit"><label>
								<select name="division" id="division" onchange="this.form.submit()">
										<option value="0">-- 全て --</option>
										<?php
		$query = sprintf("select * from division where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$division? " selected":"");
			$dName=getDivisionName($handle,$qo['id']);
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
										<?php
		}
?>
								</select>
						以下</label></td>
						<td class="th-edit">エリア</td>
						<td class="td-edit"><select name="area" id="area" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$query = sprintf("select * from area where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$area? " selected":"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
								<?php
		}
?>
						</select>
						以下</td>
				</tr>
				<tr elmtype="adsets">
						<td class="th-edit"><a href="javascript:void(0)" onclick="cbAlter('dset')">事業部</a></td>
						<td class="td-edit">
								<?php
		for($a=0,$b=count($dset); $b--; $a++){
			$checked = sprintf("%s",in_array($dset[$a],$__dset)? " checked":"");
			$dName=getDivisionName($handle,$dset[$a]);
?>
								<label><input elmtype="dset" name="dset[]" type="checkbox" id="dset[]" onclick="this.form.submit()" value="<?php printf("%d",$dset[$a]); ?>" <?php printf("%s",$checked); ?> />
								<?php printf("%s",$dName); ?> 以下</label><br />
								<?php
		}
?></td>
						<td class="th-edit"><a href="javascript:void(0)" onclick="cbAlter('aset')">エリア</a></td>
						<td class="td-edit"><?php
		for($a=0,$b=count($aset); $b--; $a++){
			$checked = sprintf("%s",in_array($aset[$a],$__aset)? " checked":"");
			$dName=getAreaName($handle,$aset[$a]);
?>
								<label>
								<input elmtype="aset" name="aset[]" type="checkbox" id="aset[]" onclick="this.form.submit()" value="<?php printf("%d",$aset[$a]); ?>" <?php printf("%s",$checked); ?> />
								<?php printf("%s",$dName); ?> 以下</label>
								<br />
								<?php
		}
?></td>
				</tr>
				<tr>
						<td class="th-edit">オプション</td>
						<td class="td-edit"><label><img src="../images/wakaba3.gif" width="10" height="14" /> 表示
										開店より
										<select name="fromOpen" id="fromOpen" onchange="this.form.submit()">
														<?php
	for($a=1; $a<=10; $a++){
		$selected = sprintf("%s",$a==$fromOpen? " selected":"");
?>
														<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
														<?php
	}
?>
								</select></label>
								年以内 
								<label><input onclick="this.form.submit()" <?php printf("%s",$event=='t'? " checked":""); ?> name="event" type="checkbox" id="event" value="t" />
						期間内にイベント開催 (experimental)</label></td>
						<td class="th-edit">並べ替え</td>
						<td class="td-edit"><select name="order" id="order" onchange="this.form.submit()">
								<?php
	for($a=0,$b=count($__oList); $b--; $a++){
		$selected = sprintf("%s",$a==$order? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$__oList[$a]['name']); ?></option>
								<?php
	}
?>
						</select>
								<label>
								<input name="desc" type="radio" id="desc" onclick="this.form.submit()" value="t" <?php printf("%s",$desc=='t'? "checked":"");?> />
降順</label>
								<label>
								<input name="desc" type="radio" id="desc" onclick="this.form.submit()" value="f" <?php printf("%s",$desc=='f'? "checked":"");?> />
昇順</label>
								<input name="exec" type="hidden" id="exec" value="go" />
								<input type="submit" name="show" id="show" value="再表示" /></td>
				</tr>
		</table>
</form>
<?php
/*
	本当ならここで一時テーブルなど作りたくないが、並べ替えの条件に達成率を含むとなると
	ゼロ除算(＝クエリーの失敗)が発生する可能性があり、どうしてもこの形にせざるを得ない。
*/
		$qq = array();
		$select = array(
			"shop.id",
			"shop.name",
			"shop.ps",
			"date_part('year',age(shop.ps)) as ys",
			"division.id as did",
			"division.weight as dw",
			"area.id as aid",
			"area.weight as aw",
			"currency.rate",
		);
		$qq[] = sprintf("select %s",implode(",",$select));
		$qq[] = sprintf("from shop,division,area,currency");
		$qq[] = sprintf("where shop.vf=true and shop.division=division.id and shop.area=area.id and shop.currency=currency.id");
//
		if($whoami['perm']&PERM_REPORT_FULL){
			if($division){
				$tree = divisionTree($handle,$division); $tree[]=$division;
				$qq[] = sprintf("and shop.division in (%s)",implode(",",$tree));
			}
			if($area){
				$tree = areaTree($handle,$area); $tree[]=$area;
				$qq[] = sprintf("and shop.area in (%s)",implode(",",$tree));
			}
			if($brand){
				$qq[] = sprintf("and '%d'=any(shop.brand)",$brand);
			}
		}
		else{
			$ppp = array();
			if(count($__dset)){
				$tree = array();
				for($a=0,$b=count($__dset); $b--; $a++){
					$kkk = divisionTree($handle,$__dset[$a]);
					$kkk[] = $__dset[$a];
//printf("%d(%d): %s<br>",$a,$__dset[$a],implode(",",$kkk));
					$tree=array_merge($tree,$kkk);
				}
				$tree = array_unique($tree); // 重複する要素を排除
				$ppp[] = sprintf("shop.division in (%s)",implode(",",$tree));
			}
	//
			if(count($__aset)){
				$tree = array();
				for($a=0,$b=$aopt; $b--; $a++){
					if($val=$__aset[$a]){
						$kkk = areaTree($handle,$val);
						$kkk[] = $val;
						$tree=array_merge($tree,$kkk);
					}
				}
				$tree = array_unique($tree); // 重複する要素を排除
				$ppp[] = sprintf("shop.area in (%s)",implode(",",$tree));
			}
	//
			$qq[] = sprintf("and (%s)",implode(" or ",$ppp));
		}
		$qq[] = sprintf("and ((now() between shop.ps and shop.pe) or (shop.ps=shop.pe and now()>=shop.ps))");
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
//
//
		$data = array();
		$ds=0;
//
			$__ps = sprintf("%d-%d-%d",$ps[0],$ps[1],$ps[2]);
			$__pe = sprintf("%d-%d-%d",$pe[0],$pe[1],$pe[2]);

	
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$id = $qo['id'];
			$name = $qo['name'];
//
			if($event=='t'){
				$query = sprintf("select count(*) from event where vf=true and '%d'=any(shop) and ((ps between '%s' and '%s') or (pe between '%s' and '%s'))",$qo['id'],$__ps,$__pe,$__ps,$__pe);
				$er = pg_query($handle,$query);	
				$eo = pg_fetch_array($er);
				$choice = $eo['count'];
			}
			else $choice = 1;
//
			if($choice){
				$ps = $qo['ps'];
				$dname=getDivisionName($handle,$qo['did']);
				$aname=getAreaName($handle,$qo['aid']);
				$dw = $qo['dw'];
				$aw = $qo['aw'];
				$ys = $qo['ys'];
	//
				$query = sprintf("select avg(result) as ravg,sum(result) as result,sum(target) as target,sum(last) as last,sum(member) as member,sum(visitor) as visitor,sum(book) as book from daily where vf=true and shop='%d' and (yyyymmdd between '%s' and '%s')",$qo['id'],$__ps,$__pe);
				$sr = pg_query($handle,$query);
				$so = pg_fetch_array($sr);
				$result = $so['result'];
				$target = $so['target'];
				$last = $so['last'];
				$book = $so['book'];
				$member = $so['member'];
				$visitor = $so['visitor'];
				$ravg = $so['ravg'];
	
				$cavg = $visitor? $result/$visitor:0;
	
				$win = $target? ((float)$result/(float)$target)*100.0:0;
				$ppy = $last? ((float)$result/(float)$last)*100.0:0;
	
	//
				$data[] = array(
					'id'=>$id,'name'=>$name,'ps'=>$ps,
					'dw'=>$dw,'aw'=>$aw,'sid'=>$qo['id'],
					'dname'=>$dname,'aname'=>$aname,
					'ys'=>$ys,
					'result'=>$result,'target'=>$target,'last'=>$last,'book'=>$book,'member'=>$member,'visitor'=>$visitor,
					'win'=>$win,'ppy'=>$ppy,
					'cavg'=>$cavg,'ravg'=>$ravg,
				);
				$ds++;
	//
			}
		}
//		Var_dump::display($data);

		usort($data,$__oList[$order]['func']);
		if($desc=='f'){
			$data=array_reverse($data);
		}

?><span class="title1">
<script language="JavaScript" type="text/javascript">
function checkTheList(F)
{
	var mes = new Array();
	var err = 0;
	var a;
	var b;
	var elm;


/*
	if(F.elements['id[]'].length==0){
		mes[err++] = '店舗が選択されていません';
	}
*/
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else{
		return true;
	}
}
		</script>
</span>
<script type="text/javascript">
function choiceShop(F,mode)
{
	var elm = F.elements['id[]'];
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		elm[a].checked = mode;
	}
	if(mode){
		F.elements['showall'].className = 'dummyClass';
	}
}
</script>
<span class="title1">
<script type="text/javascript">
function setShow(F)
{
	F.elements['showall'].className = 'dummyClass';
}
	</script>
</span>
<form action="" method="get" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list" onsubmit="return checkTheList(this)">
<p class="title1">
		一覧表示 (<?php printf("%s",number_format($ds)); ?>件)
		
		<label></label>
	<input name="ps" type="hidden" id="ps" value="<?php printf("%s",$__ps); ?>" />
	<input name="pe" type="hidden" id="pe" value="<?php printf("%s",$__pe); ?>" />
	<input name="mode" type="hidden" id="mode" value="shop" />
	<?php
if($whoami['perm']&PERM_EXPORT_CSV_MIN){
?>
	<label>
	<input name="export" type="button" id="export" onclick="MM_goToURL('parent','../csvout.php');return document.MM_returnValue" value="CSV形式での出力" />
	</label>
	<?php
}
?>
	<input name="showall" type="submit" class="notDisplay" id="showall" value="選択された店舗の日別詳細を見る" />
</p>
<div id="shopList">
		<table width="17%">
				<tr>
						<td width="2%" height="18" class="th-edit">No</td>
						<td width="2%" class="th-edit">店舗 
								<label>
								<input name="on" type="button" id="on" onclick="choiceShop(this.form,true)" value="全てon" /></label>
								<label><input name="off" type="button" id="off" onclick="choiceShop(this.form,false)" value="全てoff" />
						</label></td>
						<td width="1%" class="th-editDigit">昨年</td>
						<td width="1%" class="th-editDigit">予算</td>
						<td width="1%" class="th-editDigit">売上</td>
						<td width="1%" class="th-editDigit">平均/日</td>
						<td width="1%" class="th-editDigit">達成率</td>
						<td width="1%" class="th-editDigit">昨対比</td>
						<td width="95%" class="th-editDigit">取りおき</td>
						<td width="1%" class="th-editDigit">顧客</td>
						<td width="1%" class="th-editDigit">客数</td>
						<td width="1%" class="th-editDigit">客単価</td>
				</tr>
<?php
	$rSum = 0;
	$tSum = 0;
	$lSum = 0;
	$bSum = 0;
	$mSum = 0;
	$vSum = 0;
	$cSum = 0;

	$csvMIN = array(); // for export
	$csvMIN[] = "店舗名,昨年,予算,売上,取りおき,顧客,客数";
	
//	for($a=0; $a<$qs; $a++){
//		$qo = pg_fetch_array($qr,$a);
	for($a=0; $a<$ds; $a++){
		$qo = $data[$a];
		$sid = $qo['sid'];
		$ys = $qo['ys'];
		$dName = $qo['dname'];
		$aName = $qo['aname'];
		$result = $qo['result'];
		$target = $qo['target'];
		$last = $qo['last'];
		$book = $qo['book'];
		$rSum += $result;
		$tSum += $target;
		$lSum += $last;
		$bSum += $book;
		$member = $qo['member'];
		$visitor = $qo['visitor'];
		$cavg = $qo['cavg'];
		$win = $qo['win'];
		$ppy = $qo['ppy'];
		$mSum += $member;
		$vSum += $visitor;
		$ravg = $qo['ravg'];

		$line = array($qo['name'],$last,$target,$result,$book,$member,$visitor);
		$csvMIN[] = implode(",",$line);

//
?>
				<tr onclick="zoomElement(this,true)" elmtype="shop" shop="<?php printf("%d",$sid); ?>" win="<?php printf("%d",$win); ?>" ys="<?php printf("%d",$ys); ?>">
						<td class="td-edit"><?php printf("%d",$a+1); ?></td>
						<td class="td-edit" title="<?php printf("%s : %s",$dName,$aName); ?>">
								<label>
								<input onclick="setShow(this.form)" name="id[]" type="checkbox" id="id[]" value="<?php printf("%d",$qo['id']); ?>" />
						<?php printf("%s",$qo['name']); ?> <img src="../images/wakaba3.gif" elmtype="icon" ys="<?php printf("%d",$ys); ?>" width="10" height="14" /></label></td>
						<td class="td-editDigit" title="昨年"><?php printf("%s",number_format($last,0)); ?></td>
						<td class="td-editDigit" title="予算"><?php printf("%s",number_format($target,0)); ?></td>
						<td class="td-editDigit" title="売上"><?php printf("%s",number_format($result,0)); ?></td>
						<td class="td-editDigit" title="売上平均/日"><?php printf("%s",number_format($ravg,0)); ?></td>
						<td class="td-editDigit" title="達成率"><?php printf("%s",number_format($win,2)); ?>％</td>
						<td class="td-editDigit" title="昨対比"><?php printf("%s",number_format($ppy,2)); ?>％</td>
						<td class="td-editDigit" title="取りおき"><?php printf("%s",number_format($book,0)); ?></td>
						<td class="td-editDigit" title="顧客"><?php printf("%s",number_format($member,0)); ?></td>
						<td class="td-editDigit" title="客計"><?php printf("%s",number_format($visitor,0)); ?></td>
						<td class="td-editDigit" title="客単価"><?php printf("%s",number_format($cavg,0)); ?></td>
				</tr>
<?php
	}
		$wSum = $tSum? ((float)$rSum/(float)$tSum)*100.0:0;
		$pSum = $lSum? ((float)$rSum/(float)$lSum)*100.0:0;
?>
				<tr onclick="zoomElement(this,true)">
						<td class="th-edit">計/平均</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($lSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($tSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($wSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($pSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($bSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($mSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vSum,0)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
				</tr>
		</table>
</div>
</form>
		<script type="text/javascript">
window.onload = function()
{
	var perm_report_full = <?php printf("%d",$whoami['perm']&PERM_REPORT_FULL); ?>;
	var fromOpen = <?php printf("%d",$fromOpen); ?>;

	var elm = document.getElementsByTagName('IMG');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("elmtype")=='icon'){
			if(elm[a].getAttribute("ys")>=fromOpen){
				elm[a].className = 'notDisplay';
			}
		}
	}
	
	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("elmtype")=='shop'){
			if(elm[a].getAttribute("win")<100){
				elm[a].className = 'notWin';
			}
			if(elm[a].getAttribute("ys")<fromOpen){
				elm[a].style.color = '#0000FF';
			}
			if(elm[a].getAttribute("shop")=='<?php printf("%d",$whoami['shop']); ?>'){
				elm[a].style.fontWeight = 'bold';
			}
		}
		else if(elm[a].getAttribute("elmtype")=='adsets' && perm_report_full){
			elm[a].className = 'notDisplay';
		}
		else if(elm[a].getAttribute("elmtype")=='full' && perm_report_full==0){
			elm[a].className = 'notDisplay';
		}
	}
	leapAdjust(document.menu,'ps');
	leapAdjust(document.menu,'pe');
//
//
	var myMenuItems = [
	  {
		name: '選択された店舗の日別詳細を見る',
		className: 'edit', 
		callback: function() {
			document.list.submit();
		}
	}
	]
	new Proto.Menu({
	  selector: '#shopList', // context menu will be shown when element with id of "contextArea" is clicked
	  className: 'menu desktop', // this is a class which will be attached to menu container (used for css styling)
	  menuItems: myMenuItems // array of menu items
	})
//
//
}
</script>
<?php
		$_SESSION['csv'] = $csvMIN;
		break;
//--------------------------------------------------------------------
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</body>
</html>
