<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/theme.php';

class ThemePreferenceTest extends TestCase
{
    protected function setUp(): void
    {
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        $_COOKIE = [];
    }

    public function testGetCurrentThemeDefaultsToLight(): void
    {
        $this->assertSame(THEME_LIGHT, getCurrentTheme());
    }

    public function testGetCurrentThemeReadsCookie(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_DARK;

        $this->assertSame(THEME_DARK, getCurrentTheme());
    }

    public function testGetCurrentThemeRejectsUnknownValue(): void
    {
        $_COOKIE[THEME_COOKIE] = 'hacked';

        $this->assertSame(THEME_LIGHT, getCurrentTheme());
    }

    public function testToggleThemeSwitchesLightToDark(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_LIGHT;

        $this->assertSame(THEME_DARK, toggleTheme());
    }

    public function testToggleThemeSwitchesDarkToLight(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_DARK;

        $this->assertSame(THEME_LIGHT, toggleTheme());
    }

    public function testIsLightThemeReturnsTrueWhenLight(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_LIGHT;

        $this->assertTrue(isLightTheme());
        $this->assertFalse(isDarkTheme());
    }

    public function testIsDarkThemeReturnsTrueWhenDark(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_DARK;

        $this->assertTrue(isDarkTheme());
        $this->assertFalse(isLightTheme());
    }

    public function testPageRendersWithLightThemeByDefault(): void
    {
        $html = $this->renderPage();

        $this->assertStringContainsString('<body class="light-theme"', $html);
        $this->assertStringContainsString('Tema atual: Claro', $html);
    }

    public function testPageRendersWithDarkThemeWhenCookieSet(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_DARK;

        $html = $this->renderPage();

        $this->assertStringContainsString('<body class="dark-theme"', $html);
        $this->assertStringContainsString('Tema atual: Escuro', $html);
    }

    public function testPageHasToggleForm(): void
    {
        $html = $this->renderPage();

        $this->assertStringContainsString('<form method="post"', $html);
        $this->assertStringContainsString('<button type="submit"', $html);
    }

    public function testToggleButtonShowsCorrectLabel(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_LIGHT;
        $html = $this->renderPage();

        $this->assertStringContainsString('Alternar para tema Escuro', $html);
    }

    public function testToggleButtonShowsCorrectLabelWhenDark(): void
    {
        $_COOKIE[THEME_COOKIE] = THEME_DARK;
        $html = $this->renderPage();

        $this->assertStringContainsString('Alternar para tema Claro', $html);
    }

    public function testToggleReturnsLightWhenCookieIsInvalid(): void
    {
        $_COOKIE[THEME_COOKIE] = 'invalid';

        $this->assertSame(THEME_DARK, toggleTheme());
    }

    /**
     * @runInSeparateProcess
     */
    public function testApplyThemeSetsCookieWithThirtyDayExpiry(): void
    {
        applyTheme(THEME_DARK);

        $headers = xdebug_get_headers();
        $cookie  = $this->findCookieHeader($headers);

        $this->assertNotNull($cookie, 'Set-Cookie header not found');

        preg_match('/expires=([^;]+)/', $cookie, $matches);
        $expires = strtotime($matches[1]);
        $expectedMin = time() + THEME_TTL - 5;
        $expectedMax = time() + THEME_TTL + 5;

        $this->assertGreaterThanOrEqual($expectedMin, $expires);
        $this->assertLessThanOrEqual($expectedMax, $expires);
    }

    /**
     * @runInSeparateProcess
     */
    public function testApplyThemeSetsCookiePathToRoot(): void
    {
        applyTheme(THEME_LIGHT);

        $cookie = $this->findCookieHeader(xdebug_get_headers());

        $this->assertNotNull($cookie);
        $this->assertStringContainsString('path=/', $cookie);
    }

    /**
     * @runInSeparateProcess
     */
    public function testApplyThemeSetsCookieSecureFlags(): void
    {
        applyTheme(THEME_DARK);

        $cookie = $this->findCookieHeader(xdebug_get_headers());

        $this->assertNotNull($cookie);
        $this->assertStringContainsString('HttpOnly', $cookie);
        $this->assertStringContainsString('SameSite=Lax', $cookie);
    }

    private function findCookieHeader(array $headers): ?string
    {
        foreach ($headers as $header) {
            if (str_starts_with($header, 'Set-Cookie: theme=')) {
                return $header;
            }
        }

        return null;
    }

    private function renderPage(): string
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        // require (not require_once): each test must re-execute the script from scratch
        require __DIR__ . '/index.php';
        return ob_get_clean();
    }
}
