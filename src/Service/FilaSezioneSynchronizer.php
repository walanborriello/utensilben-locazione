<?php

namespace App\Service;

use App\Entity\Fila;
use App\Entity\Ripiano;
use App\Entity\Sezione;

/**
 * Genera automaticamente le Sezioni (e i loro Ripiani 1-4) di una Fila
 * appena creata o modificata: nessuna delle due si crea mai a mano.
 *
 * Ogni fila ha le sezioni a,b,c,e,f,g — la "d" è il corridoio e si salta —
 * tranne l'ultima fila del magazzino, dove il corridoio finisce e la "d"
 * torna a essere una sezione vera.
 *
 * Come FilaAreaSynchronizer prima di questo, non rimuove mai sezioni/ripiani
 * già esistenti (anche se il flag "ultima fila" cambia dopo), solo aggiunge
 * quelli mancanti: evita di perdere posizionamenti già assegnati.
 */
class FilaSezioneSynchronizer
{
    private const LETTERE_SEZIONE = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

    public function sincronizza(Fila $fila): void
    {
        $lettereRichieste = $fila->isUltimaFila()
            ? self::LETTERE_SEZIONE
            : array_values(array_diff(self::LETTERE_SEZIONE, ['d']));

        $lettereEsistenti = array_map(
            static fn (Sezione $sezione): string => $sezione->getLettera(),
            $fila->getSezioni()->toArray()
        );

        foreach ($lettereRichieste as $lettera) {
            if (in_array($lettera, $lettereEsistenti, true)) {
                continue;
            }

            $sezione = new Sezione();
            $sezione->setLettera($lettera);
            $fila->addSezione($sezione);
        }

        $this->completaRipiani($fila);
    }

    /**
     * Genera i 4 ripiani (1-4) di ogni sezione che ne è ancora priva: sia
     * quelle appena aggiunte da sincronizza(), sia quelle create a mano nel
     * form della fila (che arrivano con la sola lettera, senza ripiani).
     */
    public function completaRipiani(Fila $fila): void
    {
        foreach ($fila->getSezioni() as $sezione) {
            if (!$sezione->getRipiani()->isEmpty()) {
                continue;
            }

            for ($numero = 1; $numero <= 4; ++$numero) {
                $ripiano = new Ripiano();
                $ripiano->setNumero($numero);
                $sezione->addRipiano($ripiano);
            }
        }
    }
}
