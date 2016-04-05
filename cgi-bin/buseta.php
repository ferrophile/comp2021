#!/usr/local/bin/php
<script language="javascript" type="text/javascript">
	var result = "";
	
	function createXHR() {
		return new XMLHttpRequest();
	}
	
	function getEta(url) {
		xmlhttp = createXHR();
		xmlhttp.onreadystatechange = function() {
			if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				result += xmlhttp.responseText;
				result += "<br/>";
				document.getElementById('test').innerHTML = result;
			} else {
				document.getElementById('test').innerHTML = "waiting for response";				
			}
		}
		xmlhttp.open("GET", url, true);
		xmlhttp.send();
	}
	
</script>
<input type="button" value="Submit" onclick="getEta('http://etav2.kmb.hk/?action=geteta&lang=en&route=91M&bound=1&stop=HK02T10000&stop_seq=12')"/>
<input type="button" value="Submit" onclick="getEta('http://etav2.kmb.hk/?action=geteta&lang=en&route=91M&bound=1&stop=CL01W16000&stop_seq=13')"/>
<div id="test">Hello!</div>