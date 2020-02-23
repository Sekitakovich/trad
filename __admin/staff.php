<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>staff</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
<link href="../jscalendar/calendar-green.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript" src="../common.js"></script>
<script type="text/javascript" src="../prototype.js"></script>
<script type="text/javascript" src="../php.js"></script>
<script type="text/javascript" src="../jscalendar/calendar.js"></script>
<script type="text/javascript" src="../jscalendar/calendar-setup.js"></script>
<script type="text/javascript" src="../jscalendar/lang/calendar-jp-utf8.js"></script>
<?php
	function isRefer($handle,$id)
	{
		$refer = 0;
		$table = array("shop","division","area","currency","staff");	
		for($a=0,$b=count($table); $b--; $a++){
			$query = sprintf("select count(*) from %s where vf=true and (istaff='%d' or ustaff='%d')",$table[$a],$id,$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			if($refer = $qo['count']){
				break;
			}
		}
		return($refer);
	}

	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from staff");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into staff(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("nickname='%s'",pg_escape_string($_REQUEST['nickname']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);
		$set[] = sprintf("account='%s'",pg_escape_string($_REQUEST['account']));
		$set[] = sprintf("password='%s'",pg_escape_string($_REQUEST['password']));
		$set[] = sprintf("sex='%s'",pg_escape_string($_REQUEST['sex']));
		$set[] = sprintf("bday='%s'",$_REQUEST['bday']);
//
		$set[] = sprintf("dset='{%s}'",implode(",",$_REQUEST['dset']));
		$set[] = sprintf("aset='{%s}'",implode(",",$_REQUEST['aset']));
		$set[] = sprintf("bset='{%s}'",implode(",",$_REQUEST['bset']));
		$set[] = sprintf("dcheck='%s'",$_REQUEST['dcheck']);
		$set[] = sprintf("acheck='%s'",$_REQUEST['acheck']);
		$set[] = sprintf("bcheck='%s'",$_REQUEST['bcheck']);
		$set[] = sprintf("shop='%d'",$_REQUEST['shop']);
//
		for($perm=0,$a=0,$b=count($_REQUEST['perm']); $b--; $a++){
			$perm|=$_REQUEST['perm'][$a];
		}		
		$set[] = sprintf("perm='%d'",$perm);
//		
		for($a=0; $a<2; $a++){
			$set[] = sprintf("name[%d]='%s'",$a+1,pg_escape_string($_REQUEST['name'][$a]));
			$set[] = sprintf("kana[%d]='%s'",$a+1,pg_escape_string($_REQUEST['kana'][$a]));
			$set[] = sprintf("mail[%d]='%s'",$a+1,pg_escape_string($_REQUEST['mail'][$a]));
		}

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update staff set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update staff set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="staff.php">もどる</a>
<script type="text/javascript">
window.onload = function()
{
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if($id=$_REQUEST['id']){
			$query = sprintf("select staff.* from staff where staff.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$nickname = $qo['nickname'];
			$remark = $qo['remark'];
			$name = getPGSQLarray($qo['name']);
			$kana = getPGSQLarray($qo['kana']);
			$account = $qo['account'];
			$password = $qo['password'];
			$sex = $qo['sex'];
			$bday = $qo['bday'];
			$dset = getPGSQLarray($qo['dset']);
			$aset = getPGSQLarray($qo['aset']);
			$perm = $qo['perm'];
			$dcheck = $qo['dcheck'];
			$acheck = $qo['acheck'];
			$mail = getPGSQLarray($qo['mail']);
			$bset = getPGSQLarray($qo['bset']);
			$bcheck = $qo['bcheck'];
			$shop = $qo['shop'];

			$refer = isRefer($handle,$qo['id']);
		}
		else{
			$id = 0;
			$nickname = '';
			$remark = "";
			$account = "";
			$password = "";
			$name = array();
			$kana = array();
			$sex = 't';
			$bday = date("Y-m-d");
			$dset = array();
			$aset = array();
			$perm =0;
			$dcheck = 't';
			$acheck = 'f';
			$mail = array();
			$bset = array();
			$bcheck = 'f';
			$shop = 0;

			$refer = 0;
		}
?>
<p class="title1">HPFスタッフ：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var elm;
	var sets;
	var mes = new Array();
	var a;
	var b;
	var err = 0;
// Ajax! Ajax! Ajax!
<?php
	$query = sprintf("select account from staff where vf=true and id<>%d",$id);
?>
	var ooo = new Ajax.Request('../query.php',{method:'get',asynchronous:false,parameters:'?query=<?php printf("%s",$query); ?>'});
	var account = explode('\n',ooo.transport.responseText);
// Ajax! Ajax! Ajax!

	
/*
	for(sets=0,elm=F.elements['dset[]'],a=0,b=elm.length; b--; a++){
		if(elm[a].checked == true){
			sets++;
		}
	}
	if(sets==0){
		mes[err++] = "事業部が選択されていません";
	}
//
	for(sets=0,elm=F.elements['aset[]'],a=0,b=elm.length; b--; a++){
		if(elm[a].checked == true){
			sets++;
		}
	}
	if(sets==0){
		mes[err++] = "エリアが選択されていません";
	}
*/
	if(F.elements['nickname'].value==''){
		mes[err++] = "ハンドルは必須です";
	}
	for(a=0; a<2; a++){
		if(F.elements['name['+a+']'].value==''){
			mes[err++] = "名前は必須です";
			break;
		}
	}
	for(a=0; a<2; a++){
		if(F.elements['kana['+a+']'].value==''){
			mes[err++] = "読みは必須です";
			break;
		}
	}
	if(F.elements['account'].value==''){
		mes[err++] = "アカウントは必須です";
	}
	else if(in_array(F.elements['account'].value,account)){
		mes[err++] = "アカウントが他で使用されています";
	}
	if(F.elements['password'].value==''){
		mes[err++] = "パスワードは必須です";
	}
	if(err){
		alert(mes.join('\n'));
		return false;
	}
	else if(F.elements['delete'].checked){
		return confirm('このレコードを削除しますか?');
	}
	else return confirm('この内容で登録してよろしいですか?');
}
		</script>
		<script type="text/javascript">
function alertShop(F)
{
	var offset;
	if((offset=F.elements['shop'].value)!=0){
		var si = F.elements['shop'].options.selectedIndex;
		var mes = array(
			sprintf("このスタッフは以後%sの従業員として扱われ、",F.elements['shop'].options[si].text),
			'日報入力とレポート閲覧以外の全ての機能・権限が無効となります。よろしいですか?'
		);
		if(confirm(mes.join('\n'))){
			var si = F.elements['shop'].options.selectedIndex;
			var division = F.elements['shop'].options[si].getAttribute('division');
			var area = F.elements['shop'].options[si].getAttribute('area');

			var elm = F.elements['dset[]'];
			for(a=0,b=elm.length; b--; a++){
				if(elm[a].value==division){
					elm[a].checked = true;
				}
			}
			var elm = F.elements['aset[]'];
			for(a=0,b=elm.length; b--; a++){
				if(elm[a].value==area){
					elm[a].checked = true;
				}
			}
			
			var elm = F.elements['perm[]'];
			for(a=0,b=elm.length; b--; a++){
				elm[a].checked = false;
				elm[a].disabled = true;
			}
		}
		else{
			F.elements['shop'].options.selectedIndex = 0;
		}
	}
}
		</script>
</p>
* 事業部 = レポートの際の対象事業部<br />
* エリア = レポートの際の対象エリア <br />
<br />
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="34%">
				<tr>
						<td width="2%" class="th-edit">ハンドル</td>
						<td width="98%" class="td-edit"><label>
								<input name="nickname" type="text" id="nickname" value="<?php printf("%s",$nickname); ?>" size="32" maxlength="128" />
						</label></td>
						<td width="98%" class="th-edit">アカウント</td>
						<td width="98%" class="td-edit"><input name="account" type="text" id="account" value="<?php printf("%s",$account); ?>" size="8" maxlength="8" />
						パスワード
						<input name="password" type="password" id="password" value="<?php printf("%s",$password); ?>" size="8" maxlength="8" /></td>
				</tr>
				<tr>
						<td class="th-edit">名前</td>
						<td class="td-edit">姓
								<label>
								<input name="name[0]" type="text" id="name[0]" value="<?php printf("%s",$name[0]); ?>" size="16" maxlength="64" />
								</label>
						名								<label>
								<input name="name[1]" type="text" id="name[1]" value="<?php printf("%s",$name[1]); ?>" size="16" maxlength="64" />
								</label></td>
						<td class="th-edit">読み</td>
						<td class="td-edit">姓
								<label>
								<input name="kana[0]" type="text" id="kana[0]" value="<?php printf("%s",$kana[0]); ?>" size="16" maxlength="64" />
								</label>
名
<label>
<input name="kana[1]" type="text" id="kana[1]" value="<?php printf("%s",$kana[1]); ?>" size="16" maxlength="64" />
</label></td>
				</tr>
				<tr>
						<td class="th-edit">メールアドレス(PC)</td>
						<td class="td-edit"><label>
								<input name="mail[0]" type="text" id="mail[0]" value="<?php printf("%s",$mail[0]); ?>" size="48" maxlength="256" />
						</label></td>
						<td class="th-edit">メールアドレス(携帯)</td>
						<td class="td-edit"><input name="mail[1]" type="text" id="mail[1]" value="<?php printf("%s",$mail[1]); ?>" size="48" maxlength="256" /></td>
				</tr>
				<tr>
						<td class="th-edit">性別</td>
						<td class="td-edit"><span class="td-nowrap">
								<label><input name="sex" type="radio" value="true"<?php printf("%s",$sex=='t'? " checked":""); ?> />
男性</label>
<label><input name="sex" type="radio" value="false"<?php printf("%s",$sex!='t'? " checked":""); ?> />
女性</label></span></td>
						<td class="th-edit">生年月日</td>
						<td class="td-edit"><span class="td-nowrap">
								<label>
<input name="bday" type="text" id="bday" value="<?php printf("%s",$bday); ?>" size="10" maxlength="10" readonly="true" />
</label>
						</span></td>
				</tr>
				<tr>
						<td class="th-edit">店舗</td>
						<td class="td-edit"><label><select name="shop" id="shop" onchange="alertShop(this.form)">
								<option value="0">---</option>
								<?php
		$query = sprintf("select shop.*,division.id as did,area.id as aid from shop,division,area where shop.vf=true and shop.division=division.id and shop.area=area.id order by division.weight desc,area.weight desc,shop.name");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$shop? " selected":"");
			$dName=getDivisionName($handle,$qo['did']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>" division="<?php printf("%d",$qo['did']); ?>" area="<?php printf("%d",$qo['aid']); ?>"><?php printf("%s (%s)",$qo['name'],$dName); ?></option>
								<?php
		}
?>
						</select></label></td>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit">&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">事業部 初期状態</td>
						<td class="td-edit"><label>
								<label><input <?php printf("%s",$dcheck=='t'? " checked":""); ?> name="dcheck" type="radio" id="dcheck" value="t" />選択</label>
						<label><input <?php printf("%s",$dcheck!='t'? " checked":""); ?> name="dcheck" type="radio" id="dcheck" value="f" />
非選択</label></td>
						<td class="th-edit">エリア 初期状態</td>
						<td class="td-edit"><label><input <?php printf("%s",$acheck=='t'? " checked":""); ?> name="acheck" type="radio" id="acheck" value="t" />選択</label>
		<label><input <?php printf("%s",$acheck!='t'? " checked":""); ?> name="acheck" type="radio" id="acheck" value="f" />非選択</label></td>
				</tr>
				<tr>
						<td class="th-edit">事業部</td>
						<td class="td-edit"><?php
		$query = sprintf("select * from division where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$checked = sprintf("%s",in_array($qo['id'],$dset)? " checked":"");
			$dName=getDivisionName($handle,$qo['id']);
?>
								<label><input <?php printf("%s",$checked); ?> name="dset[]" type="checkbox" id="dset[]" value="<?php printf("%d",$qo['id']); ?>" />
								<?php printf("%s",$dName); ?> 以下</label><br />
								<?php
		}
?></td>
						<td class="th-edit">エリア</td>
						<td class="td-edit"><?php
		$query = sprintf("select * from area where vf=true order by weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$checked = sprintf("%s",in_array($qo['id'],$aset)? " checked":"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<label><input <?php printf("%s",$checked); ?> name="aset[]" type="checkbox" id="aset[]" value="<?php printf("%d",$qo['id']); ?>" />
								<?php printf("%s",$dName); ?> 以下</label><br />
								<?php
		}
?></td>
				</tr>
				<tr>
						<td class="th-edit">取扱ブランド(ex)</td>
						<td class="td-edit"><?php
	$query = sprintf("select brand.* from brand where brand.vf=true and brand.exclusive=true order by brand.weight desc,brand.name");
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$checked = sprintf("%s",in_array($qo['id'],$bset)? " checked":"");
?>
										<label>
												<input name="bset[]" type="checkbox" id="bset[]" value="<?php printf("%d",$qo['id']); ?>" <?php printf("%s",$checked);?> />
										<?php printf("%s",$qo['name']); ?></label>
								<br />
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
		$checked = sprintf("%s",in_array($qo['id'],$bset)? " checked":"");
?>
										<label>
										<input name="bset[]" type="checkbox" id="bset[]" value="<?php printf("%d",$qo['id']); ?>" <?php printf("%s",$checked);?> />
										<?php printf("%s",$qo['name']); ?></label>
										<br />
										<?php
	}
?></td>
				</tr>
				<tr>
						<td class="th-edit">取扱ブランド 初期状態</td>
						<td class="td-edit"><label>
								<input <?php printf("%s",$bcheck=='t'? " checked":""); ?> name="bcheck" type="radio" id="bcheck" value="t" />
								選択</label>
								<label>
								<input <?php printf("%s",$bcheck!='t'? " checked":""); ?> name="bcheck" type="radio" id="bcheck" value="f" />
非選択</label></td>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit">&nbsp;</td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td class="td-edit"><label>
								<textarea name="remark" cols="48" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
						</label></td>
						<td class="th-edit">権限</td>
						<td class="td-edit"><?php
for($a=0,$b=count($__dPerm); $b--; $a++){
	$checked = sprintf("%s",($perm&$__dPerm[$a]['mask'])? " checked":"");
?>
								<label>
								<input <?php printf("%s",$checked); ?> name="perm[]" type="checkbox" id="perm[]" value="<?php printf("%d",$__dPerm[$a]['mask']); ?>" />
								<?php printf("%s",$__dPerm[$a]['name']); ?><br />
								</label>
								<?php
}
?></td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
								<input name="mode" type="hidden" id="mode" value="save" />
								<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
								<span id="void">
								<input name="delete" type="checkbox" id="delete" value="t" />
削除する</span></td>
						<td class="th-edit">&nbsp;</td>
						<td class="td-edit">&nbsp;</td>
				</tr>
		</table>
</form><script type="text/javascript">
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
	else{
	}
	Calendar.setup({inputField:"bday",ifFormat:"%Y-%m-%d",button:"bday",eventName:"click"});
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
		$__stList = array(
			array('name'=>'全て','text'=>''),
			array('name'=>'店舗スタッフのみ','text'=>'S'),
			array('name'=>'社内スタッフのみ','text'=>'C'),
		);
		$__oList = array(
			array('name'=>'最終更新日時','text'=>'staff.udate desc'),
			array('name'=>'ハンドル','text'=>'staff.nickname'),
			array('name'=>'アカウント','text'=>'staff.account'),
			array('name'=>'初回登録日時','text'=>'staff.idate desc'),
		);

		if(isset($_REQUEST['exec'])){
			$dset = $_REQUEST['dset'];
			$aset = $_REQUEST['aset'];
			$perm = $_REQUEST['perm'];
			$stype = $_REQUEST['stype'];
			$order = $_REQUEST['order'];
			$_SESSION['staff']=array('dset'=>$dset,'aset'=>$aset,'perm'=>$perm,'order'=>$order);
		}
		else if(isset($_SESSION['staff'])){
			$dset = $_SESSION['staff']['dset'];
			$aset = $_SESSION['staff']['aset'];
			$perm = $_SESSION['staff']['perm'];
			$stype = $_SESSION['staff']['stype'];
			$order = $_SESSION['staff']['order'];
		}
		else{
			$dset = 0;
			$aset = 0;
			$perm = 0;
			$stype = '';
			$order =0;
		}
?>
<form id="form" name="" method="post" action="">
		<table width="44%">
				<tr>
						<td width="5%" class="th-edit">事業部</td>
						<td width="24%" class="td-edit"><label>
								<select name="dset" id="dset" onchange="this.form.submit()">
										<option value="0">-- 全て --</option>
										<?php
		$ddd = array();
		$query = sprintf("select dset from staff where vf=true");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$ddd = array_merge($ddd,getPGSQLarray($qo['dset']));
		}
		
		$query = sprintf("select * from division where vf=true and id in (%s) order by weight desc",implode(",",$ddd));
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$dset? " selected":"");
			$dName=getDivisionName($handle,$qo['id']);
?>
										<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
										<?php
		}
?>
								</select>
						</label></td>
						<td width="7%" class="th-edit">エリア</td>
						<td width="64%" class="td-edit"><select name="aset" id="aset" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		$ddd = array();
		$query = sprintf("select aset from staff where vf=true");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$ddd = array_merge($ddd,getPGSQLarray($qo['aset']));
		}
		
		$query = sprintf("select * from area where vf=true and id in (%s) order by weight desc",implode(",",$ddd));
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
		for($a=0; $a<$qs; $a++){
			$qo = pg_fetch_array($qr,$a);
			$selected = sprintf("%s",$qo['id']==$aset? " selected":"");
			$dName=getAreaName($handle,$qo['id']);
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$qo['id']); ?>"><?php printf("%s",$dName); ?></option>
								<?php
		}
?>
						</select></td>
				</tr>
				<tr>
						<td class="th-edit">種別</td>
						<td class="td-edit"><select name="stype" id="stype" onchange="this.form.submit()">
								<?php
	for($a=0,$b=count($__stList); $b--; $a++){
		$selected = sprintf("%s",$__stList[$a]['text']==$stype? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%s",$__stList[$a]['text']); ?>"><?php printf("%s",$__stList[$a]['name']); ?></option>
								<?php
	}
?>
						</select></td>
						<td class="th-edit">権限</td>
						<td class="td-edit"><select name="perm" id="perm" onchange="this.form.submit()">
								<option value="0">-- 全て --</option>
								<?php
		for($a=0,$b=count($__dPerm); $b--; $a++){
			$selected = sprintf("%s",$__dPerm[$a]['mask']==$perm? " selected":"");
?>
								<option <?php printf("%s",$selected); ?> value="<?php printf("%d",$__dPerm[$a]['mask']); ?>"><?php printf("%s",$__dPerm[$a]['name']); ?></option>
								<?php
		}
?>
						</select></td>
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
		$qq[] = sprintf("select staff.*,shop.name as shopname");
		$qq[] = sprintf("from staff,division,shop");
		$qq[] = sprintf("where staff.vf=true");
		$qq[] = sprintf("and staff.division=division.id");
		$qq[] = sprintf("and staff.shop=shop.id");
		if($dset){
			$qq[] = sprintf("and '%d'=any(staff.dset)",$dset);
		}
		if($aset){
			$qq[] = sprintf("and '%d'=any(staff.aset)",$aset);
		}
		if($perm){
			$qq[] = sprintf("and staff.perm&%d<>0",$perm);
		}
		switch($stype){
			case 'S':
				$qq[] = sprintf("and staff.shop<>0");
				break;
			case 'C':
				$qq[] = sprintf("and staff.shop=0");
				break;
			default:
				break;
		}
		$qq[] = sprintf("order by %s",$__oList[$order]['text']);
		$query = implode(" ",$qq);
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<!-- <?php printf("Query(%d) = [%s]",$qr,$query); ?> --><p class="title1">HPFスタッフ(<?php printf("%s",number_format($qs)); ?>名) <a href="staff.php?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">id</td>
						<td width="2%" class="th-edit">アカウント</td>
						<td width="2%" class="th-edit">ハンドル</td>
						<td width="1%" class="th-edit">本名</td>
						<td width="1%" class="th-edit">店舗</td>
						<td width="2%" class="th-edit">事業部</td>
						<td width="2%" class="th-edit">エリア</td>
						<td width="95%" class="th-edit">権限</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$us = getStaffInfo($handle,$qo['ustaff']);

//Var_dump::display($qo);

		$name = getPGSQLarray($qo['name']);
		$kana = getPGSQLarray($qo['kana']);

		$dset = getPGSQLarray($qo['dset']);
		if(count($dset)){
			$ooo = array();
			for($aa=0,$bb=count($dset); $bb--; $aa++){
				$ooo[]=sprintf("<a href=division.php?mode=edit&id=%d>%s</a>",$dset[$aa],getDivisionName($handle,$dset[$aa]));
			}
			$sDiv = implode("<br />",$ooo);
		}
		else $sDiv = "　";

		$aset = getPGSQLarray($qo['aset']);
		if(count($aset)){
			$ooo = array();
			for($aa=0,$bb=count($aset); $bb--; $aa++){
				$ooo[]=sprintf("<a href=area.php?mode=edit&id=%d>%s</a>",$aset[$aa],getAreaName($handle,$aset[$aa]));
			}
			$sArea = implode("<br />",$ooo);
		}
		else $sArea = "　";
?>
				<tr shop="<?php printf("%d",$qo['shop']) ?>">
						<td class="td-edit"><a href="staff.php?mode=edit&id=<?php printf("%d",$qo['id']); ?>"><?php printf("%04d",$qo['id']); ?></a></td>
						<td class="td-edit"><?php printf("%s",$qo['account']); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['nickname']); ?></td>
						<td class="td-edit"><?php printf("%s",implode(" ",$name)); ?></td>
						<td class="td-edit"><?php printf("%s",$qo['shopname']); ?></td>
						<td class="td-edit"><?php printf("%s",$sDiv); ?></td>
						<td class="td-edit"><?php printf("%s",$sArea); ?></td>
						<td class="td-edit">
<?php
for($aa=0,$bb=count($__dPerm); $bb--; $aa++){
	if($qo[perm]&$__dPerm[$aa]['mask']){
		printf("%s<br />",$__dPerm[$aa]['name']);
	}
}
?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$us['nickname']); ?></td>
				</tr>
<?php
	}
?>
		</table>
</form>
<script type="text/javascript">
window.onload = function()
{
	var a;
	var b;
	var elm = document.getElementsByTagName('TR');
	for(a=0,b=elm.length; b--; a++){
		switch(elm[a].getAttribute('shop')){
			case '0':
			case null:
				break;
			default:
				elm[a].style.backgroundColor = '#FFCCFF';
				break;
		}
	}
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	}
?>
</body>
</html>
<?php
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
