<?php

/**
 *
 *    @author    Alex Dominguez
 *     
 ***/
 
class crawlerWebExample{

    protected $url;
    protected $depth;
	protected $data;
	


	/**
	 * Construct of the class
	 * @param $url string url for the website		
	 * @param $depth integer number of items to be analized  
	 **/
    public function __construct($url, $depth = 5)
    {
        $this->url = $url;
        $this->depth = $depth;
    }//end of function

	/**
	 * Function that recovers data from url
	 *
	 **/
	function get_data(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		return $result = curl_exec($ch);
	}//end of function
	
	/**
	 * Function which applies scrapping to recover the specific data required
	 *
	 **/
	function execute(){
		
		$html = $this->get_data();
		$re = '/^[a-z0-9]{2,}[a-z0-9.]*[a-z0-9]+$/';
		$re = '/<span class="rank">(.*?)<\/span>/s';
		$re = '/<table border="0" cellpadding="0" cellspacing="0" class="itemlist">(.*?)<\/table>/s';
		preg_match_all($re, $html, $trList, PREG_SET_ORDER, 0);
		$index =0;
		$arr_data=array();
		foreach ($trList as $tr) {
			$re = '/<tr(.*?)>(.*?)<\/tr>/s';			
			preg_match_all($re, $tr[0], $out, PREG_SET_ORDER, 0);
			$fila=0;			
			foreach ($out as $tr1) {
				if (strpos($tr1[2], '<span class="rank">') !== false) {
					$re_tmp = '/<a(.*?)>(.*?)<\/a>/s';
					preg_match_all($re_tmp, $tr1[2], $partial_1, PREG_SET_ORDER, 0);
					$arr_data[$fila]['title']=$partial_1[1][2];				
				}else{
					$re_tmp_2 = '/<span class="score"(.*?)>(.*?)<\/span>/s';
					preg_match_all($re_tmp_2, $tr1[2], $partial_2, PREG_SET_ORDER, 0);
					
					if(!empty($partial_2[0][0]))
					{
						$points = explode(' ',$partial_2[0][2]);
						$arr_data[$fila]['points']=$points[0];
						$comments = explode('|',$tr1[2]);
						$re_tmp_3 = '/<a (.*?)>(.*?)<\/a>/s';
						preg_match_all($re_tmp_3,$comments[2], $partial_3, PREG_SET_ORDER, 0);
						$tmp_comments = explode('&nbsp;',$partial_3[0][2]);
						$arr_data[$fila]['comments']=$tmp_comments[0];
						$fila++;
					}

				}
				
				if($fila==$this->depth){
					break;
				}				
								
			}	  
			$index++;

		}		
		
		$this->data = $arr_data;
	}//end of function
	
	/**
	 * Function that sorts the data by number of comments
	 * @param $a array data.
	 **/	
	function sortByComments($a, $b)
	{
		$a = $a['comments'];
		$b = $b['comments'];

		if ($a == $b) return 0;
		return ($a < $b) ? -1 : 1;
	}

	/**
	 * Function that sorts the data by number of points
	 * @param $a array data.
	 **/		
	function sortByPoints($a, $b)
	{
		$a = $a['points'];
		$b = $b['points'];

		if ($a == $b) return 0;
		return ($a < $b) ? -1 : 1;
	}	
	
	/**
	 * Function which applies scrapping to recover the specific data required
	 * @param $option integer option to display results, 0=default, 1=filter and entries with more than five words in the title ordered by the amount of comments first, 2= entries with less than or equal to five words in the title ordered by points.
	 **/
	function printData($option=0){
		$data = $this->data;
		$tmp  = array();
		$cont = 0;
		switch ($option) {
			case 0:
				$tmp = $this->data;
				break;
			case 1:
				for($i=0;$i<count($data);$i++){
					if(str_word_count($data[$i]['title'])>=5){
						$tmp[$cont]['title']    = $data[$i]['title'];
						$tmp[$cont]['points']   = $data[$i]['points'];
						$tmp[$cont]['comments'] = $data[$i]['comments'];
						$cont++;
					}
				}
				usort($tmp,array("crawlerWebExample", "sortByComments"));
				break;
			case 2:
				for($i=0;$i<count($data);$i++){
					if(str_word_count($data[$i]['title'])<=5){
						$tmp[$cont]['title']    = $data[$i]['title'];
						$tmp[$cont]['points']   = $data[$i]['points'];
						$tmp[$cont]['comments'] = $data[$i]['comments'];
						$cont++;
					}
				}
				usort($tmp,array("crawlerWebExample", "sortByPoints"));
				break;
		}
		
		$data = $tmp;
		
		$html = '<table class="table">';
			$html .= '<tr>';
				$html .= '<th>Order';
				$html .= '</th>';			
				$html .= '<th>Title';
				$html .= '</th>';
				$html .= '<th>Points';
				$html .= '</th>';	
				$html .= '<th># Comments';
				$html .= '</th>';					
			$html .= '</tr>';
		for($i=0;$i<count($data);$i++){
			$html .= '<tr>';
				$html .= '<td>';
				$html .= ($i+1);
				$html .= '</td>';			
				$html .= '<td>';
				$html .= $data[$i]['title'];
				$html .= '</td>';
				$html .= '<td>';
				$html .= $data[$i]['points'];
				$html .= '</td>';	
				$html .= '<td>';
				$html .= $data[$i]['comments'];
				$html .= '</td>';					
			$html .= '</tr>';
		}
		$html .= '</table>';
		print_r($html);
		
	}//end of function

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Web Crawler</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
<h1>Web Crawler</h1>            
<nav class="nav" >
 <form method="POST" style="display:flex;">
 <div class="form-group">
	 <label for="rd_filter" >Filter title more or equal than 5 words and by comments</label>
	 <input type="radio" name="rd_filter" value="1" />
 </div>
 <div class="form-group">
	 <label for="rd_filter2" >Filter title less or equal than 5 words and by points</label>
	 <input type="radio" name="rd_filter" id="rd_filter2" value="2" />
  </div>	
 <div class="form-group">
	<button name="btnFilter" class="btn btn-primary">Filter</button>
  </div>	
 </form>  
</nav>
<?php

$url   = "https://news.ycombinator.com/";
$depth = 30;
$objCrawler      = new crawlerWebExample($url,$depth);
$objCrawler->execute();


if(isset($_REQUEST['btnFilter'])){
	if(isset($_REQUEST['rd_filter'])){
		switch ($_REQUEST['rd_filter']) {
			case 1:
				$objCrawler->printData(1);
				break;
			case 2:
				$objCrawler->printData(2);
				break;
			default:
				$objCrawler->printData(0);
				break;			
		}
		
	}
	
}else{
	$objCrawler->printData(0);
}


?>
</div>

</body>
</html>

