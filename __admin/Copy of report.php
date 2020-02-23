<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>shop</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript" src="../common.js"></script>
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
//
		$query = sprintf("select cast('%s' as date) - cast('%s' as date) as days",$pe,$ps);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$days = $qo['days'];
//
		$query = sprintf("select shop.*,currency.rate from shop,currency where shop.id='%d' and shop.currency=currency.id",$id);
		$sr = pg_query($handle,$query);
		$so = pg_fetch_array($sr);
		$dName = getDivisionName($handle,$so['division']);
		$aName = getAreaName($handle,$so['area']);

//printf("%s %s %s <p>",$so['name'],$dName,$aName);
//
//		$query = sprintf("select daily.*,date_part('dow',daily.yyyymmdd) as week from daily where daily.shop='%d' and daily.yyyymmdd between '%s' and '%s' order by daily.yyyymmdd",$id,$ps,$pe);
		$query = sprintf("select daily.*,date_part('dow',daily.yyyymmdd) as week from daily where daily.shop='%d' and (daily.yyyymmdd between '%s' and cast(cast('%s' as date)+cast('%d day' as interval) as date)) order by daily.yyyymmdd",$id,$ps,$ps,$days);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<p class="title1"><a href="javascript:history.back()"><?php printf("%s (%s : %s)",$so['name'],$dName,$aName); ?> - 詳細 (クリックで戻る)</a></p>

<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
<table width="7%">
		<tr>
				<td class="th-edit" width="3%">日付</td>
				<td class="th-editDigit" width="3%">売上</td>
				<td class="th-editDigit" width="94%">売上累計</td>
				<td class="th-editDigit" width="94%">会員</td>
				<td class="th-editDigit" width="94%">一般</td>
				<td class="th-edit" width="94%">特記事項</td>
		</tr>
<?php
		$rSum = 0;
		$mSum = 0;
		$vSum = 0;
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$result = $qo['result']*$so['rate'];
			$member = $qo['member'];
			$visitor = $qo['visitor'];
			$rSum += $result;
			$mSum += $member;
			$vSum += $visitor;
?>
		<tr>
				<td class="td-edit" week="<?php printf("%d",$qo['week']); ?>"><?php printf("%s",dt2JP($qo['yyyymmdd'])); ?></td>
				<td class="td-editDigit"><?php printf("%s",number_format($result,2)); ?></td>
				<td class="td-editDigit"><?php printf("%s",number_format($rSum,2)); ?></td>
				<td class="td-editDigit"><?php printf("%s",number_format($member)); ?></td>
				<td class="td-editDigit"><?php printf("%s",number_format($visitor)); ?></td>
				<td class="td-edit"><?php printf("%s",$qo['remark']!=""? nl2br($qo['remark']):"　"); ?></td>
		</tr>
<?php
		}
?>
		<tr>
				<td class="th-edit" width="3%">計</td>
				<td class="th-edit" width="3%">&nbsp;</td>
				<td class="th-editDigit" width="94%"><?php printf("%s",number_format($rSum,2)); ?></td>
				<td class="th-editDigit" width="94%"><?php printf("%s",number_format($mSum,0)); ?></td>
				<td class="th-editDigit" width="94%"><?php printf("%s",number_format($vSum,0)); ?></td>
				<td class="th-edit" width="94%">&nbsp;</td>
		</tr>
</table>
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
				break;
		}
	}
}
</script>
<?php
		break;
//--------------------------------------------------------------------
//--------------------------------------------------------------------
	default:
//
		$__oList = array(
			array('name'=>'売上(降順)','text'=>'result desc,dw desc,aw desc,name'),
			array('name'=>'売上(昇順)','text'=>'result,dw desc,aw desc,name'),
			array('name'=>'エリア','text'=>'aw desc,result desc'),
			array('name'=>'開店時期(降順)','text'=>'open desc,dw desc,aw desc,name'),
			array('name'=>'開店時期(昇順)','text'=>'open,dw desc,aw desc,name'),
		);
//
		$query = sprintf("SELECT max(date_part('year',age(open))) from shop where vf=true");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$__yLong = $qo['max'];
		
		$query = sprintf("select min(yyyymmdd),max(yyyymmdd) from daily");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$__dMin = explode("-",$qo['min']); $__dMin = $__dMin[0];
		$__dMax = explode("-",$qo['max']); $__dMax = $__dMax[0];
		if($whoami['perm']&PERM_REPORT_FULL){
			$query = sprintf("select * from division where vf=true and id in (select distinct division from shop where vf=true) order by weight desc");
		}
		else{
			$query = sprintf("select * from division where id='%d'",$whoami['division']);
		}
		$dr = pg_query($handle,$query);
		$ds = pg_num_rows($dr);
		$__divMaster = array();
		for($a=0; $a<$ds; $a++){
			$do = pg_fetch_array($dr,$a);
			$name = getDivisionName($handle,$do['id']);
			$__divMaster[] = array('id'=>$do['id'],'name'=>$name);
		}
		if(isset($_REQUEST['exec'])){
			$ps = $_REQUEST['ps'];
			$pe = $_REQUEST['pe'];
			$division = $_REQUEST['division'];
			$area = $_REQUEST['area'];
			$yLong = $_REQUEST['yLong'];
			$order = $_REQUEST['order'];
		}
		else{
			$ps = $tt;
			$pe = $tt;
			$division = array();
			for($a=0,$b=count($__divMaster); $b--; $a++){
				$division[] = $__divMaster[$a]['id'];
			}
			$area = 0;
			$yLong = $__yLong;
			$order = 0;
		}
?>
<p class="title1">レポート

<script type="text/javascript">
function __setLD(F)
{
<?php
		$query = sprintf("select cast(now()+'-1 day' as date)"); // 昨日
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$_ps = $_pe = explode("-",$qo['date']);
		for($a=0; $a<3; $a++){
			$_ps[$a] = sprintf("%d",$_ps[$a]);
			$_pe[$a] = sprintf("%d",$_pe[$a]);
		}
?>
	var a;
	var ps = new Array(<?php printf("%s",implode(',',$_ps)); ?>);
	var pe = new Array(<?php printf("%s",implode(',',$_pe)); ?>);
	for(a=0; a<3; a++){
		F.elements['ps['+a+']'].value = ps[a]; leapAdjust(F,'ps');
		F.elements['pe['+a+']'].value = pe[a]; leapAdjust(F,'pe');
	}
}
		</script>
<script type="text/javascript">
function __setTM(F)
{
<?php
		$ooo = explode("-",date("Y-m-d"));
		$_ps = array($ooo[0],$ooo[1],1);
		$_pe = array($ooo[0],$ooo[1],$ooo[2]);
		for($a=0; $a<3; $a++){
			$_ps[$a] = sprintf("%d",$_ps[$a]);
			$_pe[$a] = sprintf("%d",$_pe[$a]);
		}
?>
	var a;
	var ps = new Array(<?php printf("%s",implode(',',$_ps)); ?>);
	var pe = new Array(<?php printf("%s",implode(',',$_pe)); ?>);
	for(a=0; a<3; a++){
		F.elements['ps['+a+']'].value = ps[a]; leapAdjust(F,'ps');
		F.elements['pe['+a+']'].value = pe[a]; leapAdjust(F,'pe');
	}
}
		</script>
<script type="text/javascript">
function __setLM(F)
{
<?php
		$query = sprintf("select cast(now()+'-%d day' as date)",$tt[2]); // 先月
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$ooo = explode("-",date("Y-m-t",strtotime($qo['date'])));
		$_ps = array($ooo[0],$ooo[1],1);
		$_pe = array($ooo[0],$ooo[1],$ooo[2]);
		for($a=0; $a<3; $a++){
			$_ps[$a] = sprintf("%d",$_ps[$a]);
			$_pe[$a] = sprintf("%d",$_pe[$a]);
		}
?>
	var a;
	var ps = new Array(<?php printf("%s",implode(',',$_ps)); ?>);
	var pe = new Array(<?php printf("%s",implode(',',$_pe)); ?>);
	for(a=0; a<3; a++){
		F.elements['ps['+a+']'].value = ps[a]; leapAdjust(F,'ps');
		F.elements['pe['+a+']'].value = pe[a]; leapAdjust(F,'pe');
	}
}
		</script>
<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	var a;
	var b;
	var ds;
	
	for(ds=0,a=0,b=F.elements['ds'].value; b--; a++){
		if(F.elements['division['+a+']'].checked){
			ds++;
		}
	}
	if(ds==0){
		mes[err++]="事業部を選択してください";
	}
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else return true;
}
		</script>
</p>
<form id="" name="" method="post" action="" onsubmit="return checkTheForm(this)">
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
<label><input name="setLD" type="button" id="setLD" onclick="__setLD(this.form)" value="昨日" /></label>
<label><input name="setTM" type="button" id="setTM" onclick="__setTM(this.form)" value="今月" /></label>
<label><input name="setLM" type="button" id="setLM" onclick="__setLM(this.form)" value="先月" /></label></td>
				</tr>

				<tr>
						<td class="th-edit">事業部</td>
						<td class="td-edit">
<input name="ds" type="hidden" id="ds" value="<?php printf("%d",count($__divMaster)); ?>" />
<?php
	for($a=0,$b=count($__divMaster); $b--; $a++){
		$checked = sprintf("%s",in_array($__divMaster[$a]['id'],$division)? " checked":"");
?>
<label>
<input <?php printf("%s",$checked); ?> name="division[<?php printf("%d",$a); ?>]" type="checkbox" id="division[<?php printf("%d",$a); ?>]" value="<?php printf("%d",$__divMaster[$a]['id']); ?>" />
<?php 	printf("%s",$__divMaster[$a]['name']); ?><br />
</label>
<?php
	}
?>						</td>
				</tr>
				<tr>
						<td class="th-edit">開店からの経過年数</td>
						<td class="td-edit"><label>
								<select name="yLong" id="yLong">
<?php
	for($a=1; $a<=$__yLong; $a++){
		$selected=sprintf("%s",$a==$yLong? " selected":"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
<?php
	}
?>
								</select>
						年以内</label></td>
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
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">抽出</td>
						<td class="td-edit"><input type="submit" name="exec" id="exec" value="開始" /></td>
				</tr>
		</table>
</form>
<?php
		$qq = array();
		$select = array(
			"shop.id",
			"shop.name",
			"shop.open",
			"date_part('year',age(shop.open)) as ylong",
			"division.id as did",
			"division.weight as dw",
			"area.id as aid",
			"area.weight as aw",
			"currency.rate",
		);
		$qq[] = sprintf("select %s",implode(",",$select));
		$qq[] = sprintf("from shop,division,area,currency");
		$qq[] = sprintf("where shop.vf=true and shop.division=division.id and shop.area=area.id and shop.currency=currency.id");
		$qq[] = sprintf("and date_part('year',age(shop.open))<='%d'",$yLong);
		if($division){
			$qq[] = sprintf("and shop.division in (%s)",implode(",",$division));
		}
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
//
		$ttname = "temp";
		$ttrows = array(
			"id integer DEFAULT 0 NOT NULL",
			"name character varying(256) DEFAULT ''::character varying NOT NULL",
			"open date DEFAULT now() NOT NULL",
			"dname character varying(256) DEFAULT ''::character varying NOT NULL",
			"aname character varying(256) DEFAULT ''::character varying NOT NULL",
			"dw integer DEFAULT 0 NOT NULL",
			"aw integer DEFAULT 0 NOT NULL",
			"ylong integer DEFAULT 0 NOT NULL",
			"result double precision DEFAULT 0 NOT NULL",
			"msum integer DEFAULT 0 NOT NULL",
			"vsum integer DEFAULT 0 NOT NULL",
		);
		$query = sprintf("CREATE TEMPORARY TABLE %s (%s)",$ttname,implode(",",$ttrows));
		$tr = pg_query($handle,$query);
			$__ps = sprintf("%d-%d-%d",$ps[0],$ps[1],$ps[2]);
			$__pe = sprintf("%d-%d-%d",$pe[0],$pe[1],$pe[2]);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$id = $qo['id'];
			$name = $qo['name'];
			$open = $qo['open'];
			$dname=getDivisionName($handle,$qo['did']);
			$aname=getAreaName($handle,$qo['aid']);
			$dw = $qo['dw'];
			$aw = $qo['aw'];
			$ylong = $qo['ylong'];
			$query = sprintf("select sum(result) as result,sum(member) as msum,sum(visitor) as vsum from daily where vf=true and shop='%d' and (yyyymmdd between '%s' and '%s')",$qo['id'],$__ps,$__pe);
			$sr = pg_query($handle,$query);
			$so = pg_fetch_array($sr);
			$result = $so['result']*$qo['rate'];
			$mSum = $so['msum'];
			$vSum = $so['vsum'];
			$query = sprintf("INSERT INTO %s VALUES('%d','%s','%s','%s','%s','%d','%d','%d','%f','%d','%d')",
				$ttname,
				$id,$name,$open,$dname,$aname,$dw,$aw,$ylong,$result,$mSum,$vSum);
			$tr = pg_query($handle,$query);
//printf("Query(%d) = [%s]<BR>",$tr,$query);
		}
		$query = sprintf("select * from %s order by %s",$ttname,$__oList[$order]['text']);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<p>一覧表示 (<?php printf("%s",number_format($qs)); ?>件)
		<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
</p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="17%">
				<tr>
						<td width="2%" height="18" class="th-edit">No</td>
						<td width="2%" class="th-edit">店舗</td>
						<td width="1%" class="th-edit">開店年月日</td>
						<td width="1%" class="th-edit">事業部</td>
						<td width="1%" class="th-edit">エリア</td>
						<td width="1%" class="th-edit">売上</td>
						<td width="1%" class="th-edit">M</td>
						<td width="1%" class="th-edit">V</td>
				</tr>
<?php
	$rSum = 0;
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$result = $qo['result'];
		$rSum += $result;
		$mSum = $qo['mSum'];
		$vSum = $qo['vSum'];
?>
				<tr>
						<td class="td-edit"><?php printf("%d",$a+1); ?></td>
						<td class="td-edit"><a href="report.php?mode=shop&id=<?php printf("%d",$qo['id']); ?>&ps=<?php printf("%s",$__ps); ?>&pe=<?php printf("%s",$__pe); ?>"><?php printf("%s",$qo['name']); ?></a></td>
						<td class="td-edit"><?php printf("%s",dt2JP($qo['open'])); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['dname']); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['aname']); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($result,2)); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($mSum,0)); ?></td>
						<td class="td-editDigit"><?php printf("%s",number_format($vSum,0)); ?></td>
				</tr>
<?php
	}
?>
				<tr>
						<td class="th-edit">計</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-editDigit"><?php printf("%s",number_format($rSum,2)); ?></td>
						<td class="th-editDigit">&nbsp;</td>
						<td class="th-editDigit">&nbsp;</td>
				</tr>
		</table>
</form>
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
