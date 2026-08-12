<?php

namespace App\Service;

use App\Entity\Reservation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    /**
     * Envoyer email de confirmation de réservation
     */
    public function sendConfirmationEmail(Reservation $reservation): void
    {
        $email = (new Email())
            ->from('noreply@sirius-solar.ch')
            ->to($reservation->getCustomer()->getEmail())
            ->subject('✅ Votre intervention a été confirmée - Sirius-Solar')
            ->html($this->renderConfirmationEmail($reservation));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't throw - email failure shouldn't break the app
            error_log('Email error: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer email de refus
     */
    public function sendRefusalEmail(Reservation $reservation, string $raison): void
    {
        $email = (new Email())
            ->from('noreply@sirius-solar.ch')
            ->to($reservation->getCustomer()->getEmail())
            ->subject('❌ Votre demande de nettoyage - Sirius-Solar')
            ->html($this->renderRefusalEmail($reservation, $raison));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Email error: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer email de report d'intervention
     */
    public function sendRescheduleEmail(Reservation $reservation): void
    {
        $email = (new Email())
            ->from('noreply@sirius-solar.ch')
            ->to($reservation->getCustomer()->getEmail())
            ->subject('📅 Votre intervention a été reportée - Sirius-Solar')
            ->html($this->renderRescheduleEmail($reservation));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Email error: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer email d'annulation
     */
    public function sendCancellationEmail(Reservation $reservation, string $motif): void
    {
        $email = (new Email())
            ->from('noreply@sirius-solar.ch')
            ->to($reservation->getCustomer()->getEmail())
            ->subject('🚫 Votre intervention a été annulée - Sirius-Solar')
            ->html($this->renderCancellationEmail($reservation, $motif));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Email error: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer email de réalisation d'intervention
     */
    public function sendCompletionEmail(Reservation $reservation): void
    {
        $email = (new Email())
            ->from('noreply@sirius-solar.ch')
            ->to($reservation->getCustomer()->getEmail())
            ->subject('✨ Votre intervention a été réalisée - Sirius-Solar')
            ->html($this->renderCompletionEmail($reservation));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Email error: ' . $e->getMessage());
        }
    }

    // === Email templates ===

    private function renderConfirmationEmail(Reservation $reservation): string
    {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>✅ Votre intervention a été confirmée</h2>
            <p>Bonjour {$reservation->getCustomer()->getPrenom()},</p>
            <p>Nous avons le plaisir de confirmer votre réservation de nettoyage de panneaux solaires.</p>

            <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <strong>Détails de votre intervention:</strong>
                <p>
                    📅 Date: {$reservation->getDateSouhaitee()->format('d/m/Y')}<br>
                    ⏰ Heure: {$reservation->getHeureSouhaitee()->format('H:i')}<br>
                    📍 Adresse: {$reservation->getCustomer()->getAdresse()}<br>
                    🌐 Nombre de panneaux: {$reservation->getNombrePanneaux()}<br>
                    💰 Prix estimé: CHF {$reservation->getPrixEstime()}
                </p>
            </div>

            <p>Merci de choisir Sirius-Solar pour l'entretien de vos panneaux solaires!</p>
            <p>Cordialement,<br><strong>Équipe Sirius-Solar</strong></p>
            <p style="color: #999; font-size: 12px;">077 909 64 13 | ndiayeharouna1991@gmail.com</p>
        </div>
        HTML;
    }

    private function renderRefusalEmail(Reservation $reservation, string $raison): string
    {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>❌ Votre demande de nettoyage</h2>
            <p>Bonjour {$reservation->getCustomer()->getPrenom()},</p>
            <p>Malheureusement, votre demande de nettoyage de panneaux solaires n'a pas pu être acceptée.</p>

            <div style="background: #ffe0e0; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <strong>Raison:</strong>
                <p>{$raison}</p>
            </div>

            <p>N'hésitez pas à nous contacter pour discuter d'autres options.</p>
            <p>Cordialement,<br><strong>Équipe Sirius-Solar</strong></p>
            <p style="color: #999; font-size: 12px;">077 909 64 13 | ndiayeharouna1991@gmail.com</p>
        </div>
        HTML;
    }

    private function renderRescheduleEmail(Reservation $reservation): string
    {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>📅 Votre intervention a été reportée</h2>
            <p>Bonjour {$reservation->getCustomer()->getPrenom()},</p>
            <p>Votre intervention de nettoyage a été reportée à une nouvelle date.</p>

            <div style="background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <strong>Nouvelle date:</strong>
                <p>📅 {$reservation->getDateSouhaitee()->format('d/m/Y')}<br>⏰ {$reservation->getHeureSouhaitee()->format('H:i')}</p>
            </div>

            <p>Cordialement,<br><strong>Équipe Sirius-Solar</strong></p>
            <p style="color: #999; font-size: 12px;">077 909 64 13 | ndiayeharouna1991@gmail.com</p>
        </div>
        HTML;
    }

    private function renderCancellationEmail(Reservation $reservation, string $motif): string
    {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>🚫 Votre intervention a été annulée</h2>
            <p>Bonjour {$reservation->getCustomer()->getPrenom()},</p>
            <p>Nous vous informons que votre intervention a été annulée.</p>

            <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <strong>Motif:</strong>
                <p>{$motif}</p>
            </div>

            <p>N'hésitez pas à prendre contact pour toute question.</p>
            <p>Cordialement,<br><strong>Équipe Sirius-Solar</strong></p>
            <p style="color: #999; font-size: 12px;">077 909 64 13 | ndiayeharouna1991@gmail.com</p>
        </div>
        HTML;
    }

    private function renderCompletionEmail(Reservation $reservation): string
    {
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>✨ Votre intervention a été réalisée</h2>
            <p>Bonjour {$reservation->getCustomer()->getPrenom()},</p>
            <p>Nous avons le plaisir de vous confirmer que votre intervention de nettoyage a été réalisée avec succès!</p>

            <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <p>Vos panneaux solaires sont maintenant optimisés pour une production énergétique maximale.</p>
            </div>

            <p>Merci de faire confiance à Sirius-Solar!</p>
            <p>Cordialement,<br><strong>Équipe Sirius-Solar</strong></p>
            <p style="color: #999; font-size: 12px;">077 909 64 13 | ndiayeharouna1991@gmail.com</p>
        </div>
        HTML;
    }
}
