<?php

require 'vendor/autoload.php';


$remoteIp = file_get_contents("http://domain.tld/ip.php");
echo "Fetching remote ip: $remoteIp";

$da = new \DirectAdmin\DirectAdmin();
$da->connect('domainname.tld', 2222);
$da->set_login('username', 'password');
$da->set_method('get');

$names = ['homecloud','home'];

foreach($names as $name) {

// Remove ip
$da->query('/CMD_API_DNS_CONTROL?domain=domain.tld&action=select&arecs0=' . urlencode('name=' . $name));
$dresponse = $da->fetch_body();
echo "\n\nRemoving record; response was: " . print_r($dresponse,true);

$da->query('/CMD_API_DNS_CONTROL?domain=domain.tld&action=add&type=A&name='.$name.'&value=' . $remoteIp);
$dresponse = $da->fetch_body();
echo "\n\nAdding record; response was: " . print_r($dresponse,true);

}
