<?php

namespace App\Controller;

use App\Entity\Appointment;
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

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                error_log('Form validation errors: ' . json_encode($this->getFormErrors($form)));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $clientNom = $form->get('clientNom')->getData();
            $clientPrenom = $form->get('clientPrenom')->getData();
            $clientEmail = $form->get('clientEmail')->getData();
            $clientTelephone = $form->get('clientTelephone')->getData();
            $clientAdresse = $form->get('clientAdresse')->getData();
            $clientCodePostal = $form->get('clientCodePostal')->getData();
            $clientVille = $form->get('clientVille')->getData();
            $message = $form->get('message')->getData();

            $client = new Client();
            $client->setNom($clientNom);
            $client->setPrenom($clientPrenom);
            $client->setEmail($clientEmail);
            $client->setTelephone($clientTelephone);
            $client->setAdresse($clientAdresse);
            $client->setCodePostal($clientCodePostal);
            $client->setVille($clientVille);

            $devis->setClient($client);

            $appointment = new Appointment();
            $appointment->setClientName("$clientPrenom $clientNom");
            $appointment->setClientEmail($clientEmail);
            $appointment->setClientPhone($clientTelephone);
            $appointment->setNotes($message);
            $appointment->setRequestedDate(new \DateTime());
            $appointment->setStatus('pending');

            $em->persist($client);
            $em->persist($devis);
            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', 'Votre demande de devis a bien été envoyée. Nous vous recontacterons rapidement.');

            return $this->redirectToRoute('app_devis');
        }

        return $this->render('devis/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
        }
        return $errors;
    }
}
