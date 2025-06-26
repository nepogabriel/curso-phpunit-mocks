<?php

namespace Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;


class LeilaoDaoMock extends LeilaoDao
{
    private $leiloes = [];

    public function salva(Leilao $leilao): void
    {
        $this->leiloes[] = $leilao;
    }

    public function recuperarNaoFinalizados(): array
    {
        return array_filter($this->leiloes, function (Leilao $leilao) {
            return !$leilao->estaFinalizado();
        });
    }

    public function recuperarFinalizados(): array
    {
        return array_filter($this->leiloes, function (Leilao $leilao) {
            return $leilao->estaFinalizado();
        });
    }

    public function atualiza(Leilao $leilao): void
    {}
}

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $i30 = new Leilao('i30 2012 125000km', new \DateTimeImmutable('8 days ago'));
        $polo = new Leilao('Polo 2007 25000km', new \DateTimeImmutable('10 days ago'));

        $leilaoDao = new LeilaoDaoMock();
        $leilaoDao->salva($i30);
        $leilaoDao->salva($polo);

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();
        $this->assertCount(2, $leiloes);
        $this->assertEquals('i30 2012 125000km', $leiloes[0]->recuperarDescricao());
        $this->assertEquals('Polo 2007 25000km', $leiloes[1]->recuperarDescricao());
    }
}