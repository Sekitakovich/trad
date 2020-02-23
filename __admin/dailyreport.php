<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>report - daily</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
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
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle); // var_dump($whoami);
	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	default:
/*
	このモードでは今日が昨日なのである
*/
	$query = sprintf("SELECT cast(now()+'-1 day' as date) as today");
	$qr = pg_query($handle,$query);
	$qo = pg_fetch_array($qr);
	$today = $qo['today'];
	$ta = explode("-",$today); // array

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
	function sortFunc02($a,$b)
	{
		return($b['rsum']-$a['rsum']);
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
	function sortFunc05($a,$b)
	{
		return($b['mvalue']-$a['mvalue']);
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
		return(strtotime($b['odate'])-strtotime($a['odate']));
	}
	$__oList = array(
		array('name'=>'売上','func'=>'sortFunc01'),
		array('name'=>'事業部','func'=>'sortFunc00'),
		array('name'=>'売上累計','func'=>'sortFunc02'),
		array('name'=>'達成率','func'=>'sortFunc03'),
		array('name'=>'昨対比','func'=>'sortFunc04'),
		array('name'=>'月予算','func'=>'sortFunc05'),
		array('name'=>'日割予算','func'=>'sortFunc06'),
		array('name'=>'客単価','func'=>'sortFunc07'),
		array('name'=>'開店時期','func'=>'sortFunc08'),
	);
//
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
			$order = $_REQUEST['order'];
			$__dset = $_REQUEST['dset'];
			$__aset = $_REQUEST['aset'];
			$division = $_REQUEST['division'];
			$area = $_REQUEST['area'];
			$desc = $_REQUEST['desc'];
			$fromOpen = $_REQUEST['fromOpen'];
		}
		else{
			$ps = $ta;
			$order = 0; // 内田要望
			$__dset = ($whoami['dcheck']=='t')? $dset:array();
			$__aset = ($whoami['acheck']=='t')? $aset:array();
			$division = 0;
			$area = 0;
			$desc = 't';
			$fromOpen =1;
		}
		$__psS = implode("-",$ps);
?>
<p class="title1">レポート(日報)
		
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
		<table width="43%">

				<tr>
						<td width="5%" class="th-edit">対象日</td>
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
日
<label>
<input name="exec" type="hidden" id="exec" value="go" />
</label></td>
						<td width="24%" class="th-edit">&nbsp;</td>
						<td width="24%" class="td-edit">&nbsp;</td>
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
						<td class="td-edit"><label>比較表作成の際の閾年数
								<select name="fromOpen" id="fromOpen" onchange="this.form.submit()">
<?php
	for($a=1; $a<=10; $a++){
		$selected = sprintf("%s",$a==$fromOpen? " selected":"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
<?php
	}
?>
								</select>
						年</label></td>
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
						<input type="submit" name="show" id="show" value="再表示" />
						</td>
				</tr>
		</table>
</form>
<?php
		$ut = strtotime(implode("-",$ps)); // 対象日のunix time
		$mdE = date("t",$ut); // 最終日を得る
		$dow = date("w",$ut); // 曜日

		$data = array();
		$ds = 0;

		$qq = array();
		$qq[] = sprintf("select shop.*,division.weight as dw,area.weight as aw,division.id as did,area.id as aid,date_part('year',age('%s',shop.ps)) as ys",$__psS);
		$qq[] = sprintf("from shop,division,area");
		$qq[] = sprintf("where shop.vf=true");
		$qq[] = sprintf("and shop.division=division.id");
		$qq[] = sprintf("and shop.area=area.id");
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
		}
		else{
			$ppp = array();
			if(count($__dset)){
				$tree = array();
				for($a=0,$b=count($__dset); $b--; $a++){
					$kkk = divisionTree($handle,$__dset[$a]);
					$kkk[] = $__dset[$a];
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
			$qq[] = sprintf("and (%s)",implode(" or ",$ppp));
		}
//
		$qq[] = sprintf("and (('%s' between shop.ps and shop.pe) or (shop.ps=shop.pe and '%s'>=shop.ps))",$__psS,$__psS);
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?><!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><?php
		$ds = $qs;
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$id = $qo['id'];
			$name = $qo['name'];
			$did = $qo['did'];
			$aid = $qo['aid'];
			$dw = $qo['dw'];
			$aw = $qo['aw'];
			$ys = $qo['ys']; // 開店日からの通算年
			$oDate = $qo['ps']; // 開店日
			$cDate = $qo['pe']; // 閉店日
			$dname=getDivisionName($handle,$did);
			$aname=getAreaName($handle,$aid);
// 月次売上目標
			$query = sprintf("select sum(target) as mvalue,sum(last) as lvalue from daily where vf=true and shop='%d' and yyyymmdd between '%d-%d-%d' and '%d-%d-%d'",$id,$ps[0],$ps[1],1,$ps[0],$ps[1],$mdE);
			$vr = pg_query($handle,$query);
			$vo = pg_fetch_array($vr);
			$mvalue = $vo['mvalue'];
			$lvalue = $vo['lvalue'];
// この日の売上、昨年のそれ、目標
			$query = sprintf("select * from daily where vf=true and shop='%d' and yyyymmdd='%d-%d-%d'",$id,$ps[0],$ps[1],$ps[2]);
			$vr = pg_query($handle,$query);
			$vo = pg_fetch_array($vr);
			$result = $vo['result'];
			$target = $vo['target'];
			$last = $vo['last'];
			$book = $vo['book'];
			$member = $vo['member'];
			$visitor = $vo['visitor'];
// この日までの月間売上累計
			$query = sprintf("select sum(last) as lsum,sum(result) as rsum,sum(target) as tsum,sum(book) as bsum,sum(visitor) as vsum,sum(member) as msum from daily where vf=true and shop='%d' and yyyymmdd between '%d-%d-%d' and '%d-%d-%d'",$id,$ps[0],$ps[1],1,$ps[0],$ps[1],$ps[2]);
			$vr = pg_query($handle,$query);
			$vo = pg_fetch_array($vr);
			$rSum = $vo['rsum'];
			$lSum = $vo['lsum'];
			$tSum = $vo['tsum'];
			$bSum = $vo['bsum'];
			$vSum = $vo['vsum'];
			$mSum = $vo['msum'];
			$dwin = $target? ((float)$result/(float)$target)*100.0:0; // 日毎の達成率
//			$win = $mvalue? ((float)$rSum/(float)$mvalue)*100.0:0; // 達成率の分母を月間目標額に変更(8/20)
			$win = $tSum? ((float)$rSum/(float)$tSum)*100.0:0; // 達成率の分母をやっぱ目標金額の累積に変更(8/20)
			$ppy = $lSum? ((float)$rSum/(float)$lSum)*100.0:0;
//			$ppy = $lvalue? ((float)$rSum/(float)$lvalue)*100.0:0; // 同上
			$cavg = $vSum? (int)($rSum/$vSum):0;
//
			$data[] = array(
				'id'=>$id,'name'=>$name,
				'dw'=>$dw,'did'=>$did,'sid'=>$id,
				'aw'=>$aw,'aid'=>$aid,
				'mvalue'=>$mvalue,
				'result'=>$result,'target'=>$target,'last'=>$last,'book'=>$book,'member'=>$member,'visitor'=>$visitor,
				'rsum'=>$rSum,'tsum'=>$tSum,'bsum'=>$bSum,'vsum'=>$vSum,'msum'=>$mSum,'lsum'=>$lSum,
				'win'=>$win,'ppy'=>$ppy,'dwin'=>$dwin,
				'dname'=>$dname,'aname'=>$aname,
				'cavg'=>$cavg,'ys'=>$ys,'odate'=>$oDate
			);
		}
//Var_dump::display($data);
		usort($data,$__oList[$order]['func']);
		if($desc=='f'){
			$data=array_reverse($data);
		}
?>
<form action="" method="get" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
<p class="title1">
		一覧表示 <?php printf("%s",dt2JP(implode("-",$ps))); ?> : <?php printf("%s",number_format($qs)); ?>店
		<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
		<label></label>
	<input name="ps" type="hidden" id="ps" value="<?php printf("%s",$__ps); ?>" />
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
</p>
		<table width="17%">
				<tr>
						<td width="2%" height="18" class="th-edit">No</td>
						<td width="2%" class="th-edit">店舗 
								<label></label>
								<label></label></td>
						<td width="1%" class="th-editDigit">月予算</td>
						<td width="1%" class="th-editDigit">日割予算</td>
						<td width="1%" class="th-editDigit">売上</td>
						<td width="1%" class="th-editDigit">売上累計</td>
						<td width="1%" class="th-editDigit">達成率</td>
						<td width="1%" class="th-editDigit">昨対比</td>
						<td width="95%" class="th-editDigit">取りおき</td>
						<td width="95%" class="th-editDigit">取りおき計</td>
						<td width="1%" class="th-editDigit">顧客</td>
						<td width="1%" class="th-editDigit">客数</td>
						<td width="1%" class="th-editDigit">客数累計</td>
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
	$mvSum = 0;
	$rrSum = 0;
	$ttSum = 0;
	$llSum = 0;
	$bbSum = 0;
	$vvSum = 0;

	$csvMIN = array(); // for export
	$csvMIN[] = "店舗名,月予算,日割予算,売上,売上累計,達成率,昨対比,取りおき,取りおき計,顧客,客数,客数累計,客単価";
	
	for($a=0; $a<$ds; $a++){
		$qo = $data[$a];
		$sid = $qo['sid']; // shop.id
		$ys = $qo['ys'];
		$oDate = $qo['odate'];
		$dName = $qo['dname'];
		$aName = $qo['aname'];
		$mvalue = $qo['mvalue'];
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
		$win = $qo['win'];		$dwin = $qo['dwin'];
		$ppy = $qo['ppy'];
		$mSum += $member;
		$vSum += $visitor;

		$__tSum = $qo['tsum'];
		$__lSum = $qo['lsum'];
		$__rSum = $qo['rsum'];
		$__bSum = $qo['bsum'];
		$__vSum = $qo['vsum'];

		$mvSum += $mvalue;
		$rrSum += $__rSum;
		$ttSum += $__tSum;
		$llSum += $__lSum;
		$bbSum += $__bSum;
		$vvSum += $__vSum;

		$line = array($qo['name'],$mvalue,$target,$result,$__rSum,$win,$ppy,$book,$__bSum,$member,$visitor,$__vSum,$cavg);
		$csvMIN[] = implode(",",$line);

//
?>
				<tr elmtype="shop" shop="<?php printf("%d",$sid); ?>" win="<?php printf("%d",$win); ?>" ys="<?php printf("%d",$ys); ?>">
						<td class="td-edit"><?php printf("%d",$a+1); ?></td>
						<td class="td-edit" title="<?php printf("%s : %s %s",$dName,$aName,$oDate); ?>">
						<label><?php printf("%s",$qo['name']); ?> <img src="../images/wakaba3.gif" alt="" width="10" height="14" elmtype="icon" ys="<?php printf("%d",$ys); ?>" /></label></td>
						<td class="td-editDigit" title="月予算"><?php printf("%s",number_format($mvalue,0)); ?></td>
						<td class="td-editDigit" title="日割予算"><?php printf("%s",number_format($target,0)); ?></td>
						<td class="td-editDigit" title="売上"><?php printf("%s",number_format($result,0)); ?></td>
						<td class="td-editDigit" title="売上累計"><?php printf("%s",number_format($__rSum,0)); ?></td>
						<td class="td-editDigit" title="達成率"><?php printf("%s",number_format($win,2)); ?>％</td>
						<td class="td-editDigit" title="昨対比"><?php printf("%s",number_format($ppy,2)); ?>％</td>
						<td class="td-editDigit" title="取りおき"><?php printf("%s",number_format($book,0)); ?></td>
						<td class="td-editDigit" title="取りおき計"><?php printf("%s",number_format($__bSum,0)); ?></td>
						<td class="td-editDigit" title="顧客"><?php printf("%s",number_format($member,0)); ?></td>
						<td class="td-editDigit" title="客数"><?php printf("%s",number_format($visitor,0)); ?></td>
						<td class="td-editDigit" title="客数累計"><?php printf("%s",number_format($__vSum,0)); ?></td>
						<td class="td-editDigit" title="客単価"><?php printf("%s",number_format($cavg,0)); ?></td>
				</tr>
<?php
	}
		$wSum = $ttSum? ((float)$rrSum/(float)$ttSum)*100.0:0;
		$pSum = $llSum? ((float)$rrSum/(float)$llSum)*100.0:0;
		$cSum = $vvSum? $rrSum/$vvSum:0;

		$line = array('',$mvSum,$tSum,$rSum,$rrSum,$wSum,$pSum,$bSum,$bbSum,$Sum,$vSum,$vvSum,$cSum);
		$csvMIN[] = implode(",",$line);
?>
				<tr>
						<td class="th-edit">計/平均</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($mvSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($tSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($rrSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($wSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($pSum,2)); ?>％</td>
						<td class="th-editDigit"><?php printf("%s",number_format($bSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($bbSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($mSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($vvSum,0)); ?></td>
						<td class="th-editDigit"><?php printf("%s",number_format($cSum,0)); ?></td>
				</tr>
		</table>
		<br />
		<table width="40%">
				<tr>
						<th width="5%" nowrap="nowrap" class="th-edit">比較表</th>
						<th width="7%" nowrap="nowrap" class="th-edit">当月予算計</th>
						<th width="9%" nowrap="nowrap" class="th-edit">当月売上累計</th>
						<th width="9%" nowrap="nowrap" class="th-edit">昨年売上累計</th>
						<th width="6%" nowrap="nowrap" class="th-edit">差額累計</th>
						<th width="7%" nowrap="nowrap" class="th-edit">当月達成率</th>
						<th width="57%" nowrap="nowrap" class="th-edit">昨年比率</th>
				</tr>
<?php
	$mmSum = array(0,0);
	$rrSum = array(0,0);
	$llSum = array(0,0);
	for($a=0; $a<$ds; $a++){
		$src = $data[$a];
		$dst = ($src['ys']<$fromOpen)? 1:0;
		$mmSum[$dst]+=$src['mvalue'];
		$rrSum[$dst]+=$src['rsum'];
		$llSum[$dst]+=$src['lsum'];
	}
	$wSum = array(0,0);
	$lSum = array(0,0);
	$wSum[0] = $mmSum[0]? ((float)$rrSum[0]/(float)$mmSum[0])*100.0:0;
	$lSum[0] = $llSum[0]? ((float)$rrSum[0]/(float)$llSum[0])*100.0:0;
?>
				<tr>
						<td nowrap="nowrap" class="td-edit">オープン<?php printf("%d",$fromOpen); ?>年以上</th>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($mmSum[0],0)); ?></th>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($rrSum[0],0)); ?></th>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($llSum[0],0)); ?></th>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($rrSum[0]-$llSum[0],0)); ?></th>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%3.2f",$wSum[0]); ?>％</th>
				<td nowrap="nowrap" class="td-editDigit"><?php printf("%3.2f",$lSum[0]); ?>％&nbsp;</th>				
				</tr>
				<tr>
						<td nowrap="nowrap" class="td-edit">オープン<?php printf("%d",$fromOpen); ?>年未満</th>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($mmSum[1],0)); ?></th>
						<td nowrap="nowrap" class="td-editDigit"><?php printf("%s",number_format($rrSum[1],0)); ?></th>
						<td nowrap="nowrap" class="td-editDigit">&nbsp;</th>
						<td nowrap="nowrap" class="td-editDigit">&nbsp;</th>
						<td nowrap="nowrap" class="td-editDigit">&nbsp;</th>
				<td nowrap="nowrap" class="td-editDigit">&nbsp;</th>				</tr>
		</table>
		<p>&nbsp;</p>
</form>
<script type="text/javascript">
window.onload = function()
{
	var perm_report_full = <?php printf("%d",$whoami['perm']&PERM_REPORT_FULL); ?>;
	var fromOpen = <?php printf("%d",$fromOpen); ?>;
	
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
		else if(elm[a].getAttribute("elmtype")=='adsets' && perm_report_full!=0){
			elm[a].className = 'notDisplay';
		}
		else if(elm[a].getAttribute("elmtype")=='full' && perm_report_full==0){
			elm[a].className = 'notDisplay';
		}
	}
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
	leapAdjust(document.menu,'ps');
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
