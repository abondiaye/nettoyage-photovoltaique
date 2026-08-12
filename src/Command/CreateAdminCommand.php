<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un nouvel utilisateur administrateur',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Crée un nouvel utilisateur administrateur')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe')
            ->addArgument('nom', InputArgument::OPTIONAL, 'Nom')
            ->addArgument('prenom', InputArgument::OPTIONAL, 'Prénom');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);

        // Récupérer les arguments ou demander interactivement
        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Email de l\'administrateur: ');
            $email = $helper->ask($input, $output, $question);
        }

        $password = $input->getArgument('password');
        if (!$password) {
            $question = new Question('Mot de passe: ');
            $question->setHidden(true);
            $password = $helper->ask($input, $output, $question);
        }

        $nom = $input->getArgument('nom');
        if (!$nom) {
            $question = new Question('Nom: ');
            $nom = $helper->ask($input, $output, $question);
        }

        $prenom = $input->getArgument('prenom');
        if (!$prenom) {
            $question = new Question('Prénom: ');
            $prenom = $helper->ask($input, $output, $question);
        }

        // Valider les données
        if (!$email || !$password || !$nom || !$prenom) {
            $io->error('Tous les champs sont obligatoires');
            return Command::FAILURE;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Email invalide');
            return Command::FAILURE;
        }

        // Vérifier que l'user n'existe pas déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà');
            return Command::FAILURE;
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsActive(true);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Administrateur créé avec succès!");
        $io->table(
            ['Email', 'Nom', 'Prénom'],
            [[$user->getEmail(), $user->getNom(), $user->getPrenom()]]
        );

        return Command::SUCCESS;
    }
}
