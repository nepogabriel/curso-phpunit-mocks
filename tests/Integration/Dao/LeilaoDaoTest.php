<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Infra\ConnectionCreator;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = ConnectionCreator::getConnection();
        $this->pdo->beginTransaction();
    }

    public function testInsercaoEBuscaDevemFuncionar()
    {
        // arrange
        $leilao = new Leilao('Variante 0km');
        $leilaoDao = new LeilaoDao($this->pdo);
        $leilaoDao->salva($leilao);

        // act
        $leiloes = $leilaoDao->recuperarNaoFinalizados();

        // assert
        $this->assertCount(1, $leiloes);
        $this->assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        $this->assertSame('Variante 0km', $leiloes[0]->recuperarDescricao());
    }

    protected function tearDown(): void
    {
        $this->pdo->rollback();
    }
}