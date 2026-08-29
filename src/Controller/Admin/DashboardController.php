<?php

namespace App\Controller\Admin;

use App\Repository\CategoriaRepository;
use App\Repository\FilaRepository;
use App\Repository\MaterialeRepository;
use App\Repository\PianoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard', methods: ['GET'])]
    public function index(
        MaterialeRepository $materialeRepository,
        FilaRepository $filaRepository,
        PianoRepository $pianoRepository,
        CategoriaRepository $categoriaRepository,
    ): Response {
        $totaleMateriali = $materialeRepository->count([]);
        $materialiAttivi = $materialeRepository->count(['attivo' => true]);
        $materialiSenzaPosizione = $materialeRepository->contaSenzaPosizione();
        $materialiPosizionati = max(0, $materialiAttivi - $materialiSenzaPosizione);
        $percentualePosizionati = $materialiAttivi > 0
            ? (int) round($materialiPosizionati / $materialiAttivi * 100)
            : 0;

        $materialiPerFila = $filaRepository->materialiPerFila();
        $massimoPerFila = array_reduce(
            $materialiPerFila,
            static fn (int $massimo, array $riga): int => max($massimo, $riga['totale']),
            0
        );

        return $this->render('admin/dashboard.html.twig', [
            'totaleMateriali' => $totaleMateriali,
            'materialiAttivi' => $materialiAttivi,
            'materialiSenzaPosizione' => $materialiSenzaPosizione,
            'materialiPosizionati' => $materialiPosizionati,
            'percentualePosizionati' => $percentualePosizionati,
            'totaleFile' => $filaRepository->count([]),
            'totalePiani' => $pianoRepository->count([]),
            'totaleCategorie' => $categoriaRepository->count([]),
            'materialiPerFila' => $materialiPerFila,
            'massimoPerFila' => $massimoPerFila,
        ]);
    }
}
