<?php
$config_file = file_get_contents('config/sites.json');
$sites=json_decode($config_file);
$idSite=$_GET['id'];
$err='';


class Site
{
	public  $sid;
	public  $sData;

	
	function __construct($sid=null,$sData=null) {
		$this->sid=$sid;
		$this->sData=$sData;
	}	
	
	
	public function check()
	{
    //print_r($this->sData) ;
		foreach ($this->sData->urls as $key => $u) {	
			$request = curl_init();
			curl_setopt($request, CURLOPT_URL, $u->url);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($request, CURLOPT_HEADER,1);
			$result = curl_exec ($request);
			curl_close($request);

			$pos = strpos($result, 'HTTP/1.1 200 OK');
			$keywordsStatus='';

			if ($pos === false) {
				$this->sData->urls[$key]->status="Error";
				$keywordsStatus='Error';
			} else {
				$this->sData->urls[$key]->status="200";
				$keywordsStatusTmp='';
				foreach ($u->keywords as $kkey => $k) {	
					$pos = strpos($result, $k->keyword);

					if ($pos === false) {
						$this->sData->urls[$key]->keywords[$kkey]->status="not found";
						$keywordsStatusTmp.='0';
					} else {
						$this->sData->urls[$key]->keywords[$kkey]->status="OK";
						$keywordsStatusTmp.='1';
					}
				}
				if (strpos($keywordsStatusTmp,'1') !== false && strpos($keywordsStatusTmp,'0') !== false){
					$keywordsStatus='Warning';
				}
				elseif (strpos($keywordsStatusTmp,'1') !== false){
					$keywordsStatus='OK';
				}
				elseif (strpos($keywordsStatusTmp,'0') !== false){
					$keywordsStatus='error';
				}
			}
			
			
			
			$this->sData->urls[$key]->keywordsStatus=$keywordsStatus;
		}

		$this->sData->logs->SERVER_ADDR=$_SERVER['SERVER_ADDR'];
		$this->sData->logs->HTTP_HOST=$_SERVER['HTTP_HOST'];
		$this->sData->logs->REQUEST_TIME=$_SERVER['REQUEST_TIME'];
		$this->sData->logs->REQUEST_TIME_h=date(DATE_ATOM,$_SERVER['REQUEST_TIME']);
		echo json_encode($this->sData,JSON_UNESCAPED_SLASHES);	
	}
}


$pattern='/\w+$/';
if (!empty($idSite) && preg_match($pattern,$idSite)){
	foreach($sites->sites as $site)
	{	

		if($site->site->id == $idSite)
		{
			$checkSite=new Site($idSite,$site->site);
			$checkSite->check();
			$err='';
			break;
		}
		else{
			$err='Illegal ID';
		}
	}
}else{
	$err='Illegal ID';
}
echo $err;


?>