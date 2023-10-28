<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Book;
use App\Entity\User;
use App\Form\BookType;
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

class BookController extends AbstractController
{

    public function __construct(
        private UserRepository $user,
        private BookRepository $book,
        private CommentRepository $commentRepo,
        private UserPasswordHasherInterface $passwordEncoder
    )
    {
        
    }

    #[Route('/admin/book/add', name: 'app_admin_book_add')]
    #[IsGranted('ROLE_ADMIN')]
    public function add(Request $request, BookRepository $books): Response
    {   
        $form = $this->createForm(BookType::class, new Book());

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $bookData = $form->getData();
            $bookData->setCreated(new DateTime());
            $bookData->setAuthor($this->getUser());
            $books->add($bookData, true);

            $this->addFlash('success', 'New Book has been created successfully');

            return $this->redirectToRoute('app_admin_show_books');
        }
        return $this->renderForm(
            'book/add.html.twig',
            [
                'form' => $form
            ]
            );
    }

    #[Route('/admin/book/{book}/edit', name: 'app_admin_book_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Book $book, Request $request, BookRepository $books): Response
    {   
        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $bookData = $form->getData();
            $books->add($bookData, true);

            $this->addFlash('success', 'Book data has been updated successfully');

            return $this->redirectToRoute('app_admin_show_books');
        }

        return $this->renderForm(
            'book/edit.html.twig',
            [
                'form' => $form,
                'book' => $book
            ]
            );
    }

    #[Route('/admin/book', name: 'app_admin_show_books')]
    public function showBooks(BookRepository $bookRepo): Response
    {
        $books = $this->book->findAll();

        return $this->render('admin/book/show_book.html.twig', [
            'books' => $books
        ]);
    }

    #[Route('/admin/removebook/{book}', name: 'app_book_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function removeBook(
        Book $book,
        EntityManagerInterface $entity,
        BookRepository $bookRepo): Response
    {       
        $entity->remove($book);
        $entity->flush();

        return $this->redirectToRoute("app_admin_show_books");
    }
}
