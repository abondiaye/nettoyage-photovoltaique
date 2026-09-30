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
    /** Options facultatives du formulaire (prix HT en CHF). */
    private const OPTIONS = [
        'haute_pression' => ['Haute pression', 150],
        'inspection' => ['Inspection', 80],
        'protection' => ['Protection', 200],
        'analyse' => ['Analyse de performance', 120],
    ];

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
            $requestedDate = $form->get('requestedDate')->getData();

            // --- Estimation : surface × tarif au m² + options ---
            $surface = (float) $devis->getSurface();
            $tarif = Devis::tarifM2($surface);
            $base = round($surface * $tarif, 2);
            $chosen = array_values(array_intersect(array_keys(self::OPTIONS), (array) $request->request->all('lavage_types')));
            $optionsTotal = 0;
            $optionsTxt = [];
            foreach ($chosen as $key) {
                [$label, $prix] = self::OPTIONS[$key];
                $optionsTotal += $prix;
                $optionsTxt[] = $label . ' (CHF ' . $prix . '.–)';
            }
            $total = max($base + $optionsTotal, Devis::MINIMUM_FACTURATION);
            $devis->setPrixEstime($total);

            $chf = fn (float $n) => 'CHF ' . number_format($n, 2, '.', "'");
            $resume = sprintf(
                "Demande de devis : %s m² × %s/m² = %s HT\nToit : %s, %s\nEau / électricité : %s%s\nEstimation totale : %s HT%s\nHors zone habituelle : + %s HT de déplacement si applicable",
                rtrim(rtrim(number_format($surface, 1, '.', ''), '0'), '.'),
                $chf($tarif),
                $chf($base),
                $devis->getToitTypeLabel(),
                mb_strtolower((string) $devis->getToitAccesLabel()),
                $devis->getAccesEauElecLabel(),
                $optionsTxt ? "\nOptions : " . implode(', ', $optionsTxt) : '',
                $chf($total),
                $total > $base + $optionsTotal ? ' (minimum de facturation)' : '',
                $chf(Devis::SUPPLEMENT_HORS_ZONE)
            );
            $notes = $resume . ($message ? "\n\nMessage : " . $message : '');

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
            $appointment->setNotes($notes);
            $appointment->setRequestedDate($requestedDate ?? new \DateTime());
            $appointment->setStatus('pending');

            $em->persist($client);
            $em->persist($devis);
            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', '✅ Demande reçue! Nous analyserons votre demande sous 48h et vous recontacterons par email à ' . $clientEmail . ' pour confirmer votre rendez-vous.');

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
