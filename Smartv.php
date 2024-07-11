<?php
error_reporting(0);
header('Content-Type: text/json;charset=UTF-8');
date_default_timezone_set("Asia/Shanghai");
    $ip = '127.0.0.1';
    $header=array(
		"CLIENT-IP:".$ip,
		"X-FORWARDED-FOR:".$ip,
	);
	
    $name = $_GET["id"];
    $port = '198.16.100.186:8278';
	$nn = msseg($name);
    $ts = isset($_GET["ts"])?$_GET["ts"]:null;
    $url = "http://".$port."/".$name."/playlist.m3u8?tid={TID}&tsum={TSUM}";
    if(strstr($ts,".ts")){
        $ch = curl_init();
		$msg = $nn.'/'.$name.".Host";
		$hots = file_get_contents($msg);
		$url = $hots.$ts;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$ts = curl_exec($ch);
		header("Date: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Content-Type: video/mp2t");
		header('Content-Length: ' . strlen($ts));
		header("Connection: keep-alive");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: max-age=200");
		header("Server: TVA Streaming Server v2020 r0222");
		header("X-Nginx-Cache: HIT");
		header("Access-Control-Allow-Credentials: true");
		header("Access-Control-Allow-Origin: *");
		header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=".$name.".ts");
		curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
            	echo $data;
           	return strlen($data);
        });
		if (strlen($ts) > 300) {
            echo($ts);
			exit(0);
        }

    }
	else
	{
		
        //$curl = "http://".$port."/crossdomain.xml";
		$heade = array(
		    "CLIENT-IP:".$ip,
		    "X-FORWARDED-FOR:".$ip,
			"User-Agent: Dalvik/2.1.0 (Linux; U; Android 12; 22101316C Build/SP1A.210812.016)",
			//"X-OTT-Session: fe7a0ba31fdeebda397f436d30ec9c2863e9113b",
			"Connection: Keep-Alive",
			"Accept-Encoding: gzip"
	    );
	
		//$ch = curl_init();
		//curl_setopt($ch, CURLOPT_URL, $curl);
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $heade);	
		//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	    //$mian = curl_exec($ch);

		$seed = "tvata nginx auth module";
		$uri = preg_replace(array("/^.*url=/","/^.+?\:\/\/.+?\//","/\?.+$/"),array("","/",""),$url);
		$tid = "mc42afe745533";
		$t = strval(intval(time()/150));
		$str = $seed.$uri.$tid.$t;
		$tsum = md5($str);

		$link = "ct=".$t."&tsum=".$tsum;
		$url = preg_replace("/\{TID\}/",$tid,$url);
		$url = preg_replace("/[tsi]sum=\{[TSI]SUM\}/",$link,$url);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);	
	    $m3u8 = curl_exec($ch);
		if (strpos($m3u8, "EXTM3U")) {
            $m3u8s = explode("\n", $m3u8);
            $m3u8 = '';
            foreach($m3u8s as $v)
			{
                $v = str_replace("\r", '', $v);
                if (strpos($v, ".ts") > 0)
			    {
                    $url = "http://".$port."/".$name.'/'.$v;
				    $m3u8 .= "Smartv.php?id=".$name."&ts=".$v."\n";
                }
		        else{
                        if($v != '') 
						{
                           $m3u8 .= $v . "\n";
			            }
	                }
			}
		}
		$msg = $nn.'/'.$name.".m3u8";
		file_put_contents($msg,$m3u8);
		$msg = $nn.'/'.$name.".Host";
		$ser =  "http://".$port."/".$name."/";
		file_put_contents($msg,$ser);
		header("Content-Type: application/vnd.apple.mpegurl");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Server: TVA Streaming Server v2020 r0222");
		header("Accept-Ranges: bytes");
		header("Connection: keep-alive");
		header('Content-Length: ' . strlen($m3u8));
		header("Content-Disposition: attachment; filename=".$name.".m3u8");
		echo($m3u8);
		exit(0);
    }

	function msseg($id)
    {
	    $a = dirname(__FILE__);
    	$b = $a.'/dl/'.$id;
    	if(is_dir($b) != true)
    	{
	    	mkdir(iconv("UTF-8", "GBK", $b), 0777, true);
	    	$b = $b.'/';
    	}
    	else
    	{
	    	$b = $b.'/';
    	}
	    $str = $b;
	    return $str;
    }

?>