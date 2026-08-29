<?php

namespace App\Controller\Admin;

use App\Repository\MaterialeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Elenco dei materiali (tipicamente appena importati) a cui non e' ancora
 * stata assegnata una posizione fisica sullo scaffale.
 */
class MaterialiSenzaPosizioneController extends AbstractController
{
    #[Route('/admin/materiali-senza-posizione', name: 'admin_materiali_senza_posizione', methods: ['GET'])]
    public function __invoke(MaterialeRepository $materialeRepository): Response
    {
        return $this->render('admin/senza_posizione.html.twig', [
            'materiali' => $materialeRepository->senzaPosizione(),
        ]);
    }
}
