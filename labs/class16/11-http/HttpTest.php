<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $_SERVER = [];
        $_FILES = [];
    }

    public function testGetRequest(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_GET["search"] = "PHPUnit";

        $search = $_GET["search"] ?? "";
        $response = json_encode(["query" => $search, "results" => []]);

        $data = json_decode($response, true);
        $this->assertArrayHasKey("query", $data);
        $this->assertEquals("PHPUnit", $data["query"]);
    }

    public function testPostSubmission(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST["email"] = "dre@email.com";
        $_POST["name"] = "Dre";

        $email = $_POST["email"] ?? "";
        $name  = $_POST["name"] ?? "";
        $output = sprintf("Email saved: %s (%s)", $email, $name);

        $this->assertStringContainsString("Email saved", $output);
        $this->assertStringContainsString("dre@email.com", $output);
    }
}
