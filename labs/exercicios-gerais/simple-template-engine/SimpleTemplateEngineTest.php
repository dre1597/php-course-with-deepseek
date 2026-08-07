<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/simple_template_engine.php';

class SimpleTemplateEngineTest extends TestCase
{
    public function testExerciseExample()
    {
        $template = 'Hello {{name}}, your order #{{order}} is {{status}}.';
        $data = ['name' => 'John', 'order' => 1234, 'status' => 'delivered'];
        $this->assertEquals(
            'Hello John, your order #1234 is delivered.',
            renderTemplate($template, $data)
        );
    }

    public function testSinglePlaceholder()
    {
        $this->assertEquals(
            'Hello John',
            renderTemplate('Hello {{name}}', ['name' => 'John'])
        );
    }

    public function testNoPlaceholders()
    {
        $this->assertEquals(
            'Hello World',
            renderTemplate('Hello World', ['name' => 'John'])
        );
    }

    public function testEmptyTemplate()
    {
        $this->assertEquals('', renderTemplate('', ['name' => 'John']));
    }

    public function testEmptyData()
    {
        $this->assertEquals(
            'Hello {{name}}',
            renderTemplate('Hello {{name}}', [])
        );
    }

    public function testMultipleOccurrencesOfSamePlaceholder()
    {
        $this->assertEquals(
            'John and John again',
            renderTemplate('{{name}} and {{name}} again', ['name' => 'John'])
        );
    }

    public function testMissingKeyKeepsPlaceholder()
    {
        $this->assertEquals(
            'Hello {{name}}',
            renderTemplate('Hello {{name}}', ['other' => 'value'])
        );
    }

    public function testUnderscoreInKey()
    {
        $this->assertEquals(
            'Value: 42',
            renderTemplate('Value: {{my_key}}', ['my_key' => 42])
        );
    }

    public function testHyphenInKey()
    {
        $this->assertEquals(
            'Val: x',
            renderTemplate('Val: {{my-key}}', ['my-key' => 'x'])
        );
    }

    public function testNumericValues()
    {
        $this->assertEquals(
            'price: 19.99, qty: 3, total: 59.97',
            renderTemplate(
                'price: {{price}}, qty: {{qty}}, total: {{total}}',
                ['price' => 19.99, 'qty' => 3, 'total' => 59.97]
            )
        );
    }

    public function testBooleanValues()
    {
        $this->assertEquals(
            'active: 1, deleted: ',
            renderTemplate(
                'active: {{active}}, deleted: {{deleted}}',
                ['active' => true, 'deleted' => false]
            )
        );
    }

    public function testAdjacentPlaceholders()
    {
        $this->assertEquals(
            'AB',
            renderTemplate('{{a}}{{b}}', ['a' => 'A', 'b' => 'B'])
        );
    }

    public function testLargeTemplate()
    {
        $template = str_repeat('{{x}} ', 100);
        $this->assertEquals(
            str_repeat('OK ', 100),
            renderTemplate($template, ['x' => 'OK'])
        );
    }

    public function testValueContainsPlaceholderSyntax()
    {
        $this->assertEquals(
            'Showing: {{unused}}',
            renderTemplate(
                'Showing: {{display}}',
                ['display' => '{{unused}}']
            )
        );
    }

    public function testChainedReplacement()
    {
        $this->assertEquals(
            'Hello Smith',
            renderTemplate(
                'Hello {{name}}',
                ['name' => '{{surname}}', 'surname' => 'Smith']
            )
        );
    }

    public function testHtmlTemplate()
    {
        $this->assertEquals(
            '<h1>Welcome</h1><p>User: John, Age: 30</p>',
            renderTemplate(
                '<h1>{{title}}</h1><p>User: {{user}}, Age: {{age}}</p>',
                ['title' => 'Welcome', 'user' => 'John', 'age' => 30]
            )
        );
    }

    public function testNumericKey()
    {
        $this->assertEquals(
            'val: 42',
            renderTemplate('val: {{0}}', [42])
        );
    }

    public function testCaseSensitivePlaceholder()
    {
        $this->assertEquals(
            'Hello {{NAME}}',
            renderTemplate('Hello {{NAME}}', ['name' => 'John'])
        );
    }

    public function testNullValue()
    {
        $this->assertEquals(
            'Value: ',
            renderTemplate('Value: {{v}}', ['v' => null])
        );
    }
}
