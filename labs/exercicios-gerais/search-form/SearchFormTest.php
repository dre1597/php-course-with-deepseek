<?php

use PHPUnit\Framework\TestCase;

class SearchFormTest extends TestCase
{
    private function renderPage(?string $term = null): string
    {
        if ($term !== null) {
            $_GET['q'] = $term;
        } else {
            unset($_GET['q']);
        }

        ob_start();
        // require (not require_once): each test must re-execute the script from scratch
        require __DIR__ . '/search-form.php';
        return ob_get_clean();
    }

    protected function tearDown(): void
    {
        unset($_GET['q']);
    }

    public function testWithoutTermShowsAllItems(): void
    {
        $html = $this->renderPage();

        $this->assertStringContainsString('<li>PHP</li>', $html);
        $this->assertStringContainsString('<li>JavaScript</li>', $html);
        $this->assertStringContainsString('<li>Ruby</li>', $html);
    }

    public function testWithoutTermDoesNotShowEmptyMessage(): void
    {
        $html = $this->renderPage();

        $this->assertStringNotContainsStringIgnoringCase('nenhum resultado', $html);
    }

    public function testExactMatchReturnsOnlyThatItem(): void
    {
        $html = $this->renderPage('PHP');

        $this->assertStringContainsString('<li>PHP</li>', $html);
        $this->assertStringNotContainsString('<li>Python</li>', $html);
        $this->assertStringNotContainsString('<li>JavaScript</li>', $html);
    }

    public function testSearchIsCaseInsensitive(): void
    {
        $html = $this->renderPage('php');

        $this->assertStringContainsString('<li>PHP</li>', $html);
    }

    public function testSearchMatchesPartialSubstring(): void
    {
        $html = $this->renderPage('Script');

        $this->assertStringContainsString('<li>JavaScript</li>', $html);
        $this->assertStringContainsString('<li>TypeScript</li>', $html);
        $this->assertStringNotContainsString('<li>PHP</li>', $html);
    }

    public function testSearchMatchesWithinWord(): void
    {
        $html = $this->renderPage('av');

        $this->assertStringContainsString('<li>Java</li>', $html);
        $this->assertStringContainsString('<li>JavaScript</li>', $html);
    }

    public function testSearchReturnsMultipleMatches(): void
    {
        $html = $this->renderPage('a');

        $this->assertStringContainsString('<li>Java</li>', $html);
        $this->assertStringContainsString('<li>JavaScript</li>', $html);
        $this->assertStringContainsString('<li>Scala</li>', $html);
        $this->assertStringContainsString('<li>Dart</li>', $html);
    }

    public function testNoMatchShowsEmptyMessage(): void
    {
        $html = $this->renderPage('ZZZNotFound');

        $this->assertStringContainsStringIgnoringCase('nenhum resultado', $html);
        $this->assertStringNotContainsString('<li>', $html);
    }

    public function testTermWithWhitespaceIsTrimmed(): void
    {
        $html = $this->renderPage('  Ruby  ');

        $this->assertStringContainsString('<li>Ruby</li>', $html);
        $this->assertStringNotContainsString('  Ruby  ', $html);
    }

    public function testTermIsPreservedInInput(): void
    {
        $html = $this->renderPage('Python');

        $this->assertMatchesRegularExpression('/value="Python"/', $html);
    }

    public function testTrimmedTermIsPreservedInInput(): void
    {
        $html = $this->renderPage('  Go  ');

        $this->assertMatchesRegularExpression('/value="Go"/', $html);
        $this->assertStringNotContainsString('value="  Go  "', $html);
    }

    public function testInputValueIsEscapedAgainstXss(): void
    {
        $html = $this->renderPage('"><script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testOutputEscapesSpecialCharacters(): void
    {
        $html = $this->renderPage('C#');

        $this->assertStringContainsString('<li>C#</li>', $html);
    }

    public function testFormHasGetMethod(): void
    {
        $html = $this->renderPage();

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('method="get"', $html);
    }

    public function testFormHasSearchInput(): void
    {
        $html = $this->renderPage();

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="q"/', $html);
    }

    public function testFormHasSubmitButton(): void
    {
        $html = $this->renderPage();

        $this->assertMatchesRegularExpression('/<button[^>]*type="submit"/', $html);
    }

    public function testSingleResultIsRendered(): void
    {
        $html = $this->renderPage('Kotlin');

        $this->assertStringContainsString('<li>Kotlin</li>', $html);
    }

    public function testEmptyQueryStringShowsAllItems(): void
    {
        $html = $this->renderPage('');

        $this->assertStringContainsString('<li>PHP</li>', $html);
        $this->assertStringContainsString('<li>Ruby</li>', $html);
        $this->assertStringNotContainsStringIgnoringCase('nenhum resultado', $html);
    }

    public function testLabelForMatchesInputId(): void
    {
        $html = $this->renderPage();

        $this->assertMatchesRegularExpression('/<label[^>]*for="q"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*id="q"/', $html);
    }
}
