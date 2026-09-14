<?php

namespace App\Controller\Admin;

use App\Entity\Fila;
use App\Repository\FilaRepository;
use App\Repository\PianoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/mappa', name: 'admin_mappa_')]
class MappaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, PianoRepository $pianoRepository, FilaRepository $filaRepository): Response
    {
        $piani = $pianoRepository->filtra(null);
        $pianoIdParam = $request->query->get('piano') ? (int) $request->query->get('piano') : null;

        $pianoSelezionato = null;
        foreach ($piani as $piano) {
            if ($piano->getId() === $pianoIdParam) {
                $pianoSelezionato = $piano;
                break;
            }
        }
        $pianoSelezionato ??= $piani[0] ?? null;

        $file = null !== $pianoSelezionato ? $filaRepository->mappaPerPiano($pianoSelezionato->getId()) : [];

        return $this->render('admin/mappa/index.html.twig', [
            'piani' => $piani,
            'pianoSelezionato' => $pianoSelezionato,
            'corsie' => $this->raggruppaInCorsie($file),
        ]);
    }

    /**
     * Raggruppa le file in "corsie": la prima e l'ultima fila (muro dietro,
     * un solo lato accessibile) restano da sole, tutte le altre si accoppiano
     * due a due (i due lati dello stesso corridoio, fronte/retro). Le file
     * arrivano già ordinate per lettera DESC (L in cima, A in fondo).
     *
     * @param Fila[] $file
     *
     * @return list<array{file: Fila[], celle: list<array{tipo: string, lettera?: string}>}>
     */
    private function raggruppaInCorsie(array $file): array
    {
        $gruppi = [];
        $buffer = [];

        foreach ($file as $fila) {
            if ($fila->isPrimaFila() || $fila->isUltimaFila()) {
                if ([] !== $buffer) {
                    $gruppi[] = $buffer;
                    $buffer = [];
                }
                $gruppi[] = [$fila];
                continue;
            }

            $buffer[] = $fila;
            if (2 === count($buffer)) {
                $gruppi[] = $buffer;
                $buffer = [];
            }
        }

        if ([] !== $buffer) {
            $gruppi[] = $buffer;
        }

        return array_map(
            fn (array $filaGruppo): array => ['file' => $filaGruppo, 'celle' => $this->celleCorsia($filaGruppo)],
            $gruppi
        );
    }

    /**
     * Le "celle" da disegnare per una corsia: una per ogni sezione che le
     * file di quella corsia hanno DAVVERO (unione delle lettere, non uno
     * schema fisso a-g) — se c'è solo a,b,c si disegnano solo quelle. Un
     * corridoio (vuoto) si disegna solo dove salta esattamente la "d" tra
     * una "c" e una "e" già presenti: è l'unico caso in cui la sezione "d"
     * ha un vero significato di corridoio da segnalare.
     *
     * @param Fila[] $filaGruppo
     *
     * @return list<array{tipo: string, lettera?: string}>
     */
    private function celleCorsia(array $filaGruppo): array
    {
        $lettere = [];
        foreach ($filaGruppo as $fila) {
            foreach ($fila->getSezioni() as $sezione) {
                $lettere[$sezione->getLettera()] = true;
            }
        }
        $lettere = array_keys($lettere);
        sort($lettere);

        $celle = [];
        $precedente = null;
        foreach ($lettere as $lettera) {
            if ('c' === $precedente && 'e' === $lettera) {
                $celle[] = ['tipo' => 'corridoio'];
            }
            $celle[] = ['tipo' => 'sezione', 'lettera' => $lettera];
            $precedente = $lettera;
        }

        return $celle;
    }
}
