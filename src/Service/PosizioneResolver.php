<?php

namespace App\Service;

use App\Entity\Area;
use App\Entity\Materiale;
use App\Entity\Posizionamento;
use App\Enum\LatoArea;

/**
 * Trasforma le posizioni fisiche di un Materiale (fila / area / piano) in
 * una struttura semplice, pronta per essere serializzata in JSON e usata
 * dalla modale di ricerca per disegnare lo schema grafico dello scaffale.
 */
class PosizioneResolver
{
    /**
     * Ordine di visualizzazione dei lati, da sinistra a destra così come li
     * vede chi è in piedi davanti allo scaffale (non l'ordine alfabetico).
     */
    private const ORDINE_LATI = [
        LatoArea::UNICA->value => 0,
        LatoArea::SINISTRA->value => 1,
        LatoArea::DESTRA->value => 2,
    ];

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
        $piano = $posizionamento->getPiano();
        $area = $piano?->getArea();
        $fila = $area?->getFila();

        return [
            'id' => $posizionamento->getId(),
            'principale' => $posizionamento->isPrincipale(),
            'sezione' => $posizionamento->getSezione(),
            'note' => $posizionamento->getNote(),
            'fila' => [
                'codice' => $fila?->getCodice(),
                'nome' => $fila?->getNome(),
                'tipo' => $fila?->getTipo()->value,
            ],
            'areaCorrente' => [
                'lato' => $area?->getLato()->value,
                'latoLabel' => $area?->getLato()->label(),
            ],
            'pianoCorrente' => [
                'numero' => $piano?->getNumero(),
                'etichetta' => $piano?->getEtichetta(),
            ],
            // Struttura completa delle aree/piani della fila, con il piano
            // trovato marcato come "evidenziato", cosi' il frontend puo'
            // disegnare l'intero scaffale (anche se diviso in due lati) e
            // accendere solo la cella giusta.
            'aree' => $fila ? $this->serializzaAree($fila->getAree()->toArray(), $piano?->getId(), $posizionamento->getSezione()) : [],
        ];
    }

    /**
     * @param Area[] $aree
     *
     * @return array<int, array<string, mixed>>
     */
    private function serializzaAree(array $aree, ?int $pianoEvidenziatoId, ?string $sezioneEvidenziata): array
    {
        usort(
            $aree,
            static fn (Area $a, Area $b): int => (self::ORDINE_LATI[$a->getLato()->value] ?? 99) <=> (self::ORDINE_LATI[$b->getLato()->value] ?? 99)
        );

        return array_map(
            fn (Area $area): array => [
                'lato' => $area->getLato()->value,
                'latoLabel' => $area->getLato()->labelBreve(),
                'piani' => array_map(
                    static function ($piano) use ($pianoEvidenziatoId, $sezioneEvidenziata): array {
                        $evidenziato = $piano->getId() === $pianoEvidenziatoId;

                        return [
                            'numero' => $piano->getNumero(),
                            'etichetta' => $piano->getEtichetta(),
                            'evidenziato' => $evidenziato,
                            'sezione' => $evidenziato ? $sezioneEvidenziata : null,
                        ];
                    },
                    $area->getPiani()->toArray()
                ),
            ],
            $aree
        );
    }
}
