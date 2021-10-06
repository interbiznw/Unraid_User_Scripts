#!/usr/bin/php
# Unraid User Script for sending UPS monitoring data to InfluxDb>2.0+ for display in grafana
# Created/Modified by Jamie Owens/Interbiznw.com
# previous reference for Old influxdb https://technicalramblings.com/blog/setting-grafana-influxdb-telegraf-ups-monitoring-unraid
#
# Initial steps:
# 1) create bucket in influxdb2.0+ and enter id below.
# 2) take note of ORG id # and enter below.
# 3) create token with read/write access to previously created bucket only, place that token in the place below marked 'YOUR_API_TOKEN'
# 4) place in UNRAID User scripts, and set custom schedule of '* * * * *' to run every minute
#
#

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
