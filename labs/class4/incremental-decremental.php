<?php

$count = 5;

echo ++$count;   // 6 — pre-increment
echo $count;     // 6

echo $count++;   // 6 — post-increment
echo $count;     // 7

echo --$count;   // 6
echo $count--;   // 6
echo $count;     // 5

$i = 0;
while ($i++ < 5) {
    echo "post-increment: {$i}\n";
}
// 1, 2, 3, 4, 5

$i = 0;
while (++$i < 5) {
    echo "pre-increment: {$i}\n";
}
// 1, 2, 3, 4 (++$i becomes 5, 5 < 5 is false)
