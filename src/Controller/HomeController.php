<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $services = [
            [
                'titre' => 'Nettoyage standard',
                'description' => 'Nettoyage à l\'eau déminéralisée et brosses douces, sans rayer les panneaux. Idéal en entretien régulier.',
                'prix' => 'à partir de 3 € / panneau',
            ],
            [
                'titre' => 'Nettoyage avec traitement anti-mousse',
                'description' => 'En complément du nettoyage standard, application d\'un traitement limitant la repousse de mousses et lichens.',
                'prix' => 'à partir de 4,50 € / panneau',
            ],
            [
                'titre' => 'Contrat d\'entretien annuel',
                'description' => 'Passages programmés (1 à 3 fois par an) avec rapport de production et d\'état visuel des panneaux.',
                'prix' => 'sur devis',
            ],
        ];

        $avantages = [
            'Jusqu\'à 25 % de perte de rendement évitée grâce à un nettoyage régulier',
            'Intervention par des techniciens formés au travail en hauteur',
            'Eau osmosée : aucun résidu calcaire, aucun produit chimique agressif',
            'Devis gratuit et rapport d\'intervention détaillé',
        ];

        return $this->render('home/index.html.twig', [
            'services' => $services,
            'avantages' => $avantages,
        ]);
    }
}
