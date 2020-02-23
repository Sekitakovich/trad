<?php

$pgconnect = "dbname=hpfmaster user=postgres password=postgres";

if($handle = pg_connect($pgconnect)){
$query = "select id,name from staff";
$qr = pg_query($handle,$query);
$qs = pg_num_rows($qr);

for($a=0; $a<$qs; $a++){
	  $qo = pg_fetch_assoc($qr,$a);
var_dump($qo);
}

	   pg_close($handle);
}

else var_dump($handle);


phpinfo();
?>