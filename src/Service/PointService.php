<?php

namespace App\Service;

use App\Entity\Discount;
use App\Entity\Point;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PointService
{
    private const POINTS_DEVIS_ACCEPTED = 10;
    private const POINTS_SERVICE_COMPLETED = 25;
    private const POINTS_FOR_DISCOUNT = 100;
    private const DISCOUNT_PERCENT = 10;

    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function awardPointsForDevis(User $user, int $devisId, string $source = Point::SOURCE_DEVIS_ACCEPTED): void
    {
        $amount = $source === Point::SOURCE_DEVIS_ACCEPTED ? self::POINTS_DEVIS_ACCEPTED : self::POINTS_SERVICE_COMPLETED;

        $point = new Point();
        $point->setUser($user);
        $point->setAmount($amount);
        $point->setSource($source);
        $point->setDevisId($devisId);

        $user->addPoints($amount);
        $this->em->persist($point);
        $this->em->flush();

        $this->checkAndCreateDiscount($user);
    }

    public function awardPointsManual(User $user, int $amount, string $reason = ''): void
    {
        $point = new Point();
        $point->setUser($user);
        $point->setAmount($amount);
        $point->setSource('MANUAL_AWARD');

        $user->addPoints($amount);
        $this->em->persist($point);
        $this->em->flush();

        $this->checkAndCreateDiscount($user);
    }

    private function checkAndCreateDiscount(User $user): void
    {
        if ($user->getPoints() >= self::POINTS_FOR_DISCOUNT) {
            $existingDiscounts = $this->em->getRepository(Discount::class)
                ->findBy(['user' => $user, 'used' => false]);

            if (count($existingDiscounts) === 0) {
                $discount = new Discount();
                $discount->setUser($user);
                $discount->setPercent(self::DISCOUNT_PERCENT);
                $discount->setUsed(false);

                $user->addDiscount($discount);
                $this->em->persist($discount);
                $this->em->flush();
            }
        }
    }

    public function useDiscount(Discount $discount): void
    {
        if (!$discount->isUsed()) {
            $discount->setUsed(true);
            $discount->setUsedAt(new \DateTimeImmutable());
            $this->em->persist($discount);
            $this->em->flush();
        }
    }

    public function getAvailableDiscounts(User $user): array
    {
        return $this->em->getRepository(Discount::class)
            ->findBy(['user' => $user, 'used' => false]);
    }

    public function getPointsHistory(User $user): array
    {
        return $this->em->getRepository(Point::class)
            ->findBy(['user' => $user], ['createdAt' => 'DESC']);
    }
}
