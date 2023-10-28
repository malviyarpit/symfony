<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\BookRepository;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{

    public function __construct(
        private UserRepository $user,
        private BookRepository $book,
        private CommentRepository $commentRepo,
        private UserPasswordHasherInterface $passwordEncoder
    )
    {
        
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/user', name: 'app_admin_users')]
    public function showUser(): Response
    {
        if($this->isGranted('ROLE_ADMIN') == false){
          return $this->redirectToRoute("app_book");
        }

        $usersList = $this->user->findAll();
        
        return $this->render('admin/show_user.html.twig', [
            'users' => $usersList
        ]);
    }

    #[Route('/admin/user/{id<\d+>}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
        ): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user);
        $originalPassword = $user->getPassword();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword))  {  
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                           $user,
                           $form->get('password')->getData()
                   )
                 );           
            }
            else {
                $user->setPassword($originalPassword);
            }

            $entityManager->flush();
            $this->addFlash('success', 'User updated successfully');

            return $this->redirectToRoute('app_admin', ['id' => $user->getId()]);
        }

        return $this->render('admin/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/user/{user}', name: 'app_user_show')]
    public function showOne(User $user): Response
    {   
        return $this->render('admin/user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/admin/removeuser/{id}', name: 'admin_user_delete')]
    public function removeUser(
        User $user,
        EntityManagerInterface $entity,
        BookRepository $bookRepo): Response
    {       
        $entity->remove($user);
        $entity->flush();

        return $this->redirectToRoute("app_admin");
    }

    #[Route('/admin/book', name: 'app_admin_show_books')]
    public function showBooks(BookRepository $bookRepo): Response
    {
        $books = $this->book->findAll();

        return $this->render('admin/book/show_book.html.twig', [
            'books' => $books
        ]);
    }
}
