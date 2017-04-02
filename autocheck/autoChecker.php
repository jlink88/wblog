<?php

/**
* Created by kaox.
* Date: 23/02/2016
* v. 1.00
*/

set_time_limit(0);

require_once "config.php";
require_once $classPath . "reLink". $ds . "Parse_Url.php";
require_once $includesPath . "functions.php";
include("stats.php");
include("class/RollingCurl/RollingCurl.php");
include("class/RollingCurl/Request.php");

use \reLink\Parse_Url;

if(file_exists($root.$ds."running.status")){
    die("Already running!");
}else{
    file_put_contents($root.$ds."running.status","");
}

$stats= new stats();
$stepIndex=200;
$maxNumCheck=50;
$inc=0;
$lastTime=0;
$filehosts=array();
$DBresponse=array();
$arrayLinkBad=array();
$links=array();   
$conn = new mysqli($DBhost, $DBuser, $DBpass, $DBname) or die ('Could not connect to the database server' . mysqli_connect_error());

$cc=1;

statistic($inc);

while($inc < $stats->maxDbId()){

    getDblinks($inc);

    foreach($filehosts as $service=>$filehost){

        if(count($filehost->storeLinks)>=$maxNumCheck){
            $toDelete= checkLinks($filehost->storeLinks,$service);
            if(is_array($filehost->storeLinks)){
                $stats->processedNum(count($filehost->storeLinks));  
            }
            $filehost->processed(count($filehost->storeLinks));
            if(is_array($toDelete) && count($toDelete)>0){
                $tdel=join("\r\n",$toDelete);

                foreach($toDelete as $id => $lk){
                    mysqli_data_seek($DBresponse,0);
                    while($row = $DBresponse->fetch_assoc()) {                      
                        if($row[$tableFieldNames['link_id']]== $id){
                            $camp[$tableFieldNames['link_url']]=mysqli_real_escape_string($conn,$row[$tableFieldNames['link_url']]);
                            $link_url_bad=mysqli_real_escape_string($conn,$arrayLinkBad[$id]);
                            $insert_queryes[] = "INSERT INTO $badLinksTable ( link_id,post_id,link_url,link_url_bad,link_submitter,date_added ) VALUES (\"{$row[$tableFieldNames['link_id']]}\",\"{$row[$tableFieldNames['post_id']]}\",\"{$camp[$tableFieldNames['link_url']]}\",\"{$link_url_bad}\",\"{$row[$tableFieldNames['link_submitter']]}\",\"{$row['date_added']}\");";
                            break;
                        }
                    }             
                    $strIds.=$id.",";  
                    $linksToDelete.=$lk."\r\n";
                    $count++;
                }

                foreach($insert_queryes as $query2){
                    if ($conn->query($query2) === TRUE) {
                        //  file_put_contents($root."stats".$ds."wp_bad_links.txt",$query2."\r\n",FILE_APPEND);   
                    }

                }  

                if($count>0){
                    $strIds=trim($strIds,",");

                    $query3 = "DELETE FROM $linksTable WHERE {$tableFieldNames['link_id']} IN ($strIds)";
                    if ($conn->query($query3) === TRUE) {
                        $stats->deletedLinkNum(count($toDelete));

                    } else {
                        echo "Error deleting record: " . $conn->error;
                    }
                }
            }
            $stats->activeLinkNum(count($filehost->storeLinks)-count($toDelete));
            $filehost->alive(count($filehost->storeLinks)-count($toDelete));
            $filehost->dead(count($toDelete));
            $filehosts[$service]->clearLinks();


        }

        foreach($filehosts as $service => $data){
            $stats2=(array)$data;
            unset($stats2["storeLinks"]);
            $filehosts_stats[$service]=$stats2;  
        }

        $output=array(
            "processedNum" => $stats->processedNum(),
            "deletedLinkNum" => $stats->deletedLinkNum(),
            "lastLimit"=>$stats->activeLinkNum(),
            "maxDbId"=>$stats->maxDbId(),
            "lastLimit"=>$inc,
            "lastTime"=>$lastTime,
            "service"=>$service,
            "filehosts"=>$filehosts_stats,
            "status"=>"running"
        );

        file_put_contents($statsPath,json_encode($output));
        file_put_contents($root."stats".$ds."deleted.txt",$linksToDelete,FILE_APPEND);

        $links="";
        $strIds="";
        $linksToDelete="";
        $count=0;

        if(file_exists($root.$ds."stop.cmd")){ 
            @unlink($root.$ds."stop.cmd");
            @unlink($root.$ds."running.status");
            $output=array(
                "processedNum" => $stats->processedNum(),
                "deletedLinkNum" => $stats->deletedLinkNum(),
                "lastLimit"=>$stats->activeLinkNum(),
                "maxDbId"=>$stats->maxDbId(),
                "lastLimit"=>$inc,
                "lastTime"=>$lastTime,
                "service"=>$service,
                "filehosts"=>$filehosts_stats,
                "status"=>"stopped"
            );

            file_put_contents($statsPath,json_encode($output));
            file_put_contents($root."stats".$ds."deleted.txt",$linksToDelete,FILE_APPEND);

            die();
        }

        //sleep(1); //  waiting time between filehost
    }
    $inc+=$stepIndex;
    //sleep(1);  //  waiting time between request
} 

$output=array(
    "processedNum" => $stats->processedNum(),
    "deletedLinkNum" => $stats->deletedLinkNum(),
    "lastLimit"=>$stats->maxDbId(),
    "maxDbId"=>$stats->maxDbId(),
    "lastLimit"=>$inc,
    "lastTime"=>$lastTime,
    "service"=>$service,
    "filehosts"=>$filehosts_stats,
    "status"=>"completed"
);

file_put_contents($statsPath,json_encode($output));
file_put_contents($root."stats".$ds."deleted.txt",$linksToDelete,FILE_APPEND);

@unlink($root.$ds."stop.cmd");
@unlink($root.$ds."running.status");
$conn->close();




function checkLinks($links,$service){
    global $filehosts,$arrayLinkBad;
    // $service=str_ireplace("www.","",$service);
    $badlinks=array();
    $badResult=array();
    $result=array();
    foreach($links as $lk=>$id){
        if(badLkCk($lk)){
            $badlinks[$id]=$lk;
            $arrayLinkBad[$id]="link not approved from badLkCk function"; 
            unset($links[$lk]);
        }              
    }

    $urls=array_keys($links);
    /**/
    foreach($urls as $url){
        $llks.=$url."\r\n";
    }


    file_put_contents("last_links.txt",$llks);      
    $llks="";
    /**/
    switch ( $service ) {

        case "xvidstage.com":
            $hostPattern="<b>File Not Found</b>";
            foreach($urls as $url){
                $page=curlreq($url); // changed to follow location = true ,"","","" ,false,false,false

                preg_match('/^HTTP\/1\.0|1 ([0-9]+) .*/',$page,$status);

                if ($status[1] == 200 || $status[1] == 404) {
                    if (stripos($page ,$hostPattern)!== false){
                        $result[]=$url;
                        $badResult[]=$hostPattern;
                    }
                }
            }
            break;

        case "openload.co":
            require_once dirname(__FILE__) . "/hosts/openload.co.php";
            break;

        case "vshare.eu":

            $scn = new RollingCurl\RollingCurl();
            foreach($urls as $url){
                $scn->get($url);
                $scn->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$result,&$badResult) {
                    $hostPattern=">The file you were looking for could not be found";
                    $responseInfo=$request->getResponseInfo();
                    if( stripos( $request->getResponseText() ,$hostPattern)!== false && $responseInfo["http_code"] == 200 ){
                        $result[]=$request->getUrl();
                        $badResult[]=$hostPattern;
                    }else{
                    }
                });
                $scn->setOptions(array(CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_USERAGENT => "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "gzip,deflate"));
                $scn->execute();
            }
            break;

        case "streamin.to":

            $scn = new RollingCurl\RollingCurl();
            foreach($urls as $url){
                $scn->get($url);
                $scn->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$result,&$badResult) {
                    $hostPattern="<title>File Removed</title>";
                    $responseInfo=$request->getResponseInfo();
                    if( stripos( $request->getResponseText() ,$hostPattern)!== false && $responseInfo["http_code"] == 200 ){
                        $result[]=$request->getUrl();
                        $badResult[]=$hostPattern;
                    }elseif(stripos( $request->getResponseText() ,"<title>Attention Required! | CloudFlare</title>")!== false && $responseInfo["http_code"] == 403){
                        /*  blocked from cloudfare */
                    }
                });
                $scn->setOptions(array(CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "gzip,deflate"));
                $scn->execute();
            }
            break;

        case "uploaded.net":

            /*  $scn = new RollingCurl\RollingCurl();
            foreach($urls as $url){
            $scn->get($url);


            $scn->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$result,&$badResult) {
            if($rollingCurl != null && $request != null){
            $responseInfo=$request->getResponseInfo();
            if( stripos( $responseInfo["redirect_url"] ,"uploaded.net/404")!== false && $responseInfo["http_code"] == 302 ){
            $result[]=$request->getUrl();
            $badResult[]=$responseInfo["redirect_url"];
            }
            }else{
            }
            });
            $scn->setOptions(array(CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT => "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
            CURLOPT_ENCODING => "gzip,deflate"));
            $scn->execute();
            }*/

            foreach($urls as $url){
                $page=curlreq($url);

                preg_match('/^HTTP\/1\.0|1 ([0-9]+) .*/',$page,$status);

                if ($status[1] == 302) {
                    $locat=cut_str ($page ,"Location: ","\r");
                    if (stripos($page ,"uploaded.net/404")!== false){
                        $result[]=$url;
                        $badResult[]=$locat;
                    }
                }elseif($status[1] == 404){
                    $result[]=$url;
                    $badResult[]="404 Not Found.";
                }
            }

            break;

        case "filefactory.com":
            $scn = new RollingCurl\RollingCurl();
            foreach($urls as $url){
                $scn->get($url);
                $scn->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$result,&$badResult) {
                    //		file_put_contents(dirname(__FILE__)."/step.txt",serialize($request->getResponseInfo())."\r\n\r\n",FILE_APPEND);           
                    $responseInfo=$request->getResponseInfo();
                    if( stripos( $responseInfo["redirect_url"] ,"error.php?code=25")!== false && $responseInfo["http_code"] == 302 ){
                        $result[]=$request->getUrl();
                        $badResult[]=$responseInfo["redirect_url"];
                    }else{
                    }
                });
                $scn->setOptions(array(CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "gzip,deflate"));
                $scn->execute();
            }
            break;

        case "filehoot.com":

            $pattern='<tr><td>(.*?)</td><td style="color:red;">';
            $urls=join("\r\n",$urls);
            $Url="http://$service/?op=checkfiles";
            $post["op"]="checkfiles";
            $post["list"]=urlencode($urls);
            $post["process"]="Check+URLs";
            $page=curlreq($Url,$Url,0,$post);
            preg_match_all('#'.$pattern.'#im', $page, $result, PREG_PATTERN_ORDER);
            $badResult =$result[0];
            $result = $result[1];
            break;   

        case "vidbull.com":
            $urls=join("\r\n",$urls);
            $Url="http://$service/checkfiles.html";
            $post["op"]="checkfiles";
            $post["list"]=urlencode($urls);
            $post["process"]="Check+URLs";
            $page=curlreq($Url,$Url,0,$post);
            preg_match_all('#<tr><td>(.*?)</td><td style="color:red;">#im', $page, $result, PREG_PATTERN_ORDER);
            $badResult =$result[0];
            $result = $result[1];
            break;

        case "vodlocker.com":

            $Url="http://$service/checkfiles.html";
            $urls2=join("\r\n",$urls);
            $post["op"]="checkfiles";
            $post["list"]=urlencode($urls2);
            $post["process"]="Check+URLs";
            $page=curlreq($Url,$Url,0,$post);

            if(preg_match('#<font color=\'red\'>(.*?) #im', $page)){

                preg_match_all('#<font color=\'red\'>(.*?) #im', $page, $result, PREG_PATTERN_ORDER);
                $badResult =$result[0];
                $result = $result[1];

            }elseif(preg_match('#<font color=\'green\'>(.*?) #im', $page)){

                preg_match_all('#<font color=\'green\'>(.*?) #im', $page, $result2, PREG_PATTERN_ORDER);
                $result2 = join("\r\n", $result2[1]);
                $urls2="";
                foreach ($urls as $lk){
                    if(stripos($result2,trim($lk))===false){
                        $result[]=$lk;
                        $badResult[] ="not match with Regex = [".$pattern. "]";
                    }
                }
            }
            break;

        case "flashx.tv":
        case "flashx.cc":

            foreach ($urls as $idx => $url){

                if(stripos($urls[0],"http://flashx.tv/video/")!== false || stripos($urls[0],"http://flashx.cc/video/")!== false){
                    $result[] = $url;  
                    $badResult[] = "old link"; 
                }else{
                    $urls2[]=$url;
                }
            }         
            if(is_array($urls2) && count($urls2)>0){
                $urls=join("\r\n",$urls2);
                $Url="http://flashx.cc/checkfiles.html";
                $post["op"]="checkfiles";
                $post["list"]=urlencode($urls);
                $post["process"]="Check+URLs";
                $page=curlreq($Url,$Url,0,$post);
                $pattern='<font color=\'red\'>(.*?) ';
                preg_match_all('#'.$pattern.'#im', $page, $result, PREG_PATTERN_ORDER);
                $badResult2 =$result[0];
                $result2 = $result[1];  
            }

            if(!is_array($result)){
                $result=array();
                $badResult=array();
            }   
            if(!is_array($result2)){
                $result2=array();
                $badResult2=array();
            }     
            $result=array_merge($result,$result2);
            $badResult=array_merge($badResult,$badResult2);

        $hostPattern="<b>File Not Found</b>";
        foreach($urls as $url){
            $page=curlreq($url);

            preg_match('/^HTTP\/1\.0|1 ([0-9]+) .*/',$page,$status);

            if ($status[1] == 200) {
                if (stripos($page ,$hostPattern)!== false){
                    $result[]=$url;
                    $badResult[]=$hostPattern;
                }
            }
        }

            break;


        case "videowood.tv":
            $hostPattern="<strong>This video doesn't exist. It may be one of these reasons:</strong>";
            foreach($urls as $url){
                $page=curlreq($url); // changed to follow location = true ,"","","" ,false,false,false

                preg_match('/^HTTP\/1\.0|1 ([0-9]+) .*/',$page,$status);

                if ($status[1] == 200 || $status[1] == 404 ) {
                    if (stripos($page ,$hostPattern)!== false){
                        $result[]=$url;
                        $badResult[]=$hostPattern;
                    }
                }
            }
            break;
        case "idowatch.net":
        case "vidzi.tv":
        case "allmyvideos.net":
        case "vid.ag":
        case "gorillavid.in":
        case "daclips.in":
        case "movpod.in":
        case "vidspot.net":
        case "youwatch.org":
        case "vidto.me":
        case "streamcloud.eu":
        case "vidup.me":
        case "letwatch.us":
        case "bestreams.net":
        case "exashare.com":
        case "yourvideohost.com":
        case "goodvideohost.com":
        case "rapidvideo.ws":
            $urls=join("\r\n",$urls);
            $Url="http://$service/checkfiles.html";
            $post["op"]="checkfiles";
            $post["list"]=urlencode($urls);
            $post["process"]="Check+URLs";
            $page=curlreq($Url,$Url,0,$post);
            $pattern='<font color=\'red\'>(.*?) ';
            preg_match_all('#'.$pattern.'#im', $page, $result, PREG_PATTERN_ORDER);
            $badResult =$result[0];
            $result = $result[1];
            break;
        case "go4up.com":
            // NADA
            break;
        case "thevideo.me":
            $hostPattern = "<h2 class=\"text-center\">File Not Found</h2>";
            foreach($urls as $url){
                $page=curlreq($url); // changed to follow location = true ,"","","" ,false,false,false

                preg_match('/^HTTP\/1\.0|1 ([0-9]+) .*/',$page,$status);

                if ($status[1] == 200 || $status[1] == 404 ) {
                    if (stripos($page ,$hostPattern)!== false){
                        $result[]=$url;
                        $badResult[]=$hostPattern;
                    }
                }
            }
            break;
        default:
            break;
    }

    if(is_array($result) && count($result)>0){
        foreach($result as $idx => $lk){
            if($links[$lk]>0){
                $data[$links[$lk]]=$lk;
                $arrayLinkBad[$links[$lk]]=$badResult[$idx];
            }

        } 

    }else{
        $data=array();
    }

    if(is_array($badlinks) && count($badlinks)>0){
        foreach($badlinks as $id => $lk){
            $data[$id]=$lk;
        } 
    } 

    if(!is_array($data)){
        return array();   
    }else{
        return $data;
    }



}  

function badLkCk($link)
{
    $parsed = parse_url($link);

    if (isset($parsed['query']) && strlen($parsed['query']) > 2) {
        return false;
    }

    if ($_REQUEST['ignExt'] == true) {
        $parsed['path'] = preg_replace("/\\.[^.\\s]{3,4}$/", "", $parsed['path']);
    }
    return !isset($parsed['host']) || $parsed['path'] === null || $parsed['path']
    === '/' || $parsed['path'] === '/video' || $parsed['path'] === '/video/' || $parsed['path']
    === '/media/' || $parsed['path'] === '/media';
}

function curlreq($link,$refer = "", $cookie = "",$post = "" ,$multipart = false,$flw = false,$ckj=false)
{
    global $cpath;
    $Url=parse_url($link);
    $domain = str_replace("www.","",$Url["host"]);
    $prot=$Url["scheme"];
    $mm = !empty($post) ? 1 : 0;
    $ch = curl_init();
    $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $header[] = "Accept-Language: en-us,en;q=0.8,en-us;q=0.5,en;q=0.3";
    $header[] = "Expect: ";
    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_ENCODING,'gzip,deflate');
    if ( $ckj ){
        $pp = dirname(__FILE__);
        $path .= $pp . DIRECTORY_SEPARATOR."cookie" . DIRECTORY_SEPARATOR;
        if ( !file_exists($path)){ mkdir($path);}
        $cpath = $path . "$domain.txt";

        if ( !file_exists($cpath)){
            file_put_contents($cpath,"");
        }
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cpath);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cpath);
    }elseif ($cookie != "" ){
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    if ($mm == 1)
    {
        if(is_array($post) && !$multipart){$post=formpostdata($post);}
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    if ($refer!= "" )curl_setopt($ch, CURLOPT_REFERER, $refer);
    if ($prot=="https"){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $flw);
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents;
}


function formpostdata($post)
{
    $postdata = "";
    foreach ($post as $k => $v) {
        $postdata .= "$k=$v&";
    }
    // Remove the last '&'
    $postdata = substr($postdata, 0, -1);
    return $postdata;
}

function ifStop(){
    global $root;
    return file_exists($root."stop.cmd") ? true : false;
}

function getDblinks($index){
    global $conn,$filehosts,$stepIndex,$DBresponse, $tableFieldNames, $linksTable;
    //  $query1 = "SELECT post_id, link_id, link_url FROM wp_posts_links WHERE link_url LIKE 'http://$service%' OR link_url LIKE 'http://www.$service%' LIMIT $index , $limit";

    $query1 = "SELECT * FROM $linksTable WHERE  {$tableFieldNames['post_id']} > $index AND {$tableFieldNames['post_id']} <= ".($index+$stepIndex);

    $DBresponse = $conn->query($query1);

    if ($DBresponse->num_rows > 0) {  

        while($row = $DBresponse->fetch_assoc()) {
            $singleUrl = $row[$tableFieldNames['link_url']];
            $linkId = $row[$tableFieldNames['link_id']];
            otherHosts( $singleUrl );
            otherTests( $singleUrl,$linkId);
            foreach ($filehosts as $filehost){
                $linkUrl=trim($row[$tableFieldNames['link_url']]);
                $service=urlToHost($row[$tableFieldNames['link_url']]);
                if($service == $filehost->getName()){
                    $filehost->storeLinks($linkUrl,$linkId);
                    // $filehosts[$service]["links"][$row["link_url"]]= $row["link_id"];
                    break;
                }

            }

            //   $links[$row["link_url"]]=$row["link_id"];

        }
    }else{
        return false;
    }
    return $links;
}


function statistic(&$inc){
    global $stats,$lastTime,$statsPath,$conn,$services,$filehosts, $tableFieldNames, $linksTable;

    $spanTime=24*60*60; /* one day in seconds */

    if(file_exists($statsPath)){
        $output=file_get_contents($statsPath); 
        if(trim($output)!= ""){
            $output=json_decode($output,true);
            if(isset($output["lastLimit"])){
                $inc=$output["lastLimit"];
                if(isset($output["activeLinkNum"]) && $output["activeLinkNum"]>0){
                    $stats->activeLinkNum($output["activeLinkNum"]);   
                } 
            }else{
                $inc=0; 
            }
            if(isset($output["lastTime"])){
                $lastTime=$output["lastTime"];                
            }else{
                $lastTime=time();
            }

            if(isset($output["maxDbId"])){
                $maxDbId=$output["maxDbId"];                
            }else{
                $maxDbId=0;
            }

        }else{
            $inc=0; 
            $lastTime=time();
        } 
    }else{
        $output="";
        $inc=0; 
        $lastTime=time();
    }
    $timeDiff=time()-$lastTime;

    $query="SELECT {$tableFieldNames['post_id']} FROM $linksTable WHERE {$tableFieldNames['post_id']} = (SELECT MAX({$tableFieldNames['post_id']}) FROM $linksTable)";
    // $query="SELECT COUNT(*) AS id FROM wp_posts_links";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $maxDbId=intval($row[$tableFieldNames['post_id']]);
    $stats->maxDbId($maxDbId);

    foreach ($services as $service){

        $filehost =new filehost();
        $filehost->setName($service);
        $filehosts[$service]=$filehost;
    }

}

function cut_str($str, $left, $right)
{
    $str = substr(stristr($str, $left), strlen($left));
    $leftLen = strlen(stristr($str, $right));
    $leftLen = $leftLen ? -($leftLen) : strlen($str);
    $str = substr($str, 0, $leftLen);
    return $str;
}


?>

