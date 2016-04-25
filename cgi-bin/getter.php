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
	
	$next=0;
	for ($i=1; $i<$len; $i++) {
		if ($data[$i][0] < $data[$i-1][0]) {
			if ($i <= 12) {
				$next = $i;
			}
			$remain = 0;
			if ($data[$i][0] > $updated[$i]) {
				$remain = $data[$i][0] - $updated[$i];
			}
			$offset = $dist[$i]-(($dist[$i]-$dist[$i-1])*$remain/$interval[$i]);
			printf("<img src=\"circle.png\" style=\"position: fixed; top: 45; left: %d;\"/>",$offset);
		}
	}
	
	if ($next == 0) {
		printf("<div style=\"position: fixed; top: 210; left: 5;\"><h4>The next bus is waiting to depart from %s</h4></div>", $names[$next]);
	} else {
		if ($remain == 0) {
			printf("<div style=\"position: fixed; top: 210; left: 5;\"><h4>The next bus is near %s</h4></div>", $names[$next]);
		} else {
			printf("<div style=\"position: fixed; top: 210; left: 5;\"><h4>The next bus is travelling towards %s</h4></div>", $names[$next]);		
		}
	}
	
	$secs = $data[12][0] - $updated[$next];
	if ($secs > 30) {
		printf("<div style=\"position: fixed; top: 240; left: 5;\"><h4>Arrive at HKUST in about: %d min</h4></div>", (int)($secs / 60));
	} else {
		printf("<div style=\"position: fixed; top: 240; left: 5;\"><h4>It will arrive soon.</h4></div>");
	}
	
	$walkdata = array();
	$walk = fopen( 'walk.csv', "r" );
	while (!feof($walk)) {
		$area = explode(",", fgets($walk));
		array_push($walkdata, $area);
	}
	fclose($walk);
	
	printf("<table style=\"border: 1px solid black; position: fixed; top: 300; left: 5;\">");
	printf("<tr>");
	foreach ($walkdata as $row) {
		printf("<th>%s</th>", $row[1]);
	}
	printf("</tr>");
	printf("<tr>");
	foreach ($walkdata as $row) {
		$offsetsec = $secs - intval($row[2]);
		if ($offsetsec > 0) {
			$color = "#4CAF50";
		} else if ($offsetsec > -60) {
			$color = "#FFCC00";
		} else {
			$color = "#CC0000";
		}
		printf("<td style=\"background-color: %s;\"> %d</td>", $color, $offsetsec);
	}
	printf("</tr>");
	printf("</table>");
?>