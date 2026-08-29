<?php

namespace App\Service;

use App\Entity\Area;
use App\Entity\Fila;
use App\Enum\LatoArea;
use App\Enum\TipoFila;

/**
 * Garantisce che una Fila abbia le Aree corrispondenti al tipo scelto:
 * una sola area "unica" oppure due aree "sinistra" + "destra".
 *
 * L'amministratore non crea mai le Aree a mano: sceglie solo il tipo
 * della Fila e questo servizio genera (o completa) le Aree necessarie.
 * Per non perdere posizionamenti già assegnati, le Aree esistenti non
 * vengono mai rimosse automaticamente, nemmeno se il tipo della Fila
 * viene cambiato in seguito.
 */
class FilaAreaSynchronizer
{
    public function sincronizza(Fila $fila): void
    {
        $latiRichiesti = TipoFila::DIVISA === $fila->getTipo()
            ? [LatoArea::SINISTRA, LatoArea::DESTRA]
            : [LatoArea::UNICA];

        $latiEsistenti = array_map(
            static fn (Area $area): LatoArea => $area->getLato(),
            $fila->getAree()->toArray()
        );

        foreach ($latiRichiesti as $lato) {
            if (!in_array($lato, $latiEsistenti, true)) {
                $area = new Area();
                $area->setLato($lato);
                $fila->addArea($area);
            }
        }
    }
}
