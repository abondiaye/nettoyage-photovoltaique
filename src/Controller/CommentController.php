<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment')]
#[IsGranted('ROLE_MEMBER')]
class CommentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommentRepository $commentRepo,
    ) {
    }

    #[Route('/submit', name: 'app_comment_submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        $user = $this->getUser();
        $text = $request->request->get('text');
        $category = $request->request->get('category', Comment::CATEGORY_OTHER);

        if (empty($text)) {
            $this->addFlash('error', 'Comment cannot be empty.');
            return $this->redirectToRoute('app_member_appointments');
        }

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setText($text);
        $comment->setCategory($category);
        $comment->setStatus(Comment::STATUS_PENDING);

        $this->em->persist($comment);
        $this->em->flush();

        $this->addFlash('success', 'Comment submitted successfully!');
        return $this->redirectToRoute('app_member_appointments');
    }
}
