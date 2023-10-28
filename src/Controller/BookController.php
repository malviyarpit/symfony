<?php

namespace App\Controller;

use DateTime;
use App\Entity\Book;
use App\Form\BookType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\BookRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('/add', name: 'app_book_add')]
    #[IsGranted('ROLE_AUTHOR')]
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

            return $this->redirectToRoute('app_book');
        }
        return $this->renderForm(
            'book/add.html.twig',
            [
                'form' => $form
            ]
            );
    }

    #[Route('/{book}/edit', name: 'app_book_edit')]
    #[IsGranted('ROLE_AUTHOR')]
    public function edit(Book $book, Request $request, BookRepository $books): Response
    {   
        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $bookData = $form->getData();
            $books->add($bookData, true);

            $this->addFlash('success', 'Book data has been updated successfully');

            return $this->redirectToRoute('app_book');
        }
        return $this->renderForm(
            'book/edit.html.twig',
            [
                'form' => $form,
                'book' => $book
            ]
            );
    }

    #[Route('/', name: 'app_book')]
    public function index(BookRepository $bookRepo): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute("app_admin");
        }

        $books = $bookRepo->findAllWithComments();
        
        return $this->render('book/index.html.twig', [
            'books' => $books
        ]);
    }

    #[Route('/{book}', name: 'app_book_show')]
    public function showOne(Book $book): Response
    {   
        return $this->render('book/show.html.twig', [
            'book' => $book
        ]);
    }

    #[Route('/{book}/comment', name: 'app_book_comment')]
    public function addComment(Book $book, Request $request, CommentRepository $comments): Response
    {   
        $form = $this->createForm(CommentType::class, new Comment());

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setBook($book);
            $comments->add($comment, true);

            $this->addFlash('success', ' Book Comment added successfully');

            return $this->redirectToRoute(
                'app_book_show',
                ['book' => $book->getId()]
            );
        }
        return $this->renderForm(
            'book/comment.html.twig',
            [
                'form' => $form,
                'book' => $book
            ]
            );
    }
}
