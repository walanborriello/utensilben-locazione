<?php

namespace App\Controller\Admin;

use App\Entity\Materiale;
use App\Form\MaterialeType;
use App\Repository\CategoriaRepository;
use App\Repository\MaterialeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/materiali', name: 'admin_materiale_')]
class MaterialeController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, MaterialeRepository $materialeRepository, CategoriaRepository $categoriaRepository): Response
    {
        $q = $request->query->get('q');
        $categoriaId = $request->query->get('categoria') ? (int) $request->query->get('categoria') : null;
        $attivoParam = $request->query->get('attivo');
        $attivo = match ($attivoParam) {
            '1' => true,
            '0' => false,
            default => null,
        };

        return $this->render('admin/materiale/index.html.twig', [
            'materiali' => $materialeRepository->filtra($q, $categoriaId, $attivo),
            'categorie' => $categoriaRepository->findBy([], ['nome' => 'ASC']),
            'q' => $q,
            'categoriaId' => $categoriaId,
            'attivoParam' => $attivoParam,
        ]);
    }

    #[Route('/nuovo', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $materiale = new Materiale();
        $form = $this->createForm(MaterialeType::class, $materiale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($materiale);
            $entityManager->flush();

            $this->addFlash('success', 'Prodotto creato.');

            return $this->redirectToRoute('admin_materiale_index');
        }

        return $this->render('admin/materiale/form.html.twig', [
            'materiale' => $materiale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifica', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Materiale $materiale, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MaterialeType::class, $materiale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Prodotto aggiornato.');

            return $this->redirectToRoute('admin_materiale_index');
        }

        return $this->render('admin/materiale/form.html.twig', [
            'materiale' => $materiale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/elimina', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Materiale $materiale, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-materiale-'.$materiale->getId(), $request->request->get('_token'))) {
            $entityManager->remove($materiale);
            $entityManager->flush();
            $this->addFlash('success', 'Prodotto eliminato.');
        }

        return $this->redirectToRoute('admin_materiale_index');
    }
}
