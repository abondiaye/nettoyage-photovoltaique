<?php

namespace App\Controller;

use App\Entity\PasswordReset;
use App\Entity\User;
use App\Repository\PasswordResetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $success = false;
        $error = null;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            if (!$email) {
                $error = 'Veuillez entrer une adresse email.';
            } else {
                $user = $userRepository->findOneBy(['email' => $email]);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = new \DateTimeImmutable('+1 hour');

                    $passwordReset = new PasswordReset();
                    $passwordReset->setUser($user);
                    $passwordReset->setEmail($email);
                    $passwordReset->setToken($token);
                    $passwordReset->setExpiresAt($expiresAt);
                    $passwordReset->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($passwordReset);
                    $em->flush();

                    $resetLink = $this->generateUrl('app_reset_password', ['token' => $token], 0);
                    $resetUrl = $request->getSchemeAndHttpHost() . $resetLink;

                    $fromEmail = getenv('MAILER_FROM_ADDRESS') ?: 'noreply@sirius-solar.ch';

                    $emailMessage = (new Email())
                        ->from($fromEmail)
                        ->to($email)
                        ->subject('🔑 Réinitialiser votre mot de passe - Sirius-Solar')
                        ->html($this->renderView('emails/reset_password.html.twig', [
                            'resetUrl' => $resetUrl,
                            'expiresAt' => $expiresAt->format('d/m/Y à H:i'),
                        ]));

                    $mailer->send($emailMessage);
                }

                $success = true;
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'success' => $success,
            'error' => $error,
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        PasswordResetRepository $resetRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $passwordReset = $resetRepository->findOneBy(['token' => $token]);

        if (!$passwordReset || $passwordReset->isExpired()) {
            $this->addFlash('error', '❌ Le lien de réinitialisation a expiré. Veuillez demander un nouveau lien.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if (!$password || !$passwordConfirm) {
                $error = 'Veuillez remplir tous les champs.';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 8) {
                $error = 'Le mot de passe doit contenir au moins 8 caractères.';
            } else {
                $user = $passwordReset->getUser();
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                $em->remove($passwordReset);
                $em->flush();

                $this->addFlash('success', '✅ Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'error' => $error,
            'token' => $token,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
