<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin/customers', name: 'admin_customers_')]
#[IsGranted('ROLE_ADMIN')]
class AdminCustomerController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(
        CustomerRepository $customerRepository,
        Request $request
    ): Response {
        $search = $request->query->get('search');
        $ville = $request->query->get('ville');

        if ($search) {
            $customers = $customerRepository->findBySearchTerm($search);
        } elseif ($ville) {
            $customers = $customerRepository->findByVille($ville);
        } else {
            $customers = $customerRepository->findAll();
        }

        return $this->render('admin/customers/list.html.twig', [
            'customers' => $customers,
            'search' => $search,
            'ville' => $ville,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(
        int $id,
        CustomerRepository $customerRepository,
        ReservationRepository $reservationRepository
    ): Response {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            throw $this->createNotFoundException('Client non trouvé');
        }

        $reservations = $reservationRepository->findByCustomer($customer);

        return $this->render('admin/customers/show.html.twig', [
            'customer' => $customer,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            throw $this->createNotFoundException('Client non trouvé');
        }

        if ($request->isMethod('POST')) {
            $customer->setNom($request->request->get('nom', $customer->getNom()));
            $customer->setPrenom($request->request->get('prenom', $customer->getPrenom()));
            $customer->setEmail($request->request->get('email', $customer->getEmail()));
            $customer->setTelephone($request->request->get('telephone', $customer->getTelephone()));
            $customer->setAdresse($request->request->get('adresse', $customer->getAdresse()));
            $customer->setVille($request->request->get('ville', $customer->getVille()));
            $customer->setCodePostal($request->request->get('codePostal', $customer->getCodePostal()));

            $entityManager->flush();
            $this->addFlash('success', 'Informations client mises à jour');

            return $this->redirectToRoute('admin_customers_show', ['id' => $id]);
        }

        return $this->render('admin/customers/edit.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        int $id,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            throw $this->createNotFoundException('Client non trouvé');
        }

        // Vérifier si le client a des réservations
        if (count($customer->getReservations()) > 0) {
            $this->addFlash('error', 'Impossible de supprimer un client qui a des réservations');
            return $this->redirectToRoute('admin_customers_show', ['id' => $id]);
        }

        $entityManager->remove($customer);
        $entityManager->flush();

        $this->addFlash('success', 'Client supprimé');
        return $this->redirectToRoute('admin_customers_list');
    }
}
