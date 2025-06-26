<?php

namespace Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $i30 = new Leilao('i30 2012 125000km', new \DateTimeImmutable('8 days ago'));
        $polo = new Leilao('Polo 2007 25000km', new \DateTimeImmutable('10 days ago'));

        // $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
        //     ->setConstructorArgs([new \PDO('sqlite::memory:')])
        //     ->getMock();

        $leilaoDao = $this->createMock(LeilaoDao::class);

        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$i30, $polo]);
        $leilaoDao->method('recuperarFinalizados')->willReturn([$i30, $polo]);

        $leilaoDao
            ->expects($this->exactly(2))
            ->method('atualiza');

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = [$i30, $polo];
        $this->assertCount(2, $leiloes);
        $this->assertTrue($leiloes[0]->estaFinalizado());
        $this->assertTrue($leiloes[1]->estaFinalizado());
    }
}