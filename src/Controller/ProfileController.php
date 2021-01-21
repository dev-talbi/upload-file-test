<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DocumentType;
use App\Entity\UserDocument;
use PhpParser\Builder\Property;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;



class ProfileController extends AbstractController
{
    /**
     * 
     * @IsGranted("ROLE_USER")
     * @Route("/profile/{id}", name="profile")
     * 
     */
    public function index(SluggerInterface $slugger, Request $request, EntityManagerInterface $manager, User $user, SessionInterface $session): Response
    {
        if ($this->getUser()->getId() === $user->getId()) {
            $document = new UserDocument;

            $uploadForm = $this->createForm(DocumentType::class, $document);
            $uploadForm->handleRequest($request);

            if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
                $file = $uploadForm->get('upload')->getData();

                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
                    try {
                        $file->move(
                            $this->getParameter('files_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                    }
                    $document->setUser($user)
                        ->setName($document->getName())
                        ->setUpload($newFilename);
                    $manager->persist($document);
                    $manager->flush();
                }
            }

            $documents = $this->getDoctrine()
                ->getRepository(UserDocument::class)
                ->findBy(['user' => $user]);

            return $this->render('profile/index.html.twig', [
                'controller_name' => 'ProfileController',
                'uploadForm' => $uploadForm->createView(),
                'documents' => $documents,
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }
}
