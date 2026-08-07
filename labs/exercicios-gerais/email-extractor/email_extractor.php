<?php

function extractEmails($text)
{
    $pattern = '/[a-zA-Z0-9._%\'\+\-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
    $result = preg_match_all($pattern, $text, $matches);
    if ($result === false) {
        return [];
    }
    return $matches[0];
}
