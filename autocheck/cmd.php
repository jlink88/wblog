<?php
$root=dirname(__FILE__).DIRECTORY_SEPARATOR;

if ( $_POST["cmd"]== "stop.cmd" ){
	file_put_contents($root."stop.cmd","");
}elseif( $_POST["cmd"]== "restart.cmd"){
@unlink($root."stats".DIRECTORY_SEPARATOR."data.stats");
}

?>
