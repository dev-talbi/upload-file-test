<?php

namespace App\Controller;

use App\Entity\UserDocument;
use App\Form\UserDocumentType;
use App\Repository\UserDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/document")
 */
class UserDocumentController extends AbstractController
{
    /**
     * @Route("/", name="user_document_index", methods={"GET"})
     */
    public function index(UserDocumentRepository $userDocumentRepository): Response
    {
        return $this->render('user_document/index.html.twig', [
            'user_documents' => $userDocumentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_document_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $userDocument = new UserDocument();
        $form = $this->createForm(UserDocumentType::class, $userDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userDocument);
            $entityManager->flush();

            return $this->redirectToRoute('user_document_index');
        }

        return $this->render('user_document/new.html.twig', [
            'user_document' => $userDocument,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_document_show", methods={"GET"})
     */
    public function show(UserDocument $userDocument): Response
    {
        return $this->render('user_document/show.html.twig', [
            'user_document' => $userDocument,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_document_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UserDocument $userDocument): Response
    {
        $form = $this->createForm(UserDocumentType::class, $userDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_document_index');
        }

        return $this->render('user_document/edit.html.twig', [
            'user_document' => $userDocument,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_document_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UserDocument $userDocument): Response
    {
        if ($this->isCsrfTokenValid('delete' . $userDocument->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userDocument);
            $entityManager->flush();
        }

        return $this->redirectToRoute('profile', ['id' => $this->getUser()->getId()]);
    }
}
