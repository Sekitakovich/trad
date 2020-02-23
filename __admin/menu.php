<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>menu</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php
include("../hpfmaster.inc");
if($handle=pg_connect($pgconnect)){
	$whoami = getStaffInfo($handle);
	pg_query($handle,"begin");
?>
<form id="form1" name="form1" method="post" action="">
		<table width="18%">
				<tr>
						<td width="5%" class="th-edit">H.P.France Sales Report for <?php printf("%s",$whoami['nickname']); ?></td>
<?php
	if($whoami['shop']){
?>
						<td width="2%" class="td-edit"><a href="daily.php" target="mainFrame">日報入力</a></td>
						<?php
	}
?>
						<td width="6%" class="td-edit"><a href="dailyreport.php" target="mainFrame">レポート(日報)</a></td>
						<td width="6%" class="td-edit"><a href="report.php" target="mainFrame">レポート(期間集計)</a></td>
<?php
	if($whoami['perm']&PERM_MASTER_EDIT){
?>
						<td width="6%" class="th-edit">マスター保守</td>
						<td width="2%" class="td-edit"><a href="event.php" target="mainFrame">イベント</a></td>
						<td width="2%" class="td-edit"><a href="shop.php" target="mainFrame">shop</a></td>
						<td width="3%" class="td-edit"><a href="staff.php" target="mainFrame">staff</a></td>
						<td width="3%" class="td-edit"><a href="division.php" target="mainFrame">事業部</a></td>
						<td width="82%" class="td-edit"><a href="area.php" target="mainFrame">エリア</a></td>
						<td width="2%" class="td-edit"><a href="brand.php" target="mainFrame">ブランド</a></td>
						<td width="2%" class="td-edit"><a href="currency.php" target="mainFrame">通貨</a></td>
						<?php
	}
?>
<?php
	if($whoami['perm']&PERM_DAILY_EDIT){
?>
						<td width="2%" class="td-edit"><a href="dedit.php" target="mainFrame">売上データの編集</a></td>
<?php
	}
?>
				</tr>
		</table>
</form>
<?php
	pg_query($handle,"commit");
	pg_close($handle);
}
?>
</body>
</html>
