<?php


$i = 0;

loop_start:
$i++;
echo "{$i} ";

if ($i < 5) {
    goto loop_start;
}
// 1 2 3 4 5


// goto can replace break N in very complex cases
foreach ($data as $group) {
    foreach ($group as $item) {
        foreach ($item as $subItem) {
            if (exitCondition($subItem)) {
                goto clean_exit;
            }
            processItem($subItem);
        }
    }
}

clean_exit:
echo "Processing complete or interrupted.\n";


// This does NOT work:
goto inside;
for ($i = 0; $i < 5; $i++) {
    inside:
    echo "{$i}\n";
}
// Fatal error: 'goto' into loop
