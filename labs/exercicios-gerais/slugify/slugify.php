<?php

function slugify($string)
{
    return $string
            |> trim(...)
            |> (fn($x) => iconv('UTF-8', 'ASCII//TRANSLIT', $x))
            |> strtolower(...)
            |> (fn($x) => preg_replace('/\s+/', '-', $x))
            |> (fn($x) => preg_replace('/[^A-Za-z0-9\-]/', '', $x));
}
