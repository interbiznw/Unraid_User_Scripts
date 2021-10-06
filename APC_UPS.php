#!/usr/bin/php
# Unraid User Script for sending UPS monitoring data to InfluxDb>2.0+ for display in grafana
# Created/Modified by Jamie Owens/Interbiznw.com
# Initial script credit for Old influxdb usage < 2.0 https://technicalramblings.com/blog/setting-grafana-influxdb-telegraf-ups-monitoring-unraid
#
# Initial steps:
# 1) set IP address and port like "192.168.2.100:8086" in the "_influxURL" variable below
# 2) create bucket in influxdb2.0+ and enter id below in "_bucketID"
# 3) take note of ORG id # and enter below in "_orgIDnumber"
# 4) create token with read/write access to previously created bucket only, enter that below in '_influxToken'
# 5) set $_influxHost to the hostname or identifer you want data to be filtered under in influxdb
# 6) set the UPS Unit name to describe the specific ups(in case you are logging multiple) in "_upsUnitName"
# 7) place in UNRAID User scripts, and set custom schedule of '* * * * *' to run every minute
#
<?php

$command = "apcaccess";
$args = "status";

// Configure your specific info here
$_influxURL = "192.168.2.236:8086";
$_orgIDnumber = "95bfa0355b08a7ab";
$_bucketID = "6d8f31aa4d82244f";
$_influxToken = "FbInquF_cMFfTg1oGL3aRUYLzLIgKsWNhxCAsWO_o_baOqaOr3P0PP3kODAs4piK3PVtLf17mD-dhMD2fNOH4w==";
$_influxHost = "NAS";
$_upsUnitName = "UPS1";
// END configuration ----------- do not touch anything below here unless you know what you are doing.

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

sendDB($match[1], $tag, $_influxURL, $_orgIDnumber, $_bucketID, $_influxToken, $_influxHost, $_upsUnitName);

}
//end system call


//send to influxdb2

function sendDB($val, $tagname, $influxURL, $orgIDnumber, $bucketID, $influxToken, $influxHost, $upsUnitName) {

$curl = "curl -i -XPOST 'http://" .$influxURL. "/api/v2/write?org=" .$orgIDnumber. "&bucket=" .$bucketID. "&precision=ns' --header 'Authorization: Token "
.$influxToken. "' --header 'Content-Type: text/plain; charset=utf-8' --header 'Accept: application/json' --data-binary '".$upsUnitName.",host=".$influxHost." "
.$tagname."=".$val."'";

echo $curl;
$execsr = exec($curl);

}

?>
