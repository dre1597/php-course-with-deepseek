<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/email_extractor.php';

class EmailExtractorTest extends TestCase
{
    public function testSingleEmail()
    {
        $this->assertEquals(
            ['john@example.com'],
            extractEmails('john@example.com')
        );
    }

    public function testMultipleEmails()
    {
        $this->assertEquals(
            ['alice@example.com', 'bob@test.org'],
            extractEmails('alice@example.com and bob@test.org')
        );
    }

    public function testNoEmails()
    {
        $this->assertEquals([], extractEmails('just some random text'));
    }

    public function testEmptyString()
    {
        $this->assertEquals([], extractEmails(''));
    }

    public function testEmailAtStartOfText()
    {
        $this->assertEquals(
            ['start@mail.com'],
            extractEmails('start@mail.com is the first thing')
        );
    }

    public function testEmailAtEndOfText()
    {
        $this->assertEquals(
            ['end@mail.com'],
            extractEmails('the last thing is end@mail.com')
        );
    }

    public function testEmailWithPunctuationAround()
    {
        $this->assertEquals(
            ['test@mail.com', 'test@mail.com'],
            extractEmails('(test@mail.com) or "test@mail.com"')
        );
    }

    public function testEmailFollowedByComma()
    {
        $this->assertEquals(
            ['comma@test.org'],
            extractEmails('comma@test.org, and more text')
        );
    }

    public function testEmailFollowedByPeriod()
    {
        $this->assertEquals(
            ['dot@ender.net'],
            extractEmails('Contact dot@ender.net.')
        );
    }

    public function testSubdomainEmail()
    {
        $this->assertEquals(
            ['user@mail.example.com'],
            extractEmails('user@mail.example.com')
        );
    }

    public function testPlusTagEmail()
    {
        $this->assertEquals(
            ['user+tag@domain.com'],
            extractEmails('user+tag@domain.com')
        );
    }

    public function testDotsInLocalPart()
    {
        $this->assertEquals(
            ['first.last@domain.com'],
            extractEmails('first.last@domain.com')
        );
    }

    public function testUnderscoreAndHyphen()
    {
        $this->assertEquals(
            ['under_score@do-main.com'],
            extractEmails('under_score@do-main.com')
        );
    }

    public function testNumbersInEmail()
    {
        $this->assertEquals(
            ['user123@99designs.com'],
            extractEmails('user123@99designs.com')
        );
    }

    public function testCombinedPunctuation()
    {
        $this->assertEquals(
            ['one@a.com', 'two@b.org'],
            extractEmails('<one@a.com> [two@b.org]')
        );
    }

    public function testInvalidMissingAt()
    {
        $this->assertEquals([], extractEmails('notanemail.com'));
    }

    public function testInvalidNoDomain()
    {
        $this->assertEquals([], extractEmails('user@'));
    }

    public function testInvalidNoTLD()
    {
        $this->assertEquals([], extractEmails('user@domain'));
    }

    public function testInvalidNoUser()
    {
        $this->assertEquals([], extractEmails('@domain.com'));
    }

    public function testRealWorldExample()
    {
        $text = <<<TEXT
        Contact us at support@company.com or sales@company.co.uk.
        For complaints: complaints@company.org.
        CEO direct line: ceo@company.com.br
        TEXT;

        $this->assertEquals(
            [
                'support@company.com',
                'sales@company.co.uk',
                'complaints@company.org',
                'ceo@company.com.br',
            ],
            extractEmails($text)
        );
    }

    public function testLongTLD()
    {
        $this->assertEquals(
            ['name@example.technology'],
            extractEmails('name@example.technology')
        );
    }

    public function testPercentageAndPlusMixed()
    {
        $this->assertEquals(
            ['weird%name+tag@example.com'],
            extractEmails('weird%name+tag@example.com')
        );
    }

    public function testTrailingDoubleQuotesHuggingEmail()
    {
        $this->assertEquals(
            ['x@y.com'],
            extractEmails('"x@y.com"')
        );
    }

    public function testApostropheInLocalPart()
    {
        $this->assertEquals(
            ['o\'connor@domain.com'],
            extractEmails("o'connor@domain.com")
        );
    }

    public function testDuplicateEmailsPreserved()
    {
        $this->assertEquals(
            ['dup@mail.com', 'dup@mail.com'],
            extractEmails('dup@mail.com and dup@mail.com again')
        );
    }

    public function testTextWithoutAnythingResemblingEmail()
    {
        $this->assertEquals(
            [],
            extractEmails('12345 !@#$ hello world')
        );
    }
}
