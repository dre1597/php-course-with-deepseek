<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/slugify.php';

class SlugifyTest extends TestCase
{
    public function testLowerCase()
    {
        $this->assertEquals('hello', slugify('Hello'));
    }

    public function testAccents()
    {
        $this->assertEquals('hello', slugify('Helló'));
    }

    public function testSpaces()
    {
        $this->assertEquals('hello-world', slugify('Hello World'));
    }

    public function testSpecialCharacters()
    {
        $this->assertEquals('hello-world', slugify('Hello World!'));
    }

    public function testNumbers()
    {
        $this->assertEquals('hello-world-123', slugify('Hello World 123'));
    }

    public function testExerciseExample()
    {
        $this->assertEquals('curso-de-php-8-avancado', slugify('Curso de PHP 8 — Avançado!'));
    }

    public function testEmptyString()
    {
        $this->assertEquals('', slugify(''));
    }

    public function testOnlySpecialCharacters()
    {
        $this->assertEquals('', slugify('!@#$%'));
    }

    public function testMultipleConsecutiveSpaces()
    {
        $this->assertEquals('a-b-c', slugify('a  b   c'));
    }

    public function testLeadingAndTrailingSpaces()
    {
        $this->assertEquals('hello', slugify('  hello  '));
    }

    public function testPortugueseAccents()
    {
        $this->assertEquals('nao-avaliacao-otimo', slugify('não avaliação ótimo'));
    }

    public function testFrenchAccents()
    {
        $this->assertEquals('cafe-creme-etes-vous', slugify('café crème êtes vous'));
    }

    public function testGermanUmlauts()
    {
        $this->assertEquals('uber-muller-schon', slugify('Über Müller schön'));
    }

    public function testTildeAndCedilla()
    {
        $this->assertEquals('pinhao-nalgaca', slugify('piñhão nalgaça'));
    }

    public function testOnlyNumbers()
    {
        $this->assertEquals('123-456', slugify('123 456'));
    }

    public function testAlreadySlugified()
    {
        $this->assertEquals('already-a-slug', slugify('already-a-slug'));
    }

    public function testPunctuationBetweenWords()
    {
        $this->assertEquals('helloworld', slugify('hello,world'));
    }

    public function testDotsAndCommas()
    {
        $this->assertEquals('hello-world', slugify('hello... world,,,'));
    }

    public function testUnderscoresAndPipes()
    {
        $this->assertEquals('helloworldfoo', slugify('hello_world|foo'));
    }

    public function testMixedEverything()
    {
        $this->assertEquals('a-b-c-123', slugify('À! b? ç... 123'));
    }

    public function testSingleCharacter()
    {
        $this->assertEquals('x', slugify('X'));
    }

    public function testSingleAccentedCharacter()
    {
        $this->assertEquals('a', slugify('á'));
    }

    public function testLongString()
    {
        str_repeat('Á B Ç ', 50)
            |> slugify(...)
            |> (fn($x) => $this->assertEquals(str_repeat('a-b-c-', 49) . 'a-b-c', $x));
    }

    public function testHyphenOnlyString()
    {
        $this->assertEquals('-', slugify('-'));
    }

    public function testConsecutiveHyphensPreserved()
    {
        $this->assertEquals('hello---world', slugify('hello - world'));
    }
}
