#!/usr/bin/php
<?php

$command = "apcaccess";
$args = "status";
$tagsArray = array(
"LOADPCT",
"ITEMP",
"TIMELEFT",
"TONBATT",
"BCHARGE"
);

//do system call

$call = $command." ".$args;
$output = shell_exec($call);

//parse output for tag and value

foreach ($tagsArray as $tag) {

preg_match("/".$tag."\s*:\s([\d|\.]+)/si", $output, $match);

//send measurement, tag and value to influx

sendDB($match[1], $tag);

}
//end system call


//send to influxdb

function sendDB($val, $tagname) {

$curl = "curl -i -XPOST 'http://influxDBIP:8086/api/v2/write?org=YOUR_ORG&bucket=YOUR_BUCKET&precision=ns' --header 'Authorization: Token YOUR_API_TOKEN' --header 'Content-Type: text/plain; charset=utf-8' --header 'Accept: application/json' --data-binary 'APC,host=NAS,region=us-west "
.$tagname."=".$val."'";
$execsr = exec($curl);

}

?>
