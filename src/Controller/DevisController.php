<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Devis;
use App\Form\DevisType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DevisController extends AbstractController
{
    #[Route('/devis', name: 'app_devis')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $devis = new Devis();
        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $client = new Client();
            $client->setNom($form->get('clientNom')->getData());
            $client->setPrenom($form->get('clientPrenom')->getData());
            $client->setEmail($form->get('clientEmail')->getData());
            $client->setTelephone($form->get('clientTelephone')->getData());
            $client->setAdresse($form->get('clientAdresse')->getData());
            $client->setCodePostal($form->get('clientCodePostal')->getData());
            $client->setVille($form->get('clientVille')->getData());

            $devis->setClient($client);

            $em->persist($client);
            $em->persist($devis);
            $em->flush();

            $this->addFlash('success', 'Votre demande de devis a bien été envoyée. Nous vous recontacterons rapidement.');

            return $this->redirectToRoute('app_devis');
        }

        return $this->render('devis/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
