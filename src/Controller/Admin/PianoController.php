<?php

namespace App\Controller\Admin;

use App\Entity\Piano;
use App\Form\PianoType;
use App\Repository\FilaRepository;
use App\Repository\PianoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/piani', name: 'admin_piano_')]
class PianoController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, PianoRepository $pianoRepository, FilaRepository $filaRepository): Response
    {
        $q = $request->query->get('q');
        $filaId = $request->query->get('fila') ? (int) $request->query->get('fila') : null;

        return $this->render('admin/piano/index.html.twig', [
            'piani' => $pianoRepository->filtra($q, $filaId),
            'file' => $filaRepository->findBy([], ['codice' => 'ASC']),
            'q' => $q,
            'filaId' => $filaId,
        ]);
    }

    #[Route('/nuovo', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $piano = new Piano();
        $form = $this->createForm(PianoType::class, $piano);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($piano);
            $entityManager->flush();

            $this->addFlash('success', 'Piano creato.');

            return $this->redirectToRoute('admin_piano_index');
        }

        return $this->render('admin/piano/form.html.twig', [
            'piano' => $piano,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifica', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Piano $piano, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PianoType::class, $piano);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Piano aggiornato.');

            return $this->redirectToRoute('admin_piano_index');
        }

        return $this->render('admin/piano/form.html.twig', [
            'piano' => $piano,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/elimina', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Piano $piano, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-piano-'.$piano->getId(), $request->request->get('_token'))) {
            $entityManager->remove($piano);
            $entityManager->flush();
            $this->addFlash('success', 'Piano eliminato.');
        }

        return $this->redirectToRoute('admin_piano_index');
    }
}
