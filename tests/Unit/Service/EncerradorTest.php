<?php

namespace Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    private $encerrador;
    private $enviadorEmail;
    private $leilaoCarro1;
    private $leilaoCarro2;

    protected function setUp(): void
    {
        $this->leilaoCarro1 = new Leilao('i30 2012 125000km', new \DateTimeImmutable('8 days ago'));
        $this->leilaoCarro2 = new Leilao('Polo 2007 25000km', new \DateTimeImmutable('10 days ago'));

        // $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
        //     ->setConstructorArgs([new \PDO('sqlite::memory:')])
        //     ->getMock();

        $leilaoDao = $this->createMock(LeilaoDao::class);

        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$this->leilaoCarro1, $this->leilaoCarro2]);
        $leilaoDao->method('recuperarFinalizados')->willReturn([$this->leilaoCarro1, $this->leilaoCarro2]);

        $leilaoDao
            ->expects($this->exactly(2))
            ->method('atualiza');

        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);
        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmail);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        
        $this->encerrador->encerra();

        $leiloes = [$this->leilaoCarro1, $this->leilaoCarro2];
        $this->assertCount(2, $leiloes);
        $this->assertTrue($leiloes[0]->estaFinalizado());
        $this->assertTrue($leiloes[1]->estaFinalizado());
    }

    public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmal()
    {
        $exception = new \DomainException('Erro ao enviar e-mail');

        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException($exception);

        $this->encerrador->encerra();
    }

    public function testSoDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willReturnCallback(function (Leilao $leilao) {
                $this->assertTrue($leilao->estaFinalizado());
            });

        $this->encerrador->encerra();
    }
}