<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use App\Form\CategoriaType;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categorie', name: 'admin_categoria_')]
class CategoriaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, CategoriaRepository $categoriaRepository): Response
    {
        $q = $request->query->get('q');

        return $this->render('admin/categoria/index.html.twig', [
            'categorie' => $categoriaRepository->filtra($q),
            'q' => $q,
        ]);
    }

    #[Route('/nuova', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categoria = new Categoria();
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categoria);
            $entityManager->flush();

            $this->addFlash('success', 'Categoria creata.');

            return $this->redirectToRoute('admin_categoria_index');
        }

        return $this->render('admin/categoria/form.html.twig', [
            'categoria' => $categoria,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifica', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categoria $categoria, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Categoria aggiornata.');

            return $this->redirectToRoute('admin_categoria_index');
        }

        return $this->render('admin/categoria/form.html.twig', [
            'categoria' => $categoria,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/elimina', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Categoria $categoria, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-categoria-'.$categoria->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categoria);
            $entityManager->flush();
            $this->addFlash('success', 'Categoria eliminata.');
        }

        return $this->redirectToRoute('admin_categoria_index');
    }
}
