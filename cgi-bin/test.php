#!/usr/local/bin/php
<?php
	echo "exec";
	exec('sh test.sh', $output, $return_var);
	echo "<br/>return_var:";
	print_r($return_var);
	echo "<br/>output:";
	print_r($output[0]);
?>