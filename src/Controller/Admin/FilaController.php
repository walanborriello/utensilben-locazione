<?php

namespace App\Controller\Admin;

use App\Entity\Fila;
use App\Enum\TipoFila;
use App\Form\FilaType;
use App\Repository\FilaRepository;
use App\Service\FilaAreaSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/file', name: 'admin_fila_')]
class FilaController extends AbstractController
{
    public function __construct(private readonly FilaAreaSynchronizer $filaAreaSynchronizer)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, FilaRepository $filaRepository): Response
    {
        $q = $request->query->get('q');
        $tipoParam = $request->query->get('tipo');
        $tipo = $tipoParam ? TipoFila::tryFrom($tipoParam) : null;

        return $this->render('admin/fila/index.html.twig', [
            'file' => $filaRepository->filtra($q, $tipo),
            'tipiFila' => TipoFila::cases(),
            'q' => $q,
            'tipoParam' => $tipoParam,
        ]);
    }

    #[Route('/nuova', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fila = new Fila();
        $form = $this->createForm(FilaType::class, $fila);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filaAreaSynchronizer->sincronizza($fila);
            $entityManager->persist($fila);
            $entityManager->flush();

            $this->addFlash('success', 'Fila creata. Le aree corrispondenti sono state generate automaticamente.');

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
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filaAreaSynchronizer->sincronizza($fila);
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
}
