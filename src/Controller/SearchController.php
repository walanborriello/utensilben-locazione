<?php

namespace App\Controller;

use App\Repository\MaterialeRepository;
use App\Service\PosizioneResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('search/index.html.twig');
    }

    /**
     * Autocompletamento: cerca per nome o codice articolo.
     */
    #[Route('/api/cerca', name: 'app_cerca', methods: ['GET'])]
    public function cerca(Request $request, MaterialeRepository $materialeRepository): JsonResponse
    {
        $termine = (string) $request->query->get('q', '');
        $risultati = $materialeRepository->cerca($termine);

        return $this->json(array_map(
            static fn ($materiale): array => [
                'id' => $materiale->getId(),
                'codice' => $materiale->getCodice(),
                'nome' => $materiale->getNome(),
                'categoria' => $materiale->getCategoria()?->getNome(),
                'numeroPosizioni' => $materiale->getPosizionamenti()->count(),
            ],
            $risultati
        ));
    }

    /**
     * Dettaglio delle posizioni di un materiale, usato dalla modale grafica.
     */
    #[Route('/api/materiali/{id}/posizione', name: 'app_materiale_posizione', methods: ['GET'])]
    public function posizione(int $id, MaterialeRepository $materialeRepository, PosizioneResolver $posizioneResolver): JsonResponse
    {
        $materiale = $materialeRepository->find($id);

        if (null === $materiale) {
            return $this->json(['errore' => 'Materiale non trovato.'], Response::HTTP_NOT_FOUND);
        }

        $posizioni = $posizioneResolver->risolvi($materiale);

        return $this->json([
            'materiale' => [
                'id' => $materiale->getId(),
                'codice' => $materiale->getCodice(),
                'nome' => $materiale->getNome(),
                'descrizione' => $materiale->getDescrizione(),
            ],
            'posizioni' => $posizioni,
        ]);
    }
}
