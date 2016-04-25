#!/usr/local/bin/php
<?php
	function getdata($stop, $seq) {
		$host = "etav2.kmb.hk";
		$page = "/?action=geteta&lang=en&route=91M&bound=1&stop=";
		$page .= $stop;
		$page .= "&stop_seq=";
		$page .= $seq;
		$fp = fsockopen( "$host", 80, &$errno, &$errdesc);
		if ( ! $fp )
			die ( "Couldn't connect to $host:\nError: $errno\nDesc: $errdesc\n" );
		$request = "GET $page HTTP/1.0\r\n";
		$request .= "Host: $host\r\n";
		$request .= "User-Agent: PHP test client\r\n\r\n";
		$page = array();
		fputs ( $fp, $request );
		while ( ! feof( $fp ) )
			$page[] = fgets( $fp, 1024 );
		fclose( $fp );
		return $page[count($page)-1];
	}
	
	$filename = '91M_1.csv';
	$regex1 = '/"response":\[(.*)\]/';
	$regex2 = '/"updated":([0-9]*)/';
	$regex3 = '/"ex":".* ([0-9]*):([0-9]*):([0-9]*)"/';
	
	$len = 0;
	$data = array();
	
	$file = fopen( $filename, "r" );
	while (!feof($file)) {
		$line = explode(",", fgets($file));
		$text = getdata($line[2], $line[0]);
		
		$names[$len] = $line[1];
		$interval[$len] = $line[3];
		$dist[$len] = $line[4];
		
		preg_match($regex2, $text, $matches);
		$matches[1] = substr($matches[1], 0, -3);
		$updated[$len] = ((int)(intval($matches[1])) + 28800) % 86400;
		
		preg_match($regex1, $text, $matches);
		$record = explode("}", $matches[1]);
		$lengths[$len] = sizeof($record);
		$row = array();
		for ($i=0; $i<$lengths[$len]; $i++) {
			preg_match($regex3, $record[$i], $matches);
			$row[$i] = intval($matches[1])*3600+intval($matches[2])*60+intval($matches[3])-600;
		}
		array_push($data, $row);
		
		
		$len++;
	}
	fclose($file);
	
	for ($i=1; $i<$len; $i++) {
		if ($data[$i][0] < $data[$i-1][0]) {
			$remain = 0;
			if ($data[$i][0] > $updated[$i]) {
				$remain = $data[$i][0] - $updated[$i];
			}
			$offset = $dist[$i]-(($dist[$i]-$dist[$i-1])*$remain/$interval[$i]);
			printf("<img src=\"circle.png\" style=\"position: fixed; top: 45; left: %d;\"/>",$offset);
		}
	}
?>