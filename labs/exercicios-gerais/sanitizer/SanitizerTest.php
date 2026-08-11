<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/sanitizer.php';

class SanitizerTest extends TestCase
{
    public function testHtmlEscapesTags(): void
    {
        $result = safe('<script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testHtmlEscapesAmpersand(): void
    {
        $result = safe('A & B');

        $this->assertSame('A &amp; B', $result);
    }

    public function testHtmlDoesNotEscapeSingleQuotes(): void
    {
        $result = safe("it's fine");

        $this->assertStringContainsString("'", $result);
        $this->assertStringNotContainsString('&#039;', $result);
    }

    public function testHtmlDoesNotEscapeDoubleQuotes(): void
    {
        $result = safe('say "hello"');

        $this->assertStringContainsString('"', $result);
        $this->assertStringNotContainsString('&quot;', $result);
    }

    public function testHtmlPreservesSafeContent(): void
    {
        $result = safe('Hello World');

        $this->assertSame('Hello World', $result);
    }

    public function testAttrEscapesSingleQuotes(): void
    {
        $result = safe("it's fine", 'attr');

        $this->assertStringNotContainsString("'", $result);
        $this->assertStringContainsString('&apos;', $result);
    }

    public function testAttrEscapesDoubleQuotes(): void
    {
        $result = safe('say "hello"', 'attr');

        $this->assertStringNotContainsString('"', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    public function testAttrEscapesTags(): void
    {
        $result = safe('<img src=x onerror=alert(1) alt="img">', 'attr');

        $this->assertStringNotContainsString('<', $result);
        $this->assertStringContainsString('&lt;', $result);
    }

    public function testAttrPreservesSafeContent(): void
    {
        $result = safe('safe-value', 'attr');

        $this->assertSame('safe-value', $result);
    }

    public function testJsEscapesTags(): void
    {
        $result = safe('</script><script>alert(1)</script>', 'js');

        $this->assertStringNotContainsString('<', $result);
        $this->assertStringContainsString('\\u003C', $result);
    }

    public function testJsEscapesQuotesInString(): void
    {
        $result = safe("he said \"run\"", 'js');

        $this->assertStringContainsString('\u0022', $result);
    }

    public function testJsHandlesInteger(): void
    {
        $result = safe(42, 'js');

        $this->assertSame('42', $result);
    }

    public function testJsHandlesBoolean(): void
    {
        $result = safe(true, 'js');

        $this->assertSame('true', $result);
    }

    public function testJsHandlesNull(): void
    {
        $result = safe(null, 'js');

        $this->assertSame('null', $result);
    }

    public function testJsHandlesArray(): void
    {
        $result = safe(['a' => 1, 'b' => 2], 'js');

        $this->assertSame('{"a":1,"b":2}', $result);
    }

    public function testJsHandlesFloat(): void
    {
        $result = safe(3.14, 'js');

        $this->assertSame('3.14', $result);
    }

    public function testUrlEncodesSpaces(): void
    {
        $result = safe('hello world', 'url');

        $this->assertStringContainsString('+', $result);
        $this->assertStringNotContainsString(' ', $result);
    }

    public function testUrlEncodesSpecialCharacters(): void
    {
        $result = safe('name=João&age=30', 'url');

        $this->assertStringContainsString('%3D', $result);
        $this->assertStringContainsString('%26', $result);
    }

    public function testUrlPreservesSafeContent(): void
    {
        $result = safe('hello', 'url');

        $this->assertSame('hello', $result);
    }

    public function testDefaultContextIsHtml(): void
    {
        $withContext = safe('<b>bold</b>');
        $withoutContext = safe('<b>bold</b>');

        $this->assertSame($withContext, $withoutContext);
    }

    public function testInvalidContextFallsBackToHtml(): void
    {
        $invalid = safe('<b>bold</b>', 'invalid_context');
        $html = safe('<b>bold</b>');

        $this->assertSame($html, $invalid);
    }

    public function testIntegerInHtmlBecomesString(): void
    {
        $result = safe(100);

        $this->assertSame('100', $result);
    }

    public function testFloatInHtmlBecomesString(): void
    {
        $result = safe(99.9);

        $this->assertSame('99.9', $result);
    }

    public function testEmptyStringAllModes(): void
    {
        $this->assertSame('', safe(''));
        $this->assertSame('', safe('', 'attr'));
        $this->assertSame('""', safe('', 'js'));
        $this->assertSame('', safe('', 'url'));
    }

    public function testAllModesOnSpecialChars(): void
    {
        $input = '<>"\'&';

        $html = safe($input);
        $this->assertStringNotContainsString('<', $html);
        $this->assertStringNotContainsString('>', $html);
        $this->assertStringContainsString('&amp;', $html);

        $attr = safe($input, 'attr');
        $this->assertStringNotContainsString("'", $attr);
        $this->assertStringNotContainsString('"', $attr);

        $js = safe($input, 'js');
        $this->assertStringContainsString('\\u003C', $js);
        $this->assertStringContainsString('\\u003E', $js);

        $url = safe($input, 'url');
        $this->assertStringNotContainsString('<', $url);
        $this->assertStringNotContainsString('>', $url);
    }

    public function testCommonXssVector(): void
    {
        $vector = '<img src=x onerror="alert(\'XSS\')" alt="img">';

        $html = safe($vector);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;img', $html);

        $attr = safe($vector, 'attr');
        $this->assertStringNotContainsString('<img', $attr);
        $this->assertStringNotContainsString('"', $attr);

        $js = safe($vector, 'js');
        $this->assertStringNotContainsString('<img', $js);
    }

    public function testNullInHtmlAndAttr(): void
    {
        $html = safe(null);
        $this->assertSame('', $html);

        $attr = safe(null, 'attr');
        $this->assertSame('', $attr);
    }

    public function testBooleanInHtml(): void
    {
        $this->assertSame('1', safe(true));
        $this->assertSame('', safe(false));
    }

    public function testHtmlReturnsString(): void
    {
        $this->assertIsString(safe('text'));
        $this->assertIsString(safe(123));
        $this->assertIsString(safe(null));
    }
}
