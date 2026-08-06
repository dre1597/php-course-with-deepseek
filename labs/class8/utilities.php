<?php

$comment = "First line.\nSecond line.\nThird line.";

echo nl2br($comment);
// First line.<br />
// Second line.<br />
// Third line.

echo nl2br($comment, false); // <br> instead of <br />

$text = 'The quick brown fox jumps over the lazy dog.';

echo wordwrap($text, 20, "<br />\n");
/*
The quick brown fox
jumps over the lazy
dog.
*/

// With forced cut (fourth parameter true):
echo wordwrap('AVeryyyyyyyyyyyyyyLongURL', 15, "<br />\n", true);
// AVeryyyyyyyyyyy
// yyyyLongURL
