<?php

var_dump('123' == 123);    // bool(true)  — coercion: string '123' becomes int 123
var_dump('123' === 123);   // bool(false) — different types

var_dump(0 == '');         // bool(true)  — WARNING! '' is converted to 0
var_dump(0 === '');        // bool(false)
var_dump(0 == 'zero');     // bool(true)  — 'zero' is non-numeric, becomes 0
var_dump(0 === 'zero');    // bool(false)

var_dump(strcmp('abc', 'abc'));  // int(0)  — equal
var_dump(strcmp('abc', 'abd'));  // int(-1) — 'abc' < 'abd'
var_dump(strcmp('abd', 'abc'));  // int(1)  — 'abd' > 'abc'

// Case-sensitive
var_dump(strcmp('ABC', 'abc'));  // int(-1)

var_dump(strcasecmp('ABC', 'abc')); // int(0) — equal, ignoring case

$files = ['img10.jpg', 'img2.jpg', 'img1.jpg', 'img20.jpg'];

usort($files, 'strnatcmp');
print_r($files); // ['img1.jpg', 'img2.jpg', 'img10.jpg', 'img20.jpg']
