<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>currency</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript" src="../common.js"></script>
<script type="text/javascript" src="../php.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);

//	Var_Dump::display($whoami);

	pg_query($handle,"begin");
	switch($_REQUEST['mode']){
//--------------------------------------------------------------------
	case "wsave":
		$idS = $_REQUEST['idS'];
		$old = $_REQUEST['old'];
		
		for($a=0,$b=count($idS); $b--; $a++){
			if($old[$a]!=$idS[$a]){
				$query = sprintf("update currency set weight='%d' where id='%d'",$b,$idS[$a]);
				$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			}
		}
?>
<a href="currency.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "save":
		$id=$_REQUEST['id'];
		if($id==0){
			$query = sprintf("select max(id) from currency");
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
			$qo = pg_fetch_array($qr);
			$id=$qo['max']+1;
			$query = sprintf("insert into currency(id,istaff,ustaff) values('%d','%d','%d')",$id,$whoami['id'],$whoami['id']);
			$qr = pg_query($handle,$query);
?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
		}
		$set = array();
		$set[] = sprintf("name='%s'",pg_escape_string($_REQUEST['name']));
		$set[] = sprintf("remark='%s'",pg_escape_string($_REQUEST['remark']));
		$set[] = sprintf("rate='%f'",$_REQUEST['rate']);
		$set[] = sprintf("udate=now(),ustaff='%d'",$whoami['id']);

		if(isset($_REQUEST['delete'])){
			$query = sprintf("update currency set vf=false where id='%d'",$id);
		}
		else{
			$query = sprintf("update currency set %s where id='%d'",implode(",",$set),$id);
		}
		$qr = pg_query($handle,$query);

?><!-- <?php printf("Query (%d) [ %s ]\n",$qr,$query); ?> --><?php
?>
<a href="currency.php">もどる</a>
<?php
		break;
//--------------------------------------------------------------------
	case "edit":
		if($id=$_REQUEST['id']){
			$query = sprintf("select currency.* from currency where currency.id='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$name = $qo['name'];
			$remark = $qo['remark'];
			$weight = $qo['weight'];
			$rate = $qo['rate'];

			$query = sprintf("select count(*) from shop where vf=true and currency='%d'",$id);
			$qr = pg_query($handle,$query);
			$qo = pg_fetch_array($qr);
			$refer = $qo['count'];
		}
		else{
			$id = 0;
			$name = '';
			$remark = "";
			$weight = 0;
			$rate = 0;

			$refer = 0;
		}
?>
<p class="title1">通貨：編集
		<script language="JavaScript" type="text/javascript">
function checkTheForm(F)
{
	var mes = new Array();
	var err = 0;
	if(F.elements['name'].value==''){
		mes[err++] = "名称は必須";
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
</p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="edit" target="_self" id="edit" onsubmit="return checkTheForm(this)">
		<table width="29%">
				<tr>
						<td width="2%" class="th-edit">名称</td>
						<td width="98%" class="td-edit"><label>
								<input name="name" type="text" id="name" value="<?php printf("%s",$name); ?>" size="32" maxlength="64" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">日本円換算レート</td>
						<td class="td-edit"><label>
								<input name="rate" type="text" class="input-Digit" id="rate" value="<?php printf("%.3f",$rate); ?>" size="10" maxlength="20" />
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">備考</td>
						<td class="td-edit"><label>
								<textarea name="remark" cols="64" rows="4" id="remark"><?php printf("%s",$remark); ?></textarea>
						</label></td>
				</tr>
				<tr>
						<td class="th-edit">登録</td>
						<td class="td-edit"><input type="submit" name="exec" id="exec" value="実行" />
								<input name="mode" type="hidden" id="mode" value="save" />
								<input name="id" type="hidden" id="id" value="<?php printf("%d",$id); ?>" />
								<span id="void">
								<input name="delete" type="checkbox" id="delete" value="t" />
削除する</span></td>
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
}
</script>
<?php
		break;
//--------------------------------------------------------------------
	default:
//
		$query = sprintf("select count(*) from currency");
		$qr = pg_query($handle,$query);
		$qo = pg_fetch_array($qr);
		$wmax = $qo['count']*2;
//
		$query = sprintf("select currency.*,staff.nickname from currency,staff where currency.vf=true and currency.ustaff=staff.id order by currency.weight desc");
		$qr = pg_query($handle,$query);
		$qs = pg_num_rows($qr);
?>
<p class="title1">通貨 <a href="currency.php?mode=edit">新規登録</a></p>
<form action="" method="post" enctype="application/x-www-form-urlencoded" name="list" target="_self" id="list">
		<table width="4%">
				<tr>
						<td width="2%" class="th-edit">表示順</td>
						<td width="2%" class="th-edit">id</td>
						<td width="2%" class="th-edit">名称</td>
						<td width="1%" class="th-edit">レート(日本円換算)</td>
						<td width="95%" class="th-edit">最終更新日時</td>
				</tr>
<?php
	$idS = array();
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
		$idS[] = $qo['id'];
?>
				<tr id="row[<?php printf("%d",$a); ?>]">
						<td class="td-edit">
						<label><input name="next[<?php printf("%d",$a); ?>]" type="button" id="next[<?php printf("%d",$a); ?>]" onclick=swapRow(this.form,'<?php printf("%d",$a); ?>','N') value="↓" />
						</label>
						<label><input name="prev[<?php printf("%d",$a); ?>]" type="button" id="prev[<?php printf("%d",$a); ?>]" onclick=swapRow(this.form,'<?php printf("%d",$a); ?>','P') value="↑" />
						<input name="idS[<?php printf("%d",$a); ?>]" type="hidden" id="idS[<?php printf("%d",$a); ?>]" value="<?php printf("%d",$qo['id']); ?>" />
						<input name="old[<?php printf("%d",$a); ?>]" type="hidden" id="old[<?php printf("%d",$a); ?>]" value="<?php printf("%d",$qo['id']); ?>" />
						</label>						</td>
						<td class="td-edit"><a href="currency.php?mode=edit&amp;id=<?php printf("%d",$qo['id']); ?>"><?php printf("%04d",$qo['id']); ?></a></td>
						<td class="td-edit"><?php printf("%s",$qo['name']); ?></td>
						<td class="td-edit"><?php printf("× %s",number_format($qo['rate'],3)); ?></td>
						<td class="td-edit"><?php printf("%s by %s",ts2JP($qo['udate']),$qo['nickname']); ?></td>
				</tr>
<?php
	}
?>
				<tr>
						<td class="th-edit"><input name="update" type="submit" disabled="true" id="update" value="更新" />
						<input name="mode" type="hidden" id="mode" value="wsave" />
						<input name="length" type="hidden" id="length" value="<?php printf("%d",$qs); ?>" /></td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
						<td class="th-edit">&nbsp;</td>
				</tr>
		</table>
</form><script type="text/javascript">
window.onload = function()
{
/*
	var a;
	var b;
	var length = document.list.length.value;
	var __t = 0;
	var __e = length-1;
	
	var top = document.getElementById('prev['+__t+']');
	var end = document.getElementById('next['+__e+']');

	top.className = 'notDisplay';
	end.className = 'notDisplay';
*/
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
