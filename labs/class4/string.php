<?php

$firstName = "Maria";
$lastName  = "Silva";

$fullName = $firstName . " " . $lastName;
echo $fullName; // Maria Silva

echo "Age: " . 30;                // Age: 30
echo "Price: $ " . 19.99;         // Price: $ 19.99
echo "Active: " . var_export(true); // Active: true

$html = "<ul>\n";
$html .= "    <li>Item 1</li>\n";
$html .= "    <li>Item 2</li>\n";
$html .= "    <li>Item 3</li>\n";
$html .= "</ul>";

echo $html;
/*
<ul>
    <li>Item 1</li>
    <li>Item 2</li>
    <li>Item 3</li>
</ul>
*/

$table = "users";
$columns = ['name', 'email', 'age'];
$sql = "SELECT " . implode(', ', $columns) . " FROM {$table}";
$sql .= " WHERE active = 1";
$sql .= " ORDER BY name ASC";
$sql .= " LIMIT 10";

echo $sql;
// SELECT name, email, age FROM users WHERE active = 1 ORDER BY name ASC LIMIT 10
