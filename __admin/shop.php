<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>shop</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript" src="../common.js"></script>
<script type="text/javascript" src="../php.js"></script>
<script type="text/javascript" src="../prototype.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	function isRefer($handle,$id)
	{
		$refer = 0;
		$table = array("daily","staff");	
		for($a=0,$b=count($table); $b--; $a++){
			$query = sprintf("select count(*) from %s where vf=true and shop='%d'",$table[$a],$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			if($refer = $qo['count']){
				break;
			}
		}
		return($refer);
	}

	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	case "psave":
		$shop = $_REQUEST['id'];
		$yy = $_REQUEST['yy'];
		$mm = $_REQUEST['mm'];
		$days = $_REQUEST['days'];
		$rate = $_REQUEST['rate'];
		$target = $_REQUEST['target'];
		$last = $_REQUEST['last'];
		$result = $_REQUEST['result'];
		$book = $_REQUEST['book'];
		$member = $_REQUEST['member'];
		$visitor = $_REQUEST['visitor'];
		$open = $_REQUEST['open'];
		$modify = $_REQUEST['modify'];
		$note =$_REQUEST['note'];

		for($a=0,$b=$days,$dd=1; $b--; $a++,$dd++){
			if($modify[$a]=='t'){
				$query = sprintf("select id from daily where vf=true and shop='%d' and yyyymmdd='%d-%d-%d'",
					$shop,$yy,$mm,$dd);
				$qr = pg_query($handle,$query);
				$qs = pg_num_rows($qr);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
				if($qs){
					$qo = pg_fetch_array($qr);
					$id = $qo['id'];
				}
				else{
					$query = sprintf("select max(id) from daily");
					$qr = pg_query($handle,$query);
	?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
					$qo = pg_fetch_array($qr);
					$id=$qo['max']+1;
					$query = sprintf("insert into daily(id,shop,yyyymmdd) values('%d','%d','%d-%d-%d')",$id,$shop,$yy,$mm,$dd);
					$qr = pg_query($handle,$query);
	?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
				}
				$set = array();
				$set[] = sprintf("rbase='%f'",$result[$a]);
				$set[] = sprintf("tbase='%f'",$target[$a]);
				$set[] = sprintf("lbase='%f'",$last[$a]);
				$set[] = sprintf("bbase='%f'",$book[$a]);
				$set[] = sprintf("result='%d'",$result[$a]*$rate);
				$set[] = sprintf("target='%d'",$target[$a]*$rate);
				$set[] = sprintf("last='%d'",$last[$a]*$rate);
				$set[] = sprintf("book='%d'",$book[$a]*$rate);
				$set[] = sprintf("member='%d'",$member[$a]);
				$set[] = sprintf("visitor='%d'",$visitor[$a]);
				$set[] = sprintf("open=%s",isset($open[$a])? "false":"true");
				$set[] = sprintf("note='%s'",pg_escape_string($note[$a]));
				$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
				$query = sprintf("update daily set %s where id='%d'",implode(",",$set),$id);
				$qr = pg_query($handle,$query);
	?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			}
		}
?>
<a href="shop.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "daily":
		$id = $_REQUEST['id'];
		$ym = explode("-",$_REQUEST['ym']);
		$thisY = $ym[0];
		$thisM = $ym[1];
		$query = sprintf("select shop.*,currency.rate,currency.name as cname from shop,currency where shop.id='%d' and shop.currency=currency.id",$id);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$name = $qo['name'];
		$cname = $qo['cname'];
		$rate = $qo['rate'];
?>
<p class="title1">売上データの編集 <?php printf("%s - %d年%d月 単位:%s (×%.2f円)",$name,$thisY,$thisM,$cname,$rate); ?></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list2" onsubmit="return checkTheList(this)">
		<script type="text/javascript">
function setSum(F,offset)
{
	var days = parseInt(F.elements['days'].value);
	var a;
	var d;
	var tSum = 0;
	var lSum = 0;
	var rSum = 0;
	var bSum = 0;
	var mSum = 0;
	var vSum = 0;

	var dst = 'modify['+offset+']';
	F.elements[dst].value = 't';
	
	for(a=0,d=1; a<days; a++,d++){
//
		var dst = 'target['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 目標額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			tSum += val;
		}
//
		var dst = 'last['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 昨年度の売上金額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			lSum += val;
		}
//
		var dst = 'result['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 売上金額に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			rSum += val;
		}
//
		var dst = 'book['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 取りおきに有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			bSum += val;
		}
//
		var dst = 'member['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 顧客に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			mSum += val;
		}
//
		var dst = 'visitor['+a+']';
		var val = parseInt(F.elements[dst].value); 
		if(isNaN(val)){
			alert(d+'日: 客数に有効な数値を入力してください');
			F.elements[dst].value = 0;
		}
		else{
			vSum += val;
		}
	}
	F.elements['tSum'].value = tSum;
	F.elements['lSum'].value = lSum;
	F.elements['rSum'].value = rSum;
	F.elements['bSum'].value = bSum;
	F.elements['mSum'].value = mSum;
	F.elements['vSum'].value = vSum;
//
	var day = document.getElementById(sprintf("day%02d",offset));
	day.style.backgroundColor = "#8888FF";
	F.elements['exec'].disabled = '';
//	
}
		</script>
		<script language="JavaScript" type="text/javascript">
function checkTheList(F)
{
	var mes = new Array();
	var err = 0;
	var yy = parseInt(F.elements['yy'].value);
	var mm = parseInt(F.elements['mm'].value);
	var days = parseInt(F.elements['days'].value);
	var rSum = parseInt(F.elements['rSum'].value);
	var tSum = parseInt(F.elements['tSum'].value);
	var lSum = parseInt(F.elements['lSum'].value);
	var bSum = parseInt(F.elements['bSum'].value);
	var mSum = parseInt(F.elements['mSum'].value);
	var vSum = parseInt(F.elements['vSum'].value);

	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else{
		var ask = new Array();
		var a;
		var b;
		var c;
		var rs;
		var cname = '<?php printf("%s",$cname); ?>';

		a=0;
		rs=0;
//
		for(b=0,c=1; b<days; b++,c++){
			if(F.elements['modify['+b+']'].value=='t'){
				rs++;
				var tV = parseInt(F.elements['target['+b+']'].value);
				var lV = parseInt(F.elements['last['+b+']'].value);
				var rV = parseInt(F.elements['result['+b+']'].value);
				var bV = parseInt(F.elements['book['+b+']'].value);
				var isClose = F.elements['open['+b+']'].checked;
				if(isClose == false && (tV==0 || lV==0 || rV==0 || bV==0)){
					ask[a++] = sprintf("警告：%2d日 金額0の項目があります",c);
				}
			}
		}
//
		ask[a++] = sprintf("%d年%d月 (対象レコード数:%d)",yy,mm,rs);
		ask[a++] = sprintf("昨年計:%s %s",number_format(lSum,0),cname);
		ask[a++] = sprintf("予算計:%s %s",number_format(tSum,0),cname);
		ask[a++] = sprintf("売上計:%s %s",number_format(rSum,0),cname);
		ask[a++] = sprintf("取りおき計:%s %s",number_format(bSum,0),cname);
		ask[a++] = sprintf("顧客:%s名",number_format(mSum,0));
		ask[a++] = sprintf("客数:%s名",number_format(vSum,0));
		ask[a++] = sprintf("");
		ask[a++] = sprintf("この内容で登録します。よろしいですか?");
		return confirm(ask.join('\n'));
	}
}
		</script>
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">日</td>
						<td class="th-editDigit" width="3%">曜</td>
						<td class="th-editDigit" width="3%">休</td>
						<td class="th-editDigit" width="3%">昨年</td>
						<td width="1%" class="th-editDigit">予算</td>
						<td width="95%" class="th-editDigit">売上</td>
						<td width="95%" class="th-editDigit">取りおき</td>
						<td width="95%" class="th-editDigit">顧客</td>
						<td width="95%" class="th-editDigit">客数</td>
						<td width="95%" class="th-edit">特記事項</td>
				</tr>
				<?php
	$days = date("t",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // この月が何日あるか
	$dow = date("w",strtotime(sprintf("%d-%d-1",$thisY,$thisM))); // 
	$tu = strtotime(date("Y-m-d"));
	$week = array('日','月','火','水','木','金','土');

	$lsum = 0;
	$tSum = 0;
	$rSum = 0;
	$bSum = 0;
	$mSum = 0;
	$vSum = 0;

	for($a=0,$d=1; $d<=$days; $a++,$d++,$dow++){
		$query = sprintf("select * from daily where vf=true and shop='%d' and yyyymmdd='%d-%d-%d'",$id,$thisY,$thisM,$d);
		$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		if($qs = pg_num_rows($qr)){
			$qo = pg_fetch_array($qr);
			$target = $qo['tbase'];
			$result = $qo['rbase'];
			$book = $qo['bbase'];
			$last = $qo['lbase'];
			$member = $qo['member'];
			$visitor = $qo['visitor'];
			$note = $qo['note']? $qo['note']:'　';
			$open = $qo['open'];
		}
		else{
			$target = 0;
			$result = 0;
			$book = 0;
			$last = 0;
			$member = 0;
			$visitor = 0;
			$note = '　';
			$open = 't';
		}
		$cavg = $visitor? $result/$visitor:0;
		$win = $target? ((float)$result/(float)$target)*100.0:0;
		$ppy = $last? ((float)$result/(float)$last)*100.0:0;

		$lSum += $last;
		$tSum += $target;
		$rSum += $result;
		$bSum += $book;
		$mSum += $member;
		$vSum += $visitor;

		$passed = strtotime(sprintf("%d-%d-%d",$thisY,$thisM,$d))<=$tu? 1:0;

//
		$query = sprintf("select * from holiday where vf=true and yyyymmdd='%d-%d-%d'",$thisY,$thisM,$d);
		$hr = pg_query($handle,$query);
		$hs = pg_num_rows($hr);
		if($hs){
			$ho = pg_fetch_array($hr);
		}
		$isHoliday = $hs;
		$hName = sprintf("%s",$hs? $ho['name']:"");
		$oc = $open=='f'? "checked=\"checked\"":"";
//
?>
				<tr id="<?php printf("day%02d",$a); ?>">
						<td class="td-editDigit" dow="<?php printf("%s",$dow%7); ?>" isholiday="<?php printf("%d",$isHoliday); ?>"><?php printf("%d",$d); ?></td>
						<td class="td-editDigit" dow="<?php printf("%s",$dow%7); ?>"><?php printf("%s",$week[$dow%7]); ?></td>
						<td class="td-editDigit"><input onchange="setSum(this.form,<?php printf("%d",$a); ?>)" <?php printf("%s",$oc); ?> name="open[<?php printf("%d",$a); ?>]" type="checkbox" id="open[<?php printf("%d",$a); ?>]" value="t"  /></td>
						<td class="td-editDigit"><input name="last[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="last[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$last); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><input name="target[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="target[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$target); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><label>
						<input name="result[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="result[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$result); ?>" size="8" maxlength="8" />
						</label></td>
						<td class="td-editDigit"><input name="book[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="book[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$book); ?>" size="8" maxlength="8" /></td>
						<td class="td-editDigit"><input name="member[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="member[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$member); ?>" size="3" maxlength="8" /></td>
						<td class="td-editDigit"><input name="visitor[<?php printf("%d",$a); ?>]" type="text" class="input-Digit" id="visitor[<?php printf("%d",$a); ?>]" onchange="setSum(this.form,<?php printf("%d",$a); ?>)" value="<?php printf("%d",$visitor); ?>" size="3" maxlength="8" /></td>
						<td class="td-editDigit"><label>
						<textarea onchange="setSum(this.form,<?php printf("%d",$a); ?>)" name="note[<?php printf("%d",$a); ?>]" cols="64" rows="4" id="note[<?php printf("%d",$a); ?>]"><?php printf("%s",$note); ?></textarea>
						</label>
<input name="modify[<?php printf("%d",$a); ?>]" type="hidden" id="modify[<?php printf("%d",$a); ?>]" value="f" />
						</td>
				</tr>
				<?php
	}
?>
				<tr>
						<td class="th-edit">計</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%">&nbsp;</td>
						<td class="th-editDigit" width="3%"><input name="lSum" type="text" class="input-Digit" id="lSum" value="<?php printf("%d",$lSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="tSum" type="text" class="input-Digit" id="tSum" value="<?php printf("%d",$tSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="rSum" type="text" class="input-Digit" id="rSum" value="<?php printf("%d",$rSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="bSum" type="text" class="input-Digit" id="bSum" value="<?php printf("%d",$bSum); ?>" size="12" maxlength="12" readonly="true" /></td>
						<td class="th-editDigit"><input name="mSum" type="text" class="input-Digit" id="mSum" value="<?php printf("%d",$mSum); ?>" size="6" maxlength="6" readonly="true" /></td>
						<td class="th-editDigit"><input name="vSum" type="text" class="input-Digit" id="vSum" value="<?php printf("%d",$vSum); ?>" size="6" maxlength="6" readonly="true" /></td>
						<td class="th-editDigit">
						<input name="exec" type="submit" disabled="disabled" id="exec" value="登録" /></td>
				</tr>
		</table>
		<label>
		<input name="mode" type="hidden" id="mode" value="psave" />
		</label>
		<label>
		<input name="yy" type="hidden" id="yy" value="<?php printf("%d",$thisY);?>" />
		</label>
		<label>
		<input name="mm" type="hidden" id="mm" value="<?php printf("%d",$thisM);?>" />
		</label>
		<label>
		<input name="days" type="hidden" id="days" value="<?php printf("%d",$days); ?>" />
		</label>
		<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
		<input name="rate" type="hidden" id="rate" value="<?php printf("%f",$rate); ?>" />
		<br />
		<script type="text/javascript">
window.onload = function()
{
//
	var elm = document.getElementsByTagName('TD');
	var a;
	var b;
	for(a=0,b=elm.length; b--; a++){
		switch(elm[a].getAttribute("dow")){
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
}
		</script>
</form>
<?php
		break;
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from shop");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into shop(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
// staffの自動生成
			$query = sprintf("select max(id) from staff");
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$sid=$qo['max']+1;
			$account = $_REQUEST['account'];
			if($account==''){
				$account = sprintf("%06d",$sid);
			}
			$password = $_REQUEST['password'];
			$query = sprintf("insert into staff(id,istaff,ustaff) values('%d','%d','%d')",$sid,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$query = sprintf("update staff set shop='%d',dset='{%d}',aset='{%d}',account='%s',password='%s',nickname='%s',name[1]='%s',name[2]='%s',kana[1]='%s',kana[2]='%s' where id='%d'",
	$id,
	$_REQUEST['division'],
	$_REQUEST['area'],
	$account,
	$password,
	$account,
	$account,
	$account,
	$account,
	$account,
	$sid);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
//
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("division='%d'",$_REQUEST['division']);
		$set[] = sprintf("area='%d'",$_REQUEST['area']);
		$set[] = sprintf("currency='%d'",$_REQUEST['currency']);
		$set[] = sprintf("ps='%s'",implode("-",$_REQUEST['ps']));
		$set[] = sprintf("pe='%s'",implode("-",$_REQUEST['pe']));
		if(count($_REQUEST['brand'])){
			$set[] = sprintf("brand='{%s}'",implode(",",$_REQUEST['brand']));
		}
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update shop set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update shop set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="shop.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if($id=$_REQUEST['id']){
			$query = sprintf("select shop.*,currency.rate from shop,currency where shop.id='%d' and shop.currency=currency.id",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$division = $qo['division'];
			$area = $qo['area'];
			$ps = explode("-",$qo['ps']);
			$pe = explode("-",$qo['pe']);
			$currency = $qo['currency'];
			$rate = $qo['rate'];
			$brand = getPGSQLarray($qo['brand']);

			$refer = isRefer($handle,$id);
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$refer = 0;
			$division = 0;
			$area = 0;
			$ps = $tt;
			$pe = $tt;
			$currency = 0;
			$rate = 0; // 無意味
			$brand = array();
		}
?>
<p class="title1">店舗：編集</p>
<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須です";
	}
	if(F.elements['currency'].value==0){
		mes[err++] = "通貨設定は必須です";
	}
	if(F.elements['division'].value==0){
		mes[err++] = "事業部を選択してください";
	}
	if(F.elements['area'].value==0){
		mes[err++] = "エリアを選択してください";
	}
	if(F.elements['id'].value==0){ // 新規登録
		if(F.elements['password'].value==''){
			mes[err++] = "パスワードは必須です";
		}
	}
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else if(F.elements['delete'].checked){
		return confirm('このレコードを削除しますか?');
	}
	else return true;
}
		</script>
<br />
<br />
*新規登録の場合のみアカウント：パスワードのフィールドが出る<br />
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">名称</td>
						<td width="24%" class="td-edit"><label>
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="64" maxlength="256" />
						</label></td>
						<td width="7%" class="th-edit">通貨</td>
						<td width="64%" class="td-edit"><select name="currency" id="currency">
								<option value="0">-- 選択してください --</option>
								<?php
		$query = sprintf("select * from currency where vf=true order by id");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$currency? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s (×%s)",$qo['name'],number_format($qo['rate'],3)); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">事業部</td>
						<td class="td-edit"><label>
						<select name="division" id="division">
								<option value="0">-- 選択してください --</option>
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
						</label></td>
						<td class="th-edit">エリア</td>
						<td class="td-edit"><select name="area" id="area">
								<option value="0">-- 選択してください --</option>
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
						</select></td>
				</tr>
				<tr elmtype="createStaff">
						<td class="th-edit">アカウント</td>
						<td class="td-edit"><label>
								<input name="account" type="text" id="account" size="6" maxlength="6" />
						</label>
						*未入力なら自動生成</td>
						<td class="th-edit">パスワード</td>
						<td class="td-edit"><label><input name="password" type="password" id="password" size="6" maxlength="6" />
						</label>
						*未入力の場合ログイン不可</td>
				</tr>
				<tr>
						<td class="th-edit">開店日</td>
						<td class="td-edit"><select name="ps[0]" id="ps[0]" onchange="leapAdjust(this.form,'ps')">
								<?php
for($a=1987; $a<=$tt[0]+1; $a++){
	$selected=sprintf("%s",$a==$ps[0]? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
								<?php
}
?>
						</select>
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
</select>
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
</select>
日</td>
						<td class="th-edit">閉店日</td>
						<td class="td-edit"><select name="pe[0]" id="pe[0]" onchange="leapAdjust(this.form,'pe')">
								<?php
for($a=1987; $a<=$tt[0]+1; $a++){
	$selected=sprintf("%s",$a==$pe[0]? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%d",$a); ?></option>
								<?php
}
?>
						</select>
年
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
月
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
*閉店日＝開店日とすることで閉店日未定扱いとする </td>
				</tr>
				<tr>
						<td class="th-edit">取扱ブランド(ex)</td>
						<td class="td-edit">
<?php
	$query = sprintf("select brand.* from brand where brand.vf=true and brand.exclusive=true order by brand.weight desc,brand.name");
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$checked = sprintf("%s",in_array($qo['id'],$brand)? " checked":"");
?>
								<label><input name="brand[]" type="checkbox" id="brand[]" value="<?php printf("%d",$qo['id']); ?>" <?php printf("%s",$checked);?>>
								<?php printf("%s",$qo['name']); ?></label><br />
<?php
	}
?>						</td>
						<td class="th-edit">取扱ブランド(並)</td>
						<td class="td-edit"><?php
	$query = sprintf("select brand.* from brand where brand.vf=true and brand.exclusive=false order by brand.weight desc,brand.name");
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$checked = sprintf("%s",in_array($qo['id'],$brand)? " checked":"");
?>
								<label>
								<input name="brand[]" type="checkbox" id="brand[]" value="<?php printf("%d",$qo['id']); ?>" <?php printf("%s",$checked);?> />
								<?php printf("%s",$qo['name']); ?></label>
								<br />
								<?php
	}
?></td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td class="td-edit"><textarea name="remark" cols="32" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea></td>
						<td class="th-edit">登録</td>
						<td class="td-edit"><input name="exec" type="submit" id="exec" value="実行" />
								<input name="mode" type="hidden" id="mode" value="save" />
								<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
								<span id="void">
								<input name="delete" type="checkbox" id="delete" value="t" />
削除する</span></td>
				</tr>
		</table>
</form>
<?php
	if($id){
?>
<form id="analyze" name="analyze" method="post" action="">
<p class="title1">(参考：解析資料)</p>
		<table width="12%">
		<tr>
				<th class="th-edit">W</th>
				<th class="th-edit">avg</th>
				<th class="th-edit">max</th>
				<th class="th-edit">min</th>
				<th class="th-edit">sample(s)</th>
		</tr>
<?php
	$dow = array(
		array('value'=>1,'name'=>'月'),
		array('value'=>2,'name'=>'火'),
		array('value'=>3,'name'=>'水'),
		array('value'=>4,'name'=>'木'),
		array('value'=>5,'name'=>'金'),
		array('value'=>6,'name'=>'土'),
		array('value'=>0,'name'=>'日'),
	);
	for($a=0,$b=count($dow); $b--; $a++){
		$query = sprintf("SELECT count(*),avg(result),max(result),min(result) from daily where vf=true and open=true and entered=true and shop='%d' and date_part('dow',yyyymmdd)='%d'",$id,$dow[$a]['value']);
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
?>
<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
		<tr>
				<td width="2%" class="td-edit"><?php printf("%s",$dow[$a]['name']); ?></td>
				<td width="3%" class="td-editDigit">￥<?php printf("%s",number_format($qo['avg'],0)); ?></td>
				<td width="95%" class="td-editDigit">￥<?php printf("%s",number_format($qo['max'],0)); ?></td>
				<td width="95%" class="td-editDigit">￥<?php printf("%s",number_format($qo['min'],0)); ?></td>
				<td width="95%" class="td-editDigit"><?php printf("%d",$qo['count']); ?></td>
		</tr>
<?php
	}
?>
</table>
</form>
<?php
	}
?>
<script type="text/javascript">
window.onload = function(){
	var refer = <?php printf("%d",$refer); ?>;
	var id = <?php printf("%d",$id); ?>;
	if(refer || id==0){
		var elm = document.getElementById('void');
		elm.className = 'notDisplay';
	}
	var edit = <?php printf("%s",($whoami['perm']&PERM_MASTER_EDIT)? "true":"false"); ?>;
	if(edit==false){
		Form.disable('edit'); // see prototype.js
	}
//
	if(document.edit.elements['id'].value!='0'){
		var elm = document.getElementsByTagName('TR');
		for(a=0,b=elm.length; b--; a++){
			switch(elm[a].getAttribute('elmtype')){
				case 'createStaff':
					elm[a].className = 'notDisplay';
					break;
				default:
					break;
			}
		}
	}
//	
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'shop.udate desc'),
			array('name'=>'事業部','text'=>'division.weight desc,division.name,area.weight desc,shop.name'),
			array('name'=>'エリア','text'=>'area.weight desc,area.name,division.weight desc,shop.name'),
			array('name'=>'店名','text'=>'shop.name,division.weight desc,area.weight desc'),
			array('name'=>'開店時期(降順)','text'=>'shop.ps desc,area.weight desc,division.weight desc,shop.name'),
			array('name'=>'開店時期(昇順)','text'=>'shop.ps,area.weight desc,division.weight desc,shop.name'),
		);

		if(isset($_REQUEST['exec'])){
			$division = $_REQUEST['division'];
			$area = $_REQUEST['area'];
			$order = $_REQUEST['order'];
			$_SESSION['shop']=array('division'=>$division,'area'=>$area,'order'=>$order);
		}
		else if(isset($_SESSION['shop'])){
			$division = $_SESSION['shop']['division'];
			$area = $_SESSION['shop']['area'];
			$order = $_SESSION['shop']['order'];
		}
		else{
			$division = 0;
			$area = 0;
			$order =0;
		}
?>
<p class="title1">店舗検索 <a href="shop.php?mode=edit">新規登録</a></p>
<form id="" name="" method="post" action="">
		<table width="44%">

				<tr>
						<td width="5%" class="th-edit">事業部</td>
						<td width="24%" class="td-edit"><label>
								<select name="division" id="division" onchange="this.form.submit()">
										<option value="0">-- 全て --</option>
										<?php
//		$query = sprintf("select * from division where vf=true and id in (SELECT distinct division from shop where vf=true) order by weight desc");
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
								以下
						</label></td>
						<td width="7%" class="th-edit">エリア</td>
						<td width="64%" class="td-edit"><select name="area" id="area" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
//		$query = sprintf("select * from area where vf=true and id in (SELECT distinct area from shop where vf=true) order by weight desc");
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

				<tr>
						<td class="th-edit">並べ替え</td>
						<td class="td-edit"><label>
								<select name="order" id="order" onchange="this.form.submit()">
<?php
	for($a=0,$b=count($__oList); $b--; $a++){
		$selected = sprintf("%s",$a==$order? " selected":"");
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$a); ?>"><?php printf("%s",$__oList[$a]['name']); ?></option>
<?php
	}
?>
								</select>
						</label></td>
						<td class="th-edit">表示</td>
						<td class="td-edit"><input type="submit" name="go" id="go" value="更新" />
						<input name="exec" type="hidden" id="exec" value="on" /></td>
				</tr>
		</table>
</form>
<?php
		$qq = array();
		$qq[] = sprintf("select shop.*,division.id as did,area.id as aid,currency.id as cid,currency.name as cname,staff.nickname");
		$qq[] = sprintf("from shop,division,area,currency,staff");
		$qq[] = sprintf("where shop.vf=true and shop.division=division.id and shop.area=area.id and shop.currency=currency.id and shop.ustaff=staff.id");
		if($division){
			$tree = divisionTree($handle,$division); $tree[]=$division;
			$qq[] = sprintf("and shop.division in (%s)",implode(",",$tree));
		}
		if($area){
			$tree = areaTree($handle,$area); $tree[]=$area;
			$qq[] = sprintf("and shop.area in (%s)",implode(",",$tree));
		}
		$qq[] = sprintf("order by %s",$__oList[$order]['text']);
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<p class="title1">検索結果 (<?php printf("%s",number_format($qs)); ?>件)</p>
		<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> -->
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="17%">
				<tr>
						<td width="2%" class="th-edit">id</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="1%" class="th-edit">取扱ブランド</td>
						<td width="1%" class="th-edit">事業部</td>
						<td width="1%" class="th-edit">エリア</td>
						<td width="95%" class="th-edit">通貨</td>
						<td width="1%" class="th-edit">開店日</td>
						<td width="1%" class="th-edit">閉店日</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$dName=getDivisionName($handle,$qo['did']);
		$aName=getAreaName($handle,$qo['aid']);

		$bname = array();
		$bid = getPGSQLarray($qo['brand']);
		if(count($bid)){
			$query = sprintf("select brand.* from brand where brand.id in (%s) order by brand.weight desc",implode(",",$bid));
			$br = pg_query($handle,$query);
			$bs = pg_num_rows($br);
			for($aa=0; $aa<$bs; $aa++){
				$bo = pg_fetch_array($br,$aa);
				$bname[] = sprintf("<a href=brand.php?mode=edit&id=%d>%s</a>",$bo['id'],$bo['name']);
			}
		}
		$brand = implode("<br />",$bname);

		$ps = dt2JP($qo['ps']);
		$pe = ($qo['ps']==$qo['pe'])? "----":dt2JP($qo['pe']);
/*
		$query = sprintf("select count(*) from daily where vf=true and entered=true and shop='%d'",$qo['id']);
		$rr = pg_query($handle,$query);
		$ro = pg_fetch_array($rr);
*/
?>
				<tr>
						<td class="td-edit"><a href="shop.php?mode=edit&id=<?php printf("%d",$qo['id']); ?>"><?php printf("%04d",$qo['id']); ?></a></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><?php printf("%s",$brand); ?></td>
						<td class="td-edit"><a href="division.php?mode=edit&id=<?php printf("%d",$qo['did']); ?>"><?php printf("%s",$dName); ?></a></td>
						<td class="td-edit"><a href="area.php?mode=edit&id=<?php printf("%d",$qo['aid']); ?>"><?php printf("%s",$aName); ?></a></td>
						<td class="td-edit"><a href="currency.php?mode=edit&id=<?php printf("%d",$qo['cid']); ?>"><?php printf("%s",$qo['cname']); ?></a></td>
						<td class="td-edit"><?php printf("%s",$ps); ?></td>
						<td class="td-edit"><?php printf("%s",$pe); ?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
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
