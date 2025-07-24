<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    private static \PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('CREATE TABLE leiloes (
            id INTEGER primary key,
            descricao TEXT,
            finalizado BOOLEAN,
            dataInicio TEXT
        );');
    }

    protected function setUp(): void
    {
        self::$pdo->beginTransaction();
    }

    #[DataProvider('leiloes')]
    public function testBuscaLeiloesNaoFinalizados(array $leiloes): void
    {
        // arrange
        $leilaoDao = new LeilaoDao(self::$pdo);

        foreach ($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }

        // act
        $leiloes = $leilaoDao->recuperarNaoFinalizados();

        // assert
        $this->assertCount(1, $leiloes);
        $this->assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        $this->assertSame('Variante 0km', $leiloes[0]->recuperarDescricao());
        $this->assertFalse($leiloes[0]->estaFinalizado());
    }

    #[DataProvider('leiloes')]
    public function testBuscaLeiloesFinalizados(array $leiloes): void
    {
        // arrange
        $leilaoDao = new LeilaoDao(self::$pdo);

        foreach ($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }

        // act
        $leiloes = $leilaoDao->recuperarFinalizados();

        // assert
        $this->assertCount(1, $leiloes);
        $this->assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        $this->assertSame('Fiat 147 0km', $leiloes[0]->recuperarDescricao());
        $this->assertTrue($leiloes[0]->estaFinalizado());
    }

    public function testAoAtualizarLeilaoStatusDeveSerAlterado()
    {
        $leilao = new Leilao('Brasília Amarela');
        $leilaoDao = new LeilaoDao(self::$pdo);
        $leilao = $leilaoDao->salva($leilao);
        
        $leiloes = $leilaoDao->recuperarNaoFinalizados();
        $this->assertCount(1, $leiloes);
        $this->assertSame('Brasília Amarela', $leiloes[0]->recuperarDescricao());
        $this->assertFalse($leiloes[0]->estaFinalizado());

        $leilao->finaliza();
        $leilaoDao->atualiza($leilao);

        $leiloes = $leilaoDao->recuperarFinalizados();
        $this->assertCount(1, $leiloes);
        $this->assertSame('Brasília Amarela', $leiloes[0]->recuperarDescricao());
        $this->assertTrue($leiloes[0]->estaFinalizado());
    }

    protected function tearDown(): void
    {
        self::$pdo->rollback();
    }

    public static function leiloes(): array
    {
        $leilaoAtivo = new Leilao('Variante 0km');
        $leilaoFinalizado = new leilao('Fiat 147 0km');
        $leilaoFinalizado->finaliza();

        $leiloes = [$leilaoAtivo, $leilaoFinalizado];

        return [
            [$leiloes]
        ];
    }
}