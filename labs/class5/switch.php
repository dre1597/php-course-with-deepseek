<?php

$weekDay = 3;

switch ($weekDay) {
    case 1:
        echo "Sunday";
        break;
    case 2:
        echo "Monday";
        break;
    case 3:
        echo "Tuesday";
        break;
    case 4:
        echo "Wednesday";
        break;
    case 5:
        echo "Thursday";
        break;
    case 6:
        echo "Friday";
        break;
    case 7:
        echo "Saturday";
        break;
    default:
        echo "Invalid day";
        break;
}
// Output: Tuesday


$month = 2;
$year = 2024;

switch ($month) {
    case 1:
    case 3:
    case 5:
    case 7:
    case 8:
    case 10:
    case 12:
        echo "31 days";
        break;
    case 4:
    case 6:
    case 9:
    case 11:
        echo "30 days";
        break;
    case 2:
        // Leap year?
        $days = ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0) ? 29 : 28;
        echo "{$days} days";
        break;
    default:
        echo "Invalid month";
}
// Output: 29 days (2024 is a leap year)


$value = 0;

switch ($value) {
    case false:
        echo "Matched false"; // This runs! Because 0 == false
        break;
    case 0:
        echo "Matched 0";
        break;
}

// Watch out: switch uses ==, not ===
// "0" matches 0, false matches 0, null matches 0...


// Technique: switch(true) for complex conditions
$grade = 8.5;
$attendance = 85; // percentage
switch (true) {
    case $grade >= 7 && $attendance >= 75:
        echo "Approved";
        break;
    case $grade >= 5 && $attendance >= 75:
        echo "Recovery exam";
        break;
    case $attendance < 75:
        echo "Failed due to absences";
        break;
    default:
        echo "Failed by grade";
        break;
}
// Output: Approved
