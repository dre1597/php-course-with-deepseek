<?php

$a = 0b1100;  // 12
$b = 0b1010;  // 10

printf("%b\n", $a & $b);   // 1000 → 8
printf("%b\n", $a | $b);   // 1110 → 14
printf("%b\n", $a ^ $b);   // 0110 → 6
printf("%b\n", ~$a);       // ...11110011 → -13 (watch sign)
printf("%b\n", $a << 1);   // 11000 → 24 (multiply by 2)
printf("%b\n", $a << 2);   // 110000 → 48 (multiply by 4)
printf("%b\n", $a >> 1);   // 110 → 6 (divide by 2)
printf("%b\n", $a >> 2);   // 11 → 3 (divide by 4)


const CAN_READ = 1;    // 0b0001
const CAN_WRITE = 2;    // 0b0010
const CAN_DELETE = 4;    // 0b0100
const CAN_ADMIN = 8;    // 0b1000

$userPermissions = CAN_READ | CAN_WRITE;                             // 3 (0b0011)
$adminPermissions = CAN_READ | CAN_WRITE | CAN_DELETE | CAN_ADMIN;    // 15 (0b1111)

if ($userPermissions & CAN_WRITE) {
    echo "User can write\n";
}

if (!($userPermissions & CAN_DELETE)) {
    echo "User cannot delete\n";
}

$userPermissions |= CAN_DELETE;   // add
$userPermissions &= ~CAN_DELETE;  // remove
$userPermissions ^= CAN_WRITE;    // toggle (flip on/off)
