<?php

const THEME_COOKIE = 'theme';
const THEME_LIGHT = 'light';
const THEME_DARK = 'dark';
const THEME_TTL = 60 * 60 * 24 * 30;

function getCurrentTheme(): string
{
    $theme = $_COOKIE[THEME_COOKIE] ?? THEME_LIGHT;

    if ($theme !== THEME_LIGHT && $theme !== THEME_DARK) {
        return THEME_LIGHT;
    }

    return $theme;
}

function toggleTheme(): string
{
    return getCurrentTheme() === THEME_LIGHT ? THEME_DARK : THEME_LIGHT;
}

function applyTheme(string $theme): void
{
    setcookie(THEME_COOKIE, $theme, [
        'expires'  => time() + THEME_TTL,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function isLightTheme(): bool
{
    return getCurrentTheme() === THEME_LIGHT;
}

function isDarkTheme(): bool
{
    return getCurrentTheme() === THEME_DARK;
}
