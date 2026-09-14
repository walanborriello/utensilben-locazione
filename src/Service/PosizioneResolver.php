<?php

namespace App\Service;

use App\Entity\Materiale;
use App\Entity\Posizionamento;

/**
 * Trasforma le posizioni fisiche di un Materiale (piano / fila / sezione /
 * ripiano) in una struttura semplice, pronta per essere serializzata in JSON
 * e usata dalla modale di ricerca per il breadcrumb del percorso.
 */
class PosizioneResolver
{
    /**
     * @return array<int, array<string, mixed>> una voce per ogni Posizionamento del materiale, principale per prima
     */
    public function risolvi(Materiale $materiale): array
    {
        $posizionamenti = $materiale->getPosizionamenti()->toArray();

        usort(
            $posizionamenti,
            static fn (Posizionamento $a, Posizionamento $b): int => (int) $b->isPrincipale() <=> (int) $a->isPrincipale()
        );

        return array_map(
            fn (Posizionamento $posizionamento): array => $this->serializzaPosizionamento($posizionamento),
            $posizionamenti
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializzaPosizionamento(Posizionamento $posizionamento): array
    {
        $ripiano = $posizionamento->getRipiano();
        $sezione = $ripiano?->getSezione();
        $fila = $sezione?->getFila();
        $piano = $fila?->getPiano();

        return [
            'id' => $posizionamento->getId(),
            'principale' => $posizionamento->isPrincipale(),
            'note' => $posizionamento->getNote(),
            'piano' => [
                'nome' => $piano?->getNome(),
            ],
            'fila' => [
                'lettera' => $fila?->getLettera(),
            ],
            'sezione' => [
                'lettera' => $sezione?->getLettera(),
            ],
            'ripiano' => [
                'numero' => $ripiano?->getNumero(),
                'etichetta' => $ripiano?->getEtichetta(),
            ],
        ];
    }
}
