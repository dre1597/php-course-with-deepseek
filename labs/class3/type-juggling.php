<?php
$result = "10" + 5;
echo $result;
var_dump($result);

$text = "Total: " . 100;
echo $text;
var_dump($text);

$sum = "1.5" + "2.5";
var_dump($sum);

$integerValue = (int)"42";
$floatValue = (float)"3.14";
$stringValue = (string)100;
$booleanValue = (bool)1;
$arrayValue = (array)"test";
$objectValue = (object)['a' => 1];

var_dump((bool)"");
var_dump((bool)"0");
var_dump((bool)"00");
var_dump((bool)"false");
var_dump((bool)[]);
var_dump((bool)[0]);
var_dump((bool)0);
var_dump((bool)0.0);
var_dump((bool)-1);
var_dump((bool)null);
