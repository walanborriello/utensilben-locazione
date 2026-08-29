<?php

namespace App\Tests\Service;

use App\Entity\Fila;
use App\Enum\LatoArea;
use App\Enum\TipoFila;
use App\Service\FilaAreaSynchronizer;
use PHPUnit\Framework\TestCase;

class FilaAreaSynchronizerTest extends TestCase
{
    public function testFilaUnicaOttieneUnaSolaArea(): void
    {
        $fila = new Fila();
        $fila->setCodice('F1');
        $fila->setNome('Fila di prova');
        $fila->setTipo(TipoFila::UNICA);

        (new FilaAreaSynchronizer())->sincronizza($fila);

        self::assertCount(1, $fila->getAree());
        self::assertSame(LatoArea::UNICA, $fila->getAree()->first()->getLato());
    }

    public function testFilaDivisaOttieneDueAree(): void
    {
        $fila = new Fila();
        $fila->setCodice('F2');
        $fila->setNome('Fila divisa di prova');
        $fila->setTipo(TipoFila::DIVISA);

        (new FilaAreaSynchronizer())->sincronizza($fila);

        $lati = array_map(static fn ($area) => $area->getLato(), $fila->getAree()->toArray());

        self::assertCount(2, $fila->getAree());
        self::assertContains(LatoArea::SINISTRA, $lati);
        self::assertContains(LatoArea::DESTRA, $lati);
    }

    public function testSincronizzareNonDuplicaAreeGiaPresenti(): void
    {
        $fila = new Fila();
        $fila->setCodice('F3');
        $fila->setNome('Fila già sincronizzata');
        $fila->setTipo(TipoFila::DIVISA);

        $synchronizer = new FilaAreaSynchronizer();
        $synchronizer->sincronizza($fila);
        $synchronizer->sincronizza($fila);

        self::assertCount(2, $fila->getAree());
    }

    public function testCambiareTipoAggiungeLeAreeMancantiSenzaRimuovereQuelleEsistenti(): void
    {
        $fila = new Fila();
        $fila->setCodice('F4');
        $fila->setNome('Fila che cambia tipo');
        $fila->setTipo(TipoFila::UNICA);

        $synchronizer = new FilaAreaSynchronizer();
        $synchronizer->sincronizza($fila);
        self::assertCount(1, $fila->getAree());

        // Cambio idea: la fila diventa divisa. L'area "unica" gia' esistente
        // (che potrebbe avere gia' dei materiali collegati) non va persa.
        $fila->setTipo(TipoFila::DIVISA);
        $synchronizer->sincronizza($fila);

        $lati = array_map(static fn ($area) => $area->getLato(), $fila->getAree()->toArray());

        self::assertCount(3, $fila->getAree());
        self::assertContains(LatoArea::UNICA, $lati);
        self::assertContains(LatoArea::SINISTRA, $lati);
        self::assertContains(LatoArea::DESTRA, $lati);
    }
}
