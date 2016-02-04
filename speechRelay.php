<?php
include_once 'config.inc';
/*
   Relay of Ajax Request and Response
   Version 1.0
   February 23, 2015

   Will Bontrager Software LLC
   http://www.willmaster.com/
   Copyright 2015 Will Bontrager Software LLC

   This software is provided "AS IS," without 
   any warranty of any kind, without even any 
   implied warranty such as merchantability 
   or fitness for a particular purpose.
   Will Bontrager Software LLC grants 
   you a royalty free license to use or 
   modify this software provided this 
   notice appears on all copies. 
   
   ------------
   
   Modifications and embedded API keys are property and business secret of
   (c) 2015 Klaus HammermŸller klaus@hammermueller.at
   and may not be distributed. 
   *confidential*
   Distribution including the keys which may lead to additional charges.
   Misuse of the keys or additional cost implied will be recalled from
   the distributor and may inflict further legal action.
*/

if (strpos($_SERVER['REQUEST_URI'], 'key=' . API-KEY) < 0) 
	die;
$apiKey = SPEACH-API-KEY;

// One customization, the URL of the remote page or software.

$RemotePageURL = "http://api.ispeech.org/api/rest";

// No other customizations required.
mb_internal_encoding('UTF-8');
ini_set('display_errors', 1);
$options = array(
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_HEADER         => false,
   CURLOPT_CONNECTTIMEOUT => 120,
   CURLOPT_TIMEOUT        => 120,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_MAXREDIRS      => 10,
   CURLOPT_AUTOREFERER    => true,
   CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
   CURLOPT_VERBOSE        => false
);
if( isset($_SERVER['HTTP_REFERER']) ) { $options[CURLOPT_REFERER] = $_SERVER['HTTP_REFERER']; }
if( count($_POST) )
{
   $arr = array();
   foreach( $_POST as $k => $v ) { 
   	if ($k == "apikey")
   		$arr[] = urlencode($k) . '=' . $apiKey;
   	else
   		$arr[] = urlencode($k) . '=' . urlencode($v); }
   $options[CURLOPT_POST] = 1;
   $options[CURLOPT_POSTFIELDS] = implode('&',$arr);
}
if( count($_GET) )
{
   $arr = array();
   foreach( $_GET as $k => $v ) { 
   	if ($k == "apikey")
   		$arr[] = urlencode($k) . '=' . $apiKey;
   	else
   		$arr[] = urlencode($k) . '=' . urlencode($v); }
   $RemotePageURL .= '?' . implode('&',$arr);
}
$ch = curl_init($RemotePageURL);
curl_setopt_array($ch,$options);
echo curl_exec($ch);
curl_close($ch);
?>

