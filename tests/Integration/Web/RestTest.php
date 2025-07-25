<?php

use PHPUnit\Framework\TestCase;

class RestTest extends TestCase
{
    public function testApiRestDeveRetornarArrayDeLeiloes()
    {
        $resposta = file_get_contents('http://localhost:80/rest.php');

        $this->assertStringContainsString('200 OK', $http_response_header[0]);
        $this->assertIsArray(json_decode($resposta));
    }
}