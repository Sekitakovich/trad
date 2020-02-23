<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>report</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript" src="../common.js"></script>
<script type="text/javascript" src="../php.js"></script>
<script type="text/javascript" src="../prototype.js"></script>
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

		$query = sprintf("select shop.* from shop where shop.id='%d'",$id);
		$sr = pg_query($handle,$query);
		$so = pg_fetch_array($sr);
/*
		$dName = getDivisionName($handle,$so['division']);
		$aName = getAreaName($handle,$so['area']);
*/
?>
<p class="title1"><a href="javascript:history.back()"><?php printf("%s %s ～ %s",$so['name'],date("Y年n月j日",$us),date("Y年n月j日",$ue)); ?> (クリックで戻る)</a></p>

<form action="" method="post" enctype="application/x-www-form-urlencoded" name="shop" target="_self" id="shop">
		<table width="7%">
				<tr>
						<td class="th-edit" width="3%">日付</td>
						<td class="th-editDigit" width="3%">売上</td>
						<td class="th-editDigit" width="3%">昨年</td>
						<td width="1%" class="th-editDigit">昨対比</td>
						<td class="th-editDigit" width="3%">日割予算</td>
						<td width="1%" class="th-editDigit">達成率</td>
						<td width="95%" class="th-editDigit">取りおき</td>
						<td class="th-editDigit" width="94%">顧客</td>
						<td class="th-editDigit" width="94%">一般</td>
						<td width="1%" class="th-editDigit">客計</td>
						<td width="1%" class="th-editDigit">客単価</td>
						<td class="th-edit" width="94%">特記事項</td>
				</tr>
				<?php
		$rSum = 0;
		$tsum = 0;
		$bSum = 0;
		$lSum = 0;
		$mSum = 0;
		$vSum = 0;
		$cSum = 0;
		
		$csv = array();
		
		for($a=0; $a<=$days; $a++){
			$query = sprintf("select daily.* from daily where daily.vf=true and daily.shop='%d' and daily.yyyymmdd=(cast('%s' as date)+cast('%d day' as interval))",$id,$ps,$a);
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
				$customer = $member+$visitor;
				$cavg = $customer? $result/$customer:0;
				$win = $target? ((float)$result/(float)$target)*100.0:0;
				$ppy = $last? ((float)$result/(float)$last)*100.0:0;
				$rSum += $result;
				$tSum += $target;
				$bSum += $book;
				$lSum += $last;
				$mSum += $member;
				$vSum += $visitor;
				$cSum += $customer;
			}
			else{
				$result = 0;
				$target = 0;
				$last = 0;
				$book = 0;
				$member = 0;
				$visitor = 0;
				$customer = 0;
				$cavg = 0;
				$win = 0;
				$ppy = 0;
			}

			$line = array(date("Y/m/d",$us+($secDay*$a)),$result,$target,$last,$book,$member,$visitor);
			$csv[] = implode(",",$line);
//
			$query = sprintf("select * from holiday where yyyymmdd=(cast('%s' as date)+cast('%d day' as interval))",$ps,$a);
			$hr = pg_query($handle,$query);
			$hs = pg_num_rows($hr);
			if($hs){
				$ho = pg_fetch_array($hr);
			}
			$isHoliday = $hs;
			$hName = sprintf("%s",$hs? $ho['name']:"");
//
			$query = sprintf("select * from event where vf=true and shop='%d' and (cast('%s' as date)+cast('%d day' as interval)) between ps and pe",$id,$ps,$a);
			$er = pg_query($handle,$query);
			$es = pg_num_rows($er);
//printf("Query(%d:%d) = [%s]",$er,$es,$query);
			if($es){
				$eo = pg_fetch_array($er);
			}
			$isEvent = $es;
			$eName = sprintf("%s",$es? $eo['name']:"");
			$eNote = sprintf("%s",$es? $eo['remark']:"");
//
?>
				<tr elmtype="daily" win="<?php printf("%d",$win); ?>" registered="<?php printf("%d",$qs); ?>" isEvent="<?php printf("%d",$isEvent); ?>">
						<td class="td-edit" title="<?php printf("%s",$hName); ?>" week="<?php printf("%d",($week+$a)%7); ?>" isHoliday="<?php printf("%d",$isHoliday); ?>"><?php printf("%s",dt2JP(date("Y-m-d",$us+($secDay*$a)))); ?></td>
						<td class="td-editDigit" title="売上"><?php printf("%s",number_format($result,0)); ?></td>
						<td class="td-editDigit" title="昨年"><?php printf("%s",number_format($last,0)); ?></td>
						<td class="td-editDigit" title="昨対比"><?php printf("%s",number_format($ppy,2)); ?>％</td>
						<td class="td-editDigit" title="日割予算"><?php printf("%s",number_format($target,0)); ?></td>
						<td class="td-editDigit" title="達成率"><?php printf("%s",number_format($win,2)); ?>％</td>
						<td class="td-editDigit" title="取りおき"><?php printf("%s",number_format($book,0)); ?></td>
						<td class="td-editDigit" title="顧客"><?php printf("%s",number_format($member)); ?></td>
						<td class="td-editDigit" title="一般"><?php printf("%s",number_format($visitor)); ?></td>
						<td class="td-editDigit" title="客計"><?php printf("%s",number_format($customer,0)); ?></td>
						<td class="td-editDigit" title="客単価"><?php printf("%s",number_format($cavg,0)); ?></td>
						<td class="td-edit"><a href=javascript:void(0)" title="<?php printf("%s",$eNote); ?>"><?php printf("%s",$es? $eName:""); ?></a></td>
				</tr>
				<?php
		}
		$wSum = $tSum? ((float)$rSum/(float)$tSum)*100.0:0;
		$pSum = $lSum? ((float)$rSum/(float)$lSum)*100.0:0;

?>
				<tr>
						<td class="th-edit" width="3%">計</td>
						<td class="th-editDigit" width="3%"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit" width="3%"><?php printf("%s",number_format($lSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($pSum,2)); ?>％</td>
						<td class="th-editDigit" width="3%"><?php printf("%s",number_format($tSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($wSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($bSum,0)); ?></td>
						<td class="th-editDigit" width="94%"><?php printf("%s",number_format($mSum,0)); ?></td>
						<td class="th-editDigit" width="94%"><?php printf("%s",number_format($vSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($cSum,0)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-edit" width="94%">&nbsp;</td>
				</tr>
		</table>
		<?php
if($whoami['perm']&PERM_EXPORT_CSV){
?>
		<p>※CSV形式 (Ctrl-Aで選択 → xxxx.CSVとして保存)</p>
		<p>
				<label>
				<textarea name="csv" cols="128" rows="12" readonly="readonly" class="microFont" id="csv"><?php printf("%s",implode("\n",$csv)); ?></textarea>
				</label>
		</p>
		<?php
}
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
			if(elm[a].getAttribute("registered")==0){
				elm[a].style.backgroundColor = "#CCCCCC";
			}
			else{
				if(elm[a].getAttribute("win")<100){
					elm[a].className = 'notWin';
				}
				if(elm[a].getAttribute("isEvent")==1){
					elm[a].style.fontWeight = "bold";
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
//	$_twe = $ut-$dSec; // 昨日
	$_twe = $ut;
	
	$_lws = $_tws-($dSec*7); // 先週初日
	$_lwe = $_lws+($dSec*6);
	
	$_tms = strtotime(sprintf("%d-%d-%d",$_yy,$_mm,1)); // 今月初日
//	$_tme = $_lde; // 昨日まで
	$_tme = $ut;
	
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
		$__oList = array(
			array('name'=>'標準(事業部：エリア)','text'=>'dw desc,dname,aw desc,aname desc,name'),
			array('name'=>'予算(降順)','text'=>'target desc,result desc,dw desc,dname,aw desc,name'),
			array('name'=>'予算(昇順)','text'=>'target     ,result,dw desc,dname,aw desc,name'),
			array('name'=>'売上(降順)','text'=>'result desc,dw desc,dname,aw desc,name'),
			array('name'=>'売上(昇順)','text'=>'result,dw desc,dname,aw desc,name'),
			array('name'=>'達成率(降順)','text'=>'win desc,result desc,dw desc,dname,aw desc,name'),
			array('name'=>'達成率(昇順)','text'=>'win     ,result desc,dw desc,dname,aw desc,name'),
			array('name'=>'昨対比(降順)','text'=>'ppy desc,result desc,dw desc,dname,aw desc,name'),
			array('name'=>'昨対比(昇順)','text'=>'ppy     ,result desc,dw desc,dname,aw desc,name'),
			array('name'=>'客単価(降順)','text'=>'cavg desc,result desc,dw desc,dname,aw desc,name'),
			array('name'=>'客単価(昇順)','text'=>'cavg     ,result desc,dw desc,dname,aw desc,name'),
			array('name'=>'開店時期(降順)','text'=>'open desc,dw desc,aw desc,name'),
			array('name'=>'開店時期(昇順)','text'=>'open,dw desc,aw desc,name'),
		);
//
//
		$query = sprintf("SELECT max(date_part('year',age(open))) from shop where vf=true");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		
		$query = sprintf("select min(yyyymmdd),max(yyyymmdd) from daily");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$__dMin = explode("-",$qo['min']); $__dMin = $__dMin[0];
		$__dMax = explode("-",$qo['max']); $__dMax = $__dMax[0];

//	Var_dump::display($_REQUEST['exec']);

		if(isset($_REQUEST['exec'])){
			$ps = $_REQUEST['ps'];
			$pe = $_REQUEST['pe'];
			$division = $_REQUEST['division'];
			$order = $_REQUEST['order'];
			$__plusarea = $_REQUEST['plusarea'];
		}
		else{
			$ps = $pspe['LD']['ps'];
			$pe = $pspe['LD']['pe'];
			$division = ($whoami['perm']&PERM_REPORT_FULL)? 0:$whoami['division'];
			$order = 0;
			$__plusarea = array();
		}
		$__topDiv = ($whoami['perm']&PERM_REPORT_FULL)? 0:$whoami['division'];
		$__topArea = ($whoami['perm']&PERM_REPORT_FULL)? 0:$whoami['area'];
		
//		printf("Count = %d",count($__plusarea));

?>
<p class="title1">レポート
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
	var mes = sprintf("集計対象期間を %s (%s ～ %s) として一覧を更新します",ss,implode("-",ps),implode("-",pe));
	alert(mes);
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
	
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else return true;
}
		</script>
</p>
<form action="" method="get" enctype="application/x-www-form-urlencoded" name="menu" target="_self" id="menu" onsubmit="return checkTheForm(this)">
		<table width="43%">

				<tr>
						<td width="5%" class="th-edit">対象期間</td>
						<td width="24%" class="td-edit"><label>
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
<label><input name="setTD" type="button" id="setTD" onclick="__setPSPE(this.form,'TD')" value="本日" /></label>
<label><input name="setLD" type="button" id="setLD" onclick="__setPSPE(this.form,'LD')" value="昨日" /></label>
<label><input name="setTW" type="button" id="setTW" onclick="__setPSPE(this.form,'TW')" value="今週" /></label>
<label><input name="setLW" type="button" id="setLW" onclick="__setPSPE(this.form,'LW')" value="先週" /></label>
<label><input name="setTM" type="button" id="setTM" onclick="__setPSPE(this.form,'TM')" value="今月" /></label>
<label><input name="setLM" type="button" id="setLM" onclick="__setPSPE(this.form,'LM')" value="先月" /></label></td>
				</tr>
				<tr elmtype="report-full">
						<td class="th-edit">事業部</td>
						<td class="td-edit"><select name="division" id="division">
								<option value="<?php printf("%d",$__topDiv); ?>">-- 全て --</option>
								<?php
//
		$tree = array();
		$__tree = divisionTree($handle,$__topDiv);
		for($a=0,$b=count($__tree); $b--; $a++){
			$query = sprintf("select count(*) from shop where vf=true and division='%d'",$__tree[$a]);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			if($qo['count']){
				$tree[]=$__tree[$a];
			}
		}
//
		for($a=0,$b=count($tree); $b--; $a++){
			$selected = sprintf("%s",$tree[$a]==$division? " selected":"");
			$dName=getDivisionName($handle,$tree[$a]);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$tree[$a]); ?>"><?php printf("%s",$dName); ?></option>
								<?php
		}
?>
						</select>
						以下
						</td>
				</tr>
				<tr elmtype="plusarea">
						<td class="th-edit">エリア</td>
						<td class="td-edit"><?php
		$plusarea = getPGSQLarray($whoami['plusarea']);						
//Var_dump::display(count($plusarea));
		for($a=0,$b=count($plusarea); $b--; $a++){
			$checked = sprintf("%s",in_array($plusarea[$a],$__plusarea)? " checked":"");
			$dName=getAreaName($handle,$plusarea[$a]);
?>
								<input <?php printf("%s",$checked); ?> name="plusarea[]" type="checkbox" id="plusarea[]" value="<?php printf("%d",$plusarea[$a]); ?>" />
								<?php printf("%s",$dName); ?> 以下<br />
								<?php
		}
?></td>
				</tr>
				<tr>
						<td class="th-edit">並べ替え</td>
						<td class="td-edit"><select name="order" id="order">
								<?php
	for($a=0,$b=count($__oList); $b--; $a++){
		$selected = sprintf("%s",$a==$order? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$__oList[$a]['name']); ?></option>
								<?php
	}
?>
						</select>
						<input type="submit" name="show" id="show" value="更新" />
						<input name="exec" type="hidden" id="exec" value="go" /></td>
				</tr>
		</table>
</form>
<?php
/*
	本当ならここで一時テーブルなど作りたくないが、並べ替えの条件に達成率を含むとなると
	ゼロ除算(＝クエリーの失敗)が発生する可能性があり、どうしてもこの形にせざるを得ない。
*/
//
		$report = array();
		$qq = array();
		$qq[] = sprintf("SELECT shop.id,shop.name,division.weight as dw,area.weight as aw,division.id as did,area.id as aid,sum(result) as result,sum(last) as last,sum(target) as target,sum(book) as book,sum(member) as member,sum(visitor) as visitor");
		$qq[] = sprintf("from daily,shop,division,area");
		$qq[] = sprintf("where shop.vf=true");
		$qq[] = sprintf("and shop.division=division.id");
		$qq[] = sprintf("and shop.area=area.id");
		$qq[] = sprintf("and daily.vf=true");
		$qq[] = sprintf("and daily.shop=shop.id");
		$qq[] = sprintf("and daily.yyyymmdd between '%s' and '%s'",implode("-",$ps),implode("-",$pe));
		$ppp = array();
		$tree = divisionTree($handle,$division);
		$tree[] = $division;
		$ppp[] = sprintf("shop.division in (%s)",implode(",",$tree));

		if($bbb=count($__plusarea)){
			$tree = array();
			for($a=0,$b=$bbb; $b--; $a++){
				$ooo=areaTree($handle,$__plusarea[$a]);
				$ooo[] = $__plusarea[$a];
				$tree=array_merge($tree,$ooo);
//Var_dump::display($tree);
			}
			$ppp[] = sprintf("shop.area in (%s)",implode(",",$tree));
		}
		$qq[] = sprintf("and (%s)",implode(" or ",$ppp));
		$qq[] = sprintf("group by shop.id,shop.name,division.weight,area.weight,division.id,area.id");
		$query = implode(" ",$qq);
//Var_dump::display($query);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$id = $qo['id'];
			$name = $qo['name'];
			$dname=getDivisionName($handle,$qo['did']);
			$aname=getAreaName($handle,$qo['aid']);
			$dw = $qo['dw'];
			$aw = $qo['aw'];
			$result = $qo['result'];
			$target = $qo['target'];
			$last = $qo['last'];
			$book = $qo['book'];
			$member = $qo['member'];
			$visitor = $qo['visitor'];
			$customer = $member+$visitor;
			$cavg = $customer? $result/$customer:0;
			$win = $target? ((float)$result/(float)$target)*100.0:0;
			$ppy = $last? ((float)$result/(float)$last)*100.0:0;
			$report[] = array('id'=>$id,'name'=>$name,'dname'=>$dName,'aname'=>$aName,'dw'=>$dw,'aw'=>$aw,'result'=>$result,'target'=>$target,'last'=>$last,'book'=>$book,'member'=>$member,'visitor'=>$visitor,'customer'=>$customer,'cavg'=>$cavg,'win'=>$win,'ppy'=>$ppy);
		}       
//		Var_dump::display($report);
//
?>
<p class="title1">一覧表示 (<?php printf("%s",number_format($qs)); ?>件)
		<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
 *店名クリックで詳細表示
</p>
 <script type="text/javascript">
function shopDetail(id)
{
	var script = '<?php printf("%s",$_SERVER['PHP_SELF']); ?>';
	var args = new Array(
		'mode=shop',
		'id='+id,
		'ps=<?php printf("%s",$__ps); ?>',
		'pe=<?php printf("%s",$__pe); ?>'
	);
	var url = sprintf("%s?%s",script,implode('&',args));
//	alert(url);
	window.location = url;
}
	</script>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="17%">
				<tr>
						<td width="2%" height="18" class="th-edit">No</td>
						<td width="2%" class="th-edit">店舗</td>
						<td width="1%" class="th-editDigit">売上</td>
						<td width="1%" class="th-editDigit">昨年</td>
						<td width="1%" class="th-editDigit">昨対比</td>
						<td width="1%" class="th-editDigit">予算</td>
						<td width="1%" class="th-editDigit">達成率</td>
						<td width="95%" class="th-editDigit">取りおき</td>
						<td width="1%" class="th-editDigit">顧客</td>
						<td width="1%" class="th-editDigit">一般</td>
						<td width="1%" class="th-editDigit">客計</td>
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

	$csv = array(); // for export
	
	for($a=0,$b=count($report); $b--; $a++){
		$qo = $report[$a];
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
		$customer = $qo['customer'];
		$cavg = $qo['cavg'];
		$win = $qo['win'];
		$ppy = $qo['ppy'];
		$mSum += $member;
		$vSum += $visitor;
		$cSum += $customer;

		$line = array($a+1,$qo['name'],$result,$target,$last,$book,$member,$visitor);
		$csv[] = implode(",",$line);

?>
				<tr elmtype="shop" win="<?php printf("%d",$win); ?>">
						<td class="td-edit"><?php printf("%d",$a+1); ?></td>
						<td class="td-edit" title="<?php printf("%s : %s",$dName,$aName); ?>"><a href="javascript:void(0)" onclick="shopDetail(<?php printf("%d",$qo['id']); ?>)"><?php printf("%s",$qo['name']); ?></a></td>
						<td class="td-editDigit" title="売上"><?php printf("%s",number_format($result,0)); ?></td>
						<td class="td-editDigit" title="昨年"><?php printf("%s",number_format($last,0)); ?></td>
						<td class="td-editDigit" title="昨対比"><?php printf("%s",number_format($ppy,2)); ?>％</td>
						<td class="td-editDigit" title="予算"><?php printf("%s",number_format($target,0)); ?></td>
						<td class="td-editDigit" title="達成率"><?php printf("%s",number_format($win,2)); ?>％</td>
						<td class="td-editDigit" title="取りおき"><?php printf("%s",number_format($book,0)); ?></td>
						<td class="td-editDigit" title="顧客"><?php printf("%s",number_format($member,0)); ?></td>
						<td class="td-editDigit" title="一般"><?php printf("%s",number_format($visitor,0)); ?></td>
						<td class="td-editDigit" title="客計"><?php printf("%s",number_format($customer,0)); ?></td>
						<td class="td-editDigit" title="客単価"><?php printf("%s",number_format($cavg,0)); ?></td>
				</tr>
<?php
	}
		$wSum = $tSum? ((float)$rSum/(float)$tSum)*100.0:0;
		$pSum = $lSum? ((float)$rSum/(float)$lSum)*100.0:0;
?>
				<tr>
						<td class="th-edit">計/平均</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($lSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($pSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($tSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($wSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($bSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($mSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($cSum,0)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
				</tr>
		</table>
<?php
if($whoami['perm']&PERM_EXPORT_CSV){
?>
		<p>※CSV形式 (Ctrl-Aで選択 → xxxx.CSVとして保存)</p>
		<p>
				<label>
				<textarea name="csv" cols="128" rows="12" readonly="readonly" class="microFont" id="csv"><?php printf("%s",implode("\n",$csv)); ?></textarea>
				</label>
		</p>
<?php
}
?>
</form>
<script type="text/javascript">
window.onload = function()
{
	var perm_report_full = <?php printf("%d",$whoami['perm']&PERM_REPORT_FULL); ?>;
	var pac = <?php printf("%d",count($plusarea)); ?>;

	var elm = document.getElementsByTagName('TR');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		if(elm[a].getAttribute("elmtype")=='shop'){
			if(elm[a].getAttribute("win")<100){
				elm[a].className = 'notWin';
			}
		}
		else if(elm[a].getAttribute("elmtype")=='plusarea' && (perm_report_full || pac==0)){
			elm[a].className = 'notDisplay';
		}
	}
	leapAdjust(document.menu,'ps');
	leapAdjust(document.menu,'pe');
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	}
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</body>
</html>
