<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test1@test.com');
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                '1234567'
            )
        );

        $manager->persist($user);

        $user2 = new User();
        $user2->setEmail('test2@test.com');
        $user2->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user2,
                '1234567'
            )
        );

        $manager->persist($user2);

        $book = new Book();
        $book->setTitle('Test Title 1');
        $book->setText('This is the sample text');
        $book->setAuthor($user);
        $book->setCreated(new \DateTime());
        $manager->persist($book);

        $book1 = new Book();
        $book1->setTitle('Test Title 2');
        $book1->setText('This is the sample text');
        $book1->setAuthor($user2);
        $book1->setCreated(new \DateTime());
        $manager->persist($book1);

        $book2 = new Book();
        $book2->setTitle('Test Title 2');
        $book2->setText('This is the sample text');
        $book2->setAuthor($user);
        $book2->setCreated(new \DateTime());
        $manager->persist($book2);

        $manager->flush();
    }
}
