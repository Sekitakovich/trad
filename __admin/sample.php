<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DOMによるTRの並べ替え</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript" src="../common.js"></script>
<script type="text/javascript" src="../php.js"></script>
<script type="text/javascript" src="../prototype.js"></script>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	pg_query($handle,"begin");

	$query = sprintf("select * from currency where vf=true order by weight desc");
	$qr = pg_query($handle,$query);
	$qs = pg_num_rows($qr);
?>
<script type="text/javascript">
function swapRow(F,src,pn)
{
	var length = F.length.value;
	src = parseInt(src);

	if(src == 0 && pn == 'P'){
		;
	}
	else if(src == length-1 && pn == 'N'){
		;
	}
	else{
		var a;
		var b;
		var dst = pn=='N'? src+1:src-1;
		var __idS = new Array();
		var __old = new Array();
		var ooo;
		var ppp;
	
		alert('src='+src+',dst='+dst);

		for(a=0,b=length; b--; a++){
			__idS[a] = F.elements['idS['+a+']'].value;
		}

		ooo = __idS[src];
		__idS[src] = __idS[dst];
		__idS[dst] = ooo;

		ooo = document.getElementById('row['+src+']');
		ppp = document.getElementById('row['+dst+']');

		var cells = ooo.cells.length;
		for(a=1; a<cells; a++){ // [0]は書き換えないところがミソ
			var iH = ooo.cells[a].innerHTML;
			ooo.cells[a].innerHTML = ppp.cells[a].innerHTML;
			ppp.cells[a].innerHTML = iH;
		}
/*
*
*/
		for(a=0,b=length; b--; a++){
			F.elements['idS['+a+']'].value = __idS[a];
		}
//
		F.elements['update'].disabled = '';
//
	}
}
</script>
<form id="sample" name="sample" method="post" action="">
		<table width="9%" class="table-edit" id="list">
				<tr>
						<td width="2%" class="th-edit">表示順</td>
						<td width="3%" nowrap="nowrap" class="th-edit">ID</td>
						<td width="2%" nowrap="nowrap" class="th-edit">W</td>
						<td width="95%" nowrap="nowrap" class="th-edit">name</td>
				</tr>
<?php
	for($a=0; $a<$qs; $a++){
		$qo = pg_fetch_array($qr,$a);
?>
				<tr id="row[<?php printf("%d",$a); ?>]">
						<td class="td-edit"><label>
								<input name="next[<?php printf("%d",$a); ?>]" type="button" id="next[<?php printf("%d",$a); ?>]" onclick="swapRow(this.form,'<?php printf("%d",$a); ?>','N')" value="↓" />
								</label>
										<label>
										<input name="prev[<?php printf("%d",$a); ?>]" type="button" id="prev[<?php printf("%d",$a); ?>]" onclick="swapRow(this.form,'<?php printf("%d",$a); ?>','P')" value="↑" />
										<input name="idS[<?php printf("%d",$a); ?>]" type="hidden" id="idS[<?php printf("%d",$a); ?>]" value="<?php printf("%d",$qo['id']); ?>" />
										<input name="old[<?php printf("%d",$a); ?>]" type="hidden" id="old[<?php printf("%d",$a); ?>]" value="<?php printf("%d",$qo['id']); ?>" />
										</label>
						</td>
						<td nowrap="nowrap" class="td-edit"><?php printf("%04d",$qo['id']); ?></td>
						<td nowrap="nowrap" class="td-edit"><?php printf("%d",$qo['weight']); ?></td>
						<td nowrap="nowrap" class="td-edit"><?php printf("%s",$qo['name']); ?></td>
				</tr>
<?php
	}
?>
				<tr>
						<td class="th-edit"><input name="update" type="submit" disabled="disabled" id="update" value="更新" />
										<input name="mode" type="hidden" id="mode" value="wsave" />
										<input name="length" type="hidden" id="length" value="<?php printf("%d",$qs); ?>" /></td>
						<td nowrap="nowrap" class="th-edit">&nbsp;</td>
						<td nowrap="nowrap" class="th-edit">&nbsp;</td>
						<td nowrap="nowrap" class="th-edit">&nbsp;</td>
				</tr>
		</table>
</form>
<script type="text/javascript">
window.onload = function()
{
}
</script>
<?php
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</body>
</html>
