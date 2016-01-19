<?php
class Statistics{
	private $u, $ip, $location, $dTime, $ref, $root, $IPs, $image, $xml, $dom;

	//Defines the properties. And creates the XML-statistics file.
	public function __construct($xmlFile){
		$this->u=$_SERVER['HTTP_USER_AGENT'];
		$this->ip=$_SERVER['REMOTE_ADDR'];
		$this->dTime=date("l, F d, Y, H:i:s");
		$this->ref=@$_SERVER['HTTP_REFERER'];
		$this->xml=$xmlFile;
		$this->dom=new DOMDocument("1.0");
		$this->dom->preserveWhiteSpace=false;
		$this->dom->formatOutput=true;
		if(!file_exists($xmlFile)){$this->dom->appendChild($this->dom->createElement("statistics"));$this->dom->save($xmlFile);}
		$this->dom->load($xmlFile);
		$this->root=$this->dom->getElementsByTagName("statistics")->item(0);
		$this->IPs=$this->root->getElementsByTagName("IPs");
	}

	//Checks whether the visitor is a bot.
	private function isBot(){
		if(preg_match('/bot|crawl|spider|slurp/i', $this->u)) {
			return true;
		}
		return false;
	}
	
	//Determines which browser and browser-version the visitor used to access the pages.
	public function browserGet(){
		if(preg_match("/Edge\/([0-9]{1,2})/i", $this->u, $m)){
			$bName = "Microsoft Edge ";
			$bVersion = $m[1];
		}
		elseif(preg_match("/(Chrome|CriOS)\/([0-9]{1,2})/i", $this->u, $m)){
			$bName = "Google Chrome ";
			$bVersion = $m[2];
		}
		elseif(preg_match("/Version\/([0-9]{1,2}).*Safari/i", $this->u, $m)){
			$bName = "Apple Safari ";
			$bVersion = $m[1];
		}
		elseif(preg_match("/Firefox\/([0-9]{1,2})/i", $this->u, $m)){
			$bName = "Mozilla Firefox ";
			$bVersion = $m[1];
		}
		elseif(preg_match("/Opera\/([0-9]{1,2})/i", $this->u, $m)){
			$bName = "Opera ";
			$bVersion = $m[1];
		}
		elseif(preg_match("/MSIE\s([0-9]{1,2})/i", $this->u, $m)){
			$bName = "Internet Explorer ";
			$bVersion = $m[1];
		}
		else{
			$bName = "Other";
			$bVersion = "";
		}

		return "$bName$bVersion";
	}

	//Determines which OS and OS-version the visitor used to access the pages.
	public function osGet(){
		if(preg_match("/Win/i", $this->u)){
			$oName = "Windows ";
			if(preg_match("/NT 10.0/i", $this->u)){
				$oVersion = "10";
			}
			elseif(preg_match("/NT 6.2/i", $this->u)){
				$oVersion = "8";
			}
			elseif(preg_match("/NT 6.1/i", $this->u)){
				$oVersion = "7";
			}
			elseif(preg_match("/NT 6.0/i", $this->u)){
				$oVersion = "Vista";
			}
			elseif(preg_match("/NT 5.2/i", $this->u)){
				$oVersion = "Server 2003";
			}
			elseif(preg_match("/NT 5.1/i", $this->u)){
				$oVersion = "XP";
			}
			elseif(preg_match("/NT 5.0/i", $this->u)){
				$oVersion = "2000";
			}
		}
		elseif(preg_match("/Mac/i", $this->u)){
			$oName = "Mac OS X ";
			if(preg_match("/(OS X 10_11|OS X 10.11)/i", $this->u)){
				$oVersion = "El Capitan";
			}
			elseif(preg_match("/(OS X 10_10|OS X 10.10)/i", $this->u)){
				$oVersion = "Yosemite";
			}
			elseif(preg_match("/(OS X 10_9|OS X 10.9)/i", $this->u)){
				$oVersion = "Mavericks";
			}
			elseif(preg_match("/(OS X 10_8|OS X 10.8)/i", $this->u)){
				$oVersion = "Mountain Lion";
			}
			elseif(preg_match("/(OS X 10_7|OS X 10.7)/i", $this->u)){
				$oVersion = "Lion";
			}
			elseif(preg_match("/(OS X 10_6|OS X 10.6)/i", $this->u)){
				$oVersion = "Snow Leopard";
			}
			elseif(preg_match("/(OS X 10_5|OS X 10.5)/i", $this->u)){
				$oVersion = "Leopard";
			}
			elseif(preg_match("/(OS X 10_4|OS X 10.4)/i", $this->u)){
				$oVersion = "Tiger";
			}
			elseif(preg_match("/(OS X 10_3|OS X 10.3)/i", $this->u)){
				$oVersion = "Panther";
			}
			elseif(preg_match("/(OS X 10_2|OS X 10.2)/i", $this->u)){
				$oVersion = "Jaguar";
			}
			elseif(preg_match("/(OS X 10_1|OS X 10.1)/i", $this->u)){
				$oVersion = "Puma";
			}
			elseif(preg_match("/(OS X 10_0|OS X 10.0)/i", $this->u)){
				$oVersion = "Cheetah";
			}
			else{
				$oVersion = "";
			}
		}
		elseif(preg_match("/Linux/i", $this->u)){
			$oName = "Linux";
			$oVersion = "";
		}
		elseif(preg_match("/Unix/i", $this->u)){
			$oName = "Unix";
			$oVersion = "";
		}
		else{
			$oName = "Other";
			$oVersion = "";
		}
		if(preg_match("/Android\s([0-9].[0-9]{1,1})/i", $this->u, $m)){
			$oName = "Android ";
			$oVersion = $m[1];
		}
		elseif(preg_match("/(iPad|iPhone|iPod).*?OS\s([0-9]{1})/i", $this->u, $m)){
			$oName = "$m[1] ";
			$oVersion = "iOS $m[2]";
		}
		return "$oName$oVersion";
	}

	//Determines the visitor's location based on IP. Location is based on freegeoip.net's free API.
	public function locationGet($ip=NULL){
		$json = json_decode(file_get_contents("http://ip-api.com/json/".$ip));
		if(!empty($json->country)){
			$this->location = $json->country;
			if(!empty($json->city)){
				$this->location .= ", ".$json->city;
			}
		}
		return $this->location;
	}

	//Creates an array with the value of a given tag ($tagName)
	private function arrTagVal($tagName){
		for($i=0;$i<$this->IPs->length;$i++){
			$arr[] = $this->IPs->item($i)->getElementsByTagName($tagName)->item(0)->nodeValue;
		}
		return $arr;
	}

	//Sorts the array containing the value of $tag and its occurrence which is created with arrTagVal().
	private function arrSort($tag){
		$arr=array_count_values($this->arrTagVal($tag));
		array_multisort($arr,SORT_DESC,array_keys($arr),SORT_ASC);
		return $arr;
	}

	//Creates an array with key and value
	private function arrKeyValCreate($arr, $keyName, $valName){
		foreach($arr as $key => $val){
			$uArr[] = array($keyName=>$key, $valName=>$val);
		}
		return $uArr;
	}

	//Collects the IP, this "IP's" total visits, date & time, browser, OS and possibly-referrer.
	public function dataCollect(){
		if($this->isBot()===false){
			$xPath = new DOMXPath($this->dom);
			$ipAtt = $xPath->query("//IPs[ip='$this->ip']");
			if($ipAtt->length>=1){
				$ipAtt->item(0)->getElementsByTagName("location")->item(0)->nodeValue=$this->locationGet($this->ip);
				$ipAtt->item(0)->getElementsByTagName("count")->item(0)->nodeValue++;
				$ipAtt->item(0)->getElementsByTagName("time")->item(0)->nodeValue=$this->dTime;
				$ipAtt->item(0)->getElementsByTagName("browser")->item(0)->nodeValue=$this->browserGet();
				$ipAtt->item(0)->getElementsByTagName("os")->item(0)->nodeValue=$this->osGet();
				if(isset($this->ref)){
					$ipAtt->item(0)->getElementsByTagName("referrer")->item(0)->nodeValue=$this->ref;
				}
			}
			else{
				$nRoot = $this->root->insertBefore($this->dom->createElement("IPs"),$this->IPs->item(0));
				$nRoot->appendChild($this->dom->createElement("ip",$this->ip));
				$nRoot->appendChild($this->dom->createElement("location",$this->locationGet($this->ip)));
				$nRoot->appendChild($this->dom->createElement("count","1"));
				$nRoot->appendChild($this->dom->createElement("time",$this->dTime));
				$nRoot->appendChild($this->dom->createElement("browser",$this->browserGet()));
				$nRoot->appendChild($this->dom->createElement("os",$this->osGet()));
				$nRoot->appendChild($this->dom->createElement("referrer",$this->ref));
				$nRoot->appendChild($this->dom->createElement("visit",$this->dTime));
			}
			$this->dom->save($this->xml);
		}
	}

	//Defines the $image property.
	private function imgCreate($width, $height, $destroy=false){
		if($destroy==true){
			$this->imgDestroy();
		}
		$this->image = imagecreatetruecolor($width, $height);
		imagecolortransparent($this->image,imagecolorallocate($this->image,0,0,0));
	}

	//Returns the $image property.
	private function imgGet(){
		return $this->image;
	}

	//Destroys the image.
	private function imgDestroy(){
		imagedestroy($this->imgGet());
	}

	//Increases the height of the pie-chart image if the unique-data is more than $i.
	private function imgHeightIncrease($tag, $i=12, $j=0){
		while($i <= count($this->arrSort($tag))){
			$j+=25;
			$i++;
		}
		return $j;
	}

	//Generates random colours for the pie-chart.
	private function colorCreate(){
			return imagecolorallocate($this->imgGet(),rand(1,255),rand(1,255),rand(1,255));
	}

	//Creates a pie-chart of the given data.
	public function pieCreate($tag, $fileName){
		echo "<p><table width='50%' style='border:1px solid #000;' cellspacing='15'><tr><td><b>".ucfirst($tag)."</b></td><td><b>Amount</b></td><td><b>Percentage</b></td></tr>";

		$arrSort = $this->arrSort($tag);
		$pieS = $txtCor = 0;
		$pArr = $this->arrKeyValCreate($arrSort, 'name', 'pct');

		for($i=0;$i<count($pArr);$i++){
			$curColor = $this->colorCreate();
			$pct = round($pArr[$i]['pct'] / $this->IPs->length * 100, 1);
			$pCor = round($pct / 100 * 360);
			if($pieS==0){$pieE = $pCor;}
			echo "<tr><td>".$pArr[$i]['name']."</td><td>".$pArr[$i]['pct']."</td><td>$pct%</td></tr>";
			imagefilledarc($this->imgGet(), 149,149, 300,300, $pieS, $pieE, $curColor, IMG_ARC_PIE);
			imagefilledrectangle($this->imgGet(), 310, $txtCor+3, 320, $txtCor+13, $curColor);
			imagestring($this->imgGet(), 5, 325, $txtCor, $pArr[$i]['name'].": ".$pct."%", $curColor);
			$pieS=$pieE;
			if(isset($pArr[$i+1]['pct'])){$pieE+=round(($pArr[$i+1]['pct'] / $this->IPs->length * 360), 1);}
			$txtCor=$txtCor+25;
		}
		imagepng($this->imgGet(),strtok($this->xml, '.').$fileName.".png");
		echo "<img src='".strtok($this->xml, '.').$fileName.".png' />";
		echo "</table></p>";
	}

	//Returns the number of unique visitors.
	public function visitUnique(){
			return number_format($this->IPs->length);
	}

	//Returns the number of total visits (i.e. how many times anyone has visited the pages).
	public function visitsTotal(){
		for($i=0;$i<$this->IPs->length;$i++){
			$countSum[] = $this->IPs->item($i)->getElementsByTagName("count")->item(0)->nodeValue;
		}
		return number_format(array_sum($countSum));
	}

	//Displays the number of unique visitors and total visits.
	private function allVisitsDisplay(){
		echo "Total visits: ".$this->visitsTotal()."<br />";
		echo "Total unique visits: ".$this->visitUnique()."<br />";
	}

	//Displays the collected data in text.
	public function ipDetailDisplay(){
		echo "<p><table width='100%' style='border:1px solid #000;' cellspacing='15'><tr><td colspan='8'><h1 align='center'>Detail of every IP</h1></td></tr><tr><td><b>IP</b></td><td><b>Location</b></td><td><b>Visits</b></td><td><b>Time</b></td><td><b>Browser</b></td><td><b>Operating systems</b></td><td><b>Referrer</b></td><td><b>First visit</b></td></tr>";
		foreach($this->IPs as $ipChild){
			echo "<tr><td>".$ipChild->getElementsByTagName("ip")->item(0)->nodeValue."</td>";
			echo "<td>".$ipChild->getElementsByTagName("location")->item(0)->nodeValue."</td>";
			echo "<td>".number_format($ipChild->getElementsByTagName("count")->item(0)->nodeValue)."</td>";
			echo "<td>".$ipChild->getElementsByTagName("time")->item(0)->nodeValue."</td>";
			echo "<td>".$ipChild->getElementsByTagName("browser")->item(0)->nodeValue."</td>";
			echo "<td>".$ipChild->getElementsByTagName("os")->item(0)->nodeValue."</td>";
			echo "<td>".$ipChild->getElementsByTagName("referrer")->item(0)->nodeValue."</td>";
			echo "<td>".$ipChild->getElementsByTagName('visit')->item(0)->nodeValue."</td></tr>";
		}
		echo "</table></p>";
	}

	//Displays the data in one method.
	public function dataDisplay(){

		$this->allVisitsDisplay();

		$this->imgCreate(650, 300+$this->imgHeightIncrease('os'));
		$this->pieCreate('os','O');

		$this->imgCreate(650, 300+$this->imgHeightIncrease('browser'), true);
		$this->pieCreate('browser','B');

		$this->imgCreate(650, 300+$this->imgHeightIncrease('location'), true);
		$this->pieCreate('location','L');

		$this->imgDestroy();

		if(isset($_GET['viewAll'])){
			$this->ipDetailDisplay();
		}
		else{
			echo "<a href='?viewAll'>Detail of every IP</a>";
		}
	}
}
