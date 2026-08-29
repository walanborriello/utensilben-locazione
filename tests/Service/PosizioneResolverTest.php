<?php

namespace App\Tests\Service;

use App\Entity\Area;
use App\Entity\Fila;
use App\Entity\Materiale;
use App\Entity\Piano;
use App\Entity\Posizionamento;
use App\Enum\LatoArea;
use App\Enum\TipoFila;
use App\Service\PosizioneResolver;
use PHPUnit\Framework\TestCase;

class PosizioneResolverTest extends TestCase
{
    public function testFilaUnicaRestituisceUnaSolaPosizioneConAreaEvidenziata(): void
    {
        $fila = $this->creaFila('F1', TipoFila::UNICA);
        $area = $fila->getAree()->first();
        $piano1 = $this->aggiungiPiano($area, 1, 10);
        $this->aggiungiPiano($area, 2, 11);

        $materiale = $this->creaMateriale('MAT-1');
        $this->assegnaPosizione($materiale, $piano1, true, 100);

        $risultato = (new PosizioneResolver())->risolvi($materiale);

        self::assertCount(1, $risultato);
        self::assertSame('F1', $risultato[0]['fila']['codice']);
        self::assertSame('unica', $risultato[0]['areaCorrente']['lato']);
        self::assertSame(1, $risultato[0]['pianoCorrente']['numero']);

        $piani = $risultato[0]['aree'][0]['piani'];
        self::assertTrue($piani[0]['evidenziato']);
        self::assertFalse($piani[1]['evidenziato']);
    }

    public function testFilaDivisaEvidenziaSoloIlLatoCorretto(): void
    {
        $fila = $this->creaFila('F2', TipoFila::DIVISA);
        [$sinistra, $destra] = $fila->getAree()->toArray();
        $pianoDestro = $this->aggiungiPiano($destra, 2, 20);
        $this->aggiungiPiano($sinistra, 1, 21);

        $materiale = $this->creaMateriale('MAT-2');
        $this->assegnaPosizione($materiale, $pianoDestro, true, 200);

        $risultato = (new PosizioneResolver())->risolvi($materiale);

        self::assertSame('destra', $risultato[0]['areaCorrente']['lato']);

        foreach ($risultato[0]['aree'] as $area) {
            foreach ($area['piani'] as $piano) {
                $atteso = ('destra' === $area['lato'] && 2 === $piano['numero']);
                self::assertSame($atteso, $piano['evidenziato']);
            }
        }
    }

    public function testMaterialeConPiuPosizioniRestituisceLaPrincipalePerPrima(): void
    {
        $fila = $this->creaFila('F3', TipoFila::UNICA);
        $area = $fila->getAree()->first();
        $pianoSecondario = $this->aggiungiPiano($area, 1, 30);
        $pianoPrincipale = $this->aggiungiPiano($area, 2, 31);

        $materiale = $this->creaMateriale('MAT-3');
        // Aggiunta volutamente in ordine "sbagliato" per verificare che il
        // resolver riordini mettendo la posizione principale per prima.
        $this->assegnaPosizione($materiale, $pianoSecondario, false, 300);
        $this->assegnaPosizione($materiale, $pianoPrincipale, true, 301);

        $risultato = (new PosizioneResolver())->risolvi($materiale);

        self::assertCount(2, $risultato);
        self::assertTrue($risultato[0]['principale']);
        self::assertSame(2, $risultato[0]['pianoCorrente']['numero']);
        self::assertFalse($risultato[1]['principale']);
    }

    private function creaFila(string $codice, TipoFila $tipo): Fila
    {
        $fila = new Fila();
        $fila->setCodice($codice);
        $fila->setNome('Fila '.$codice);
        $fila->setTipo($tipo);

        $lati = TipoFila::DIVISA === $tipo ? [LatoArea::SINISTRA, LatoArea::DESTRA] : [LatoArea::UNICA];
        foreach ($lati as $lato) {
            $area = new Area();
            $area->setLato($lato);
            $fila->addArea($area);
        }

        return $fila;
    }

    private function aggiungiPiano(Area $area, int $numero, int $idFittizio): Piano
    {
        $piano = new Piano();
        $piano->setNumero($numero);
        $area->addPiano($piano);
        $this->assegnaId($piano, $idFittizio);

        return $piano;
    }

    private function creaMateriale(string $codice): Materiale
    {
        $materiale = new Materiale();
        $materiale->setCodice($codice);
        $materiale->setNome('Materiale '.$codice);

        return $materiale;
    }

    private function assegnaPosizione(Materiale $materiale, Piano $piano, bool $principale, int $idFittizio): Posizionamento
    {
        $posizionamento = new Posizionamento();
        $posizionamento->setPiano($piano);
        $posizionamento->setPrincipale($principale);
        $materiale->addPosizionamento($posizionamento);
        $this->assegnaId($posizionamento, $idFittizio);

        return $posizionamento;
    }

    /**
     * Le entita' generano l'id solo dopo il persist su un vero database:
     * nei test unitari (senza Doctrine) lo impostiamo via reflection, visto
     * che il resolver lo usa per capire quale piano evidenziare.
     */
    private function assegnaId(object $entita, int $id): void
    {
        $property = new \ReflectionProperty($entita, 'id');
        $property->setAccessible(true);
        $property->setValue($entita, $id);
    }
}
