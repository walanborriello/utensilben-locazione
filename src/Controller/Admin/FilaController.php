<?php

namespace App\Controller\Admin;

use App\Entity\Fila;
use App\Entity\Sezione;
use App\Form\FilaType;
use App\Repository\FilaRepository;
use App\Repository\PianoRepository;
use App\Service\FilaSezioneSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/file', name: 'admin_fila_')]
class FilaController extends AbstractController
{
    public function __construct(private readonly FilaSezioneSynchronizer $filaSezioneSynchronizer)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, FilaRepository $filaRepository, PianoRepository $pianoRepository): Response
    {
        $q = $request->query->get('q');
        $pianoId = $request->query->get('piano') ? (int) $request->query->get('piano') : null;

        return $this->render('admin/fila/index.html.twig', [
            'file' => $filaRepository->filtra($q, $pianoId),
            'piani' => $pianoRepository->filtra(null),
            'q' => $q,
            'pianoId' => $pianoId,
        ]);
    }

    #[Route('/nuova', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fila = new Fila();
        // Precompila le sezioni "standard" (a,b,c,e,f,g) come punto di
        // partenza comodo: l'utente può poi modificare la stringa prima di
        // salvare, il testo ha pieno controllo manuale da qui in poi.
        $this->filaSezioneSynchronizer->sincronizza($fila);

        $form = $this->createForm(FilaType::class, $fila);
        $form->get('sezioniTesto')->setData($this->sezioniInTesto($fila));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errore = $this->applicaSezioni($fila, $form->get('sezioniTesto')->getData());

            if (null !== $errore) {
                $this->addFlash('error', $errore);

                return $this->render('admin/fila/form.html.twig', [
                    'fila' => $fila,
                    'form' => $form,
                ]);
            }

            $this->filaSezioneSynchronizer->completaRipiani($fila);
            $entityManager->persist($fila);
            $entityManager->flush();

            $this->addFlash('success', 'Fila creata.');

            return $this->redirectToRoute('admin_fila_index');
        }

        return $this->render('admin/fila/form.html.twig', [
            'fila' => $fila,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifica', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fila $fila, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FilaType::class, $fila);
        $form->get('sezioniTesto')->setData($this->sezioniInTesto($fila));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errore = $this->applicaSezioni($fila, $form->get('sezioniTesto')->getData());

            if (null !== $errore) {
                $this->addFlash('error', $errore);

                return $this->redirectToRoute('admin_fila_edit', ['id' => $fila->getId()]);
            }

            $this->filaSezioneSynchronizer->completaRipiani($fila);
            $entityManager->flush();

            $this->addFlash('success', 'Fila aggiornata.');

            return $this->redirectToRoute('admin_fila_index');
        }

        return $this->render('admin/fila/form.html.twig', [
            'fila' => $fila,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/elimina', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Fila $fila, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-fila-'.$fila->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fila);
            $entityManager->flush();
            $this->addFlash('success', 'Fila eliminata.');
        }

        return $this->redirectToRoute('admin_fila_index');
    }

    private function sezioniInTesto(Fila $fila): string
    {
        $lettere = array_map(
            static fn (Sezione $sezione): string => $sezione->getLettera(),
            $fila->getSezioni()->toArray()
        );

        return implode(', ', $lettere);
    }

    /**
     * Applica alla fila l'elenco di sezioni scritto a mano nel campo di
     * testo (es. "a, b, c, e, f, g"): aggiunge le lettere mancanti, rimuove
     * quelle non più presenti. Ritorna null se tutto ok, altrimenti un
     * messaggio d'errore (lettera non valida, oppure sezione con materiali
     * già posizionati che non si può togliere).
     */
    private function applicaSezioni(Fila $fila, ?string $testo): ?string
    {
        $lettereRichieste = [];
        foreach (explode(',', $testo ?? '') as $pezzo) {
            $lettera = strtolower(trim($pezzo));
            if ('' === $lettera) {
                continue;
            }
            if (!preg_match('/^[a-z]$/', $lettera)) {
                return sprintf('"%s" non è una lettera valida per una sezione.', $pezzo);
            }
            $lettereRichieste[$lettera] = true;
        }
        $lettereRichieste = array_keys($lettereRichieste);

        $sezioniEsistenti = [];
        foreach ($fila->getSezioni() as $sezione) {
            $sezioniEsistenti[$sezione->getLettera()] = $sezione;
        }

        foreach ($sezioniEsistenti as $lettera => $sezione) {
            if (in_array($lettera, $lettereRichieste, true)) {
                continue;
            }

            if ($this->sezioneHaMaterialiPosizionati($sezione)) {
                return sprintf(
                    'Non puoi togliere la sezione "%s": contiene materiali posizionati. Sposta prima i materiali.',
                    $lettera
                );
            }

            $fila->removeSezione($sezione);
        }

        foreach ($lettereRichieste as $lettera) {
            if (isset($sezioniEsistenti[$lettera])) {
                continue;
            }

            $sezione = new Sezione();
            $sezione->setLettera($lettera);
            $fila->addSezione($sezione);
        }

        return null;
    }

    private function sezioneHaMaterialiPosizionati(Sezione $sezione): bool
    {
        foreach ($sezione->getRipiani() as $ripiano) {
            if (!$ripiano->getPosizionamenti()->isEmpty()) {
                return true;
            }
        }

        return false;
    }
}
