<?php
/*error_reporting(-1);
ini_set('display_errors', true);*/
require_once 'config.php';

if ( ! is_user_logged_in() || ! current_user_can('manage_options')){
    die('Unauthorized');
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Auto Checker</title>
<link href="cupertino/jquery-ui.min.css" rel="stylesheet">
<link href="autochecker.css" rel="stylesheet">
<link href="index.css" rel="stylesheet">
<script src="jquery-1.11.3.min.js"></script>
<script src="jquery-ui.js"></script>
</head>
<body>
<div class="large-box">
	<div class="box-left">
		<span id="box-status">Press stats</span>
		<table>
			<tr>
				<td colspan="10">
					<input type="submit" id="stats_btn" name="" class="button" value="stats" onclick="interval()">
					<input type="submit" id="start_btn" name="" class="button" value="Start" onclick="">
					<input type="submit" id="stop_btn" name="" class="button" value="Stop" onclick="">
				</td>
			</tr>
			<tr class="">
				<td class=""><h3>Overall statistic</h3> </td>
				<td></td>
				<td>
					<label>Removed:</label>
					<label id="deleted_val">0</label>
				</td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td>
					<label id="">Processed:</label>
					<label id="processed_val">0</label>
				</td>
				<td class="">
					<label id="">Post-ID:</label>
					<label id="Post-ID">0</label>
					&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label id="">LastPost-ID:</label>
					<label id="LastPost-ID">0</label>
					<div id="box-bar">
						<div class="progress-container">
							<div id="progress-level"></div>
							<span id="percent">0%</span>
						</div>
					</div>                             
				</td>
			</tr>
		</table>
	</div>
	<div class="box-right">

	</div>
</div>
<script>
	var tout;
	var prc1 = 0;
	var prc2 = 0;
	var fhost ='';
	
	function getStats() {
		  var xhttp = new XMLHttpRequest();
		var d = new Date();
		var t = d.getTime();
		  xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
	
			 obj = JSON.parse(xhttp.responseText);
			console.log(obj);
			 var processed =Math.round((obj["lastLimit"] / obj["maxDbId"])*100);
			 if ( obj["status"] == "stopped" ) {
			  clearTimeout(tout);
			  $( "#box-status" ).css("background-color","#b3d189");
			   $( "#box-status" ).html("stopped");
			  
			 }else if ( obj["status"] == "completed" ) {  
			 clearTimeout(tout);
			  $( "#box-status" ).css("background-color","#b3d189");
			   $( "#box-status" ).html("completed");  
			 }else if($( "#box-status" ).css("background-color")=="rgb(255, 255, 0)"){ 
				 $( "#box-status" ).html("Wait...");
			 }else{
			  $( "#box-status" ).css("background-color","#f33");
			  $( "#box-status" ).html(obj["status"]);
			 }
			 
			 if ( parseInt($( "#Post-ID" ).html()) != obj["lastLimit"] ) {
			  $( "#Post-ID" ).toggle( "highlight" );
			  $( "#Post-ID" ).html(obj["lastLimit"]);
			  $( "#Post-ID" ).toggle( "highlight" );
			  }
	
			  $( "#LastPost-ID" ).html(obj["maxDbId"]);
			  if ( parseInt($( "#deleted_val" ).html()) != obj["deletedLinkNum"] ) {
				  $( "#deleted_val" ).toggle( "highlight" );
				  $( "#deleted_val" ).html(obj["deletedLinkNum"]);
				  $( "#deleted_val" ).toggle( "highlight" );
			  }
			 if ( parseInt($( "#processed_val" ).html()) != obj["processedNum"] ) {
				 $( "#processed_val" ).toggle( "highlight" );
				 $( "#processed_val" ).html(obj["processedNum"]);
				 $( "#processed_val" ).toggle( "highlight" );
			  }
			  
			
	
	$.each( obj.filehosts, function( fhost, stats ){
	
		var found = false;
		$('.hoster-stat').each(function(i, host_stat) {
					 var text = host_stat.innerHTML;
					 if ( text.indexOf(fhost) > 0) {
						 var prcEl=document.getElementById("li_deleted" + fhost);
						 prc1=parseInt(prcEl.innerHTML.match(/\d+/));
						 prcEl.innerHTML='Proc: ' + stats.processed;
						 prc2=parseInt(prcEl.innerHTML.match(/\d+/));
						 document.getElementById("li_checked" + fhost).innerHTML='Del: ' + stats.dead;
						 var diff = prc2-prc1;
						 
						 var opacity
						  if ( stats.processed > 0 ) {
						   opacity = 1-(stats.dead/stats.processed)+0.3;
						  }else{
							  opacity= 1;	 
						  }
						 var opacity = Math.round(opacity * 100) / 100;				 
						host_stat.style='background-color:rgba(228, 228, 228,' + opacity + ');';

						 if(diff>0){
							 $('.hoster-stat').css("border","solid 5px black");
							  host_stat.style='border: solid 5px orange;';
						 }
	
						found=true;
						return false;
					 }
					 });
					 if ( found === false ) {
					 $( ".box-right" ).append( '<div class="hoster-stat"><ul class="hoster-ul"><li id="li_head' + fhost + '"><b>' + fhost + '</b></li><li id="li_deleted' + fhost + '"> Proc: ' + stats.processed + '</li><li id="li_checked' + fhost + '">Del: ' + stats.dead + '</li></ul></div>');
					 }
	
	
	
	});
	
	
		/*	  if (  $('.hoster-stat').length > 0 ) {
			  
	
					   
			  }else{
				  $( ".box-right" ).append( '<div class="hoster-stat"><ul class="hoster-ul"><li id="li_head' + obj.filehost.name + '"><b>' + obj.filehost.name + '</b></li><li id="li_deleted' + obj.filehost.name + '"> Proc: ' + obj.filehost.processed + '</li><li id="li_checked' + obj.filehost.name + '">Del: ' + obj.filehost.dead + '</li></ul></div>');
			  }*/
			  
			  
			  
			  
	
			 
		/*	+ processed + "%" */
	
				var percent = document.getElementById('percent');
				document.getElementById('progress-level').style.width = processed + '%';
				percent.innerHTML = processed + '%';
				
	
			}
		  };
		  xhttp.open("GET", "/autocheck/stats/data.stats?" + t, true);
		  xhttp.send();
		}
	
	$(document).ready(function(){	
		$("#stop_btn").click(function(){
			$( "#box-status" ).css("background-color","yellow");
			$( "#box-status" ).html("Wait...");
			$.post("/autocheck/cmd.php",
			{cmd: "stop.cmd"},
			function(data, status){
			});
		});
	
		$("#start_btn").click(function(){
				interval();
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
				  if (xhttp.readyState == 4 && xhttp.status == 200) {
				  }
				};
				xhttp.open("GET", "/autocheck/autoChecker.php", true);
				xhttp.send();
			});
	});
	
	function interval(){
		clearTimeout(tout);
		tout=setInterval(getStats, 4000);
	}
	</script>
</body>
</html>