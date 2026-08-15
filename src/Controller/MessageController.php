<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('message/contact.html.twig', [
            'success' => false,
        ]);
    }

    #[Route('/contact/submit', name: 'app_contact_submit', methods: ['POST'])]
    public function submit(Request $request, EntityManagerInterface $em): Response
    {
        $message = new Message();
        $message->setNom($request->request->get('nom'));
        $message->setEmail($request->request->get('email'));
        $message->setMessage($request->request->get('message'));

        $dateStr = $request->request->get('datePreferee');
        if ($dateStr) {
            $message->setDatePreferee(new \DateTime($dateStr));
        }

        $em->persist($message);
        $em->flush();

        return $this->render('message/contact.html.twig', [
            'success' => true,
        ]);
    }

    #[Route('/admin/messages', name: 'app_admin_messages')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminMessages(MessageRepository $messageRepo): Response
    {
        $messages = $messageRepo->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/messages.html.twig', [
            'messages' => $messages,
        ]);
    }
}
