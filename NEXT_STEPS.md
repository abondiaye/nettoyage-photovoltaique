# Prochaines Étapes - Configuration & Migration Base de Données

## 1. Configuration Symfony

### 1.1 Fichier `.env` (ou `.env.local`)
```bash
# Database
DATABASE_URL="mysql://root:password@127.0.0.1:3306/sirius_solar"

# Email (Mailer)
MAILER_DSN="smtp://localhost:1025"
# Ou pour production:
# MAILER_DSN="sendmail://default"

# APP
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=votre_cle_secrete_aleatoire
```

### 1.2 Configuration Sécurité (`config/packages/security.yaml`)
Créer ou modifier ce fichier avec la configuration fournie dans IMPLEMENTATION_GUIDE.md

## 2. Migrations Doctrine

### 2.1 Créer la base de données
```bash
php bin/console doctrine:database:create
```

### 2.2 Générer les migrations
```bash
php bin/console make:migration
```
Cela scannera toutes les entités et créera les migrations automatiquement.

### 2.3 Exécuter les migrations
```bash
php bin/console doctrine:migrations:migrate
```

## 3. Créer l'Utilisateur Admin Initial

### Option A: Commande interactive
```bash
php bin/console make:user

# Répondre aux questions:
# - Class name: User
# - Will this app need to hash/check user passwords? (yes/no): yes
# - Name of the property for storing hashed passwords: password
# - Roles to assign to this user: ROLE_ADMIN
# - Do you want to add a "create user" command?: no
```

### Option B: Créer une commande custom (recommandé)
Créer `src/Command/CreateAdminCommand.php`:

```php
<?php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';

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
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom')
            ->addArgument('prenom', InputArgument::REQUIRED, 'Prénom');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setEmail($input->getArgument('email'));
        $user->setNom($input->getArgument('nom'));
        $user->setPrenom($input->getArgument('prenom'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsActive(true);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $input->getArgument('password')
        );
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Admin créé: ' . $user->getEmail());
        return Command::SUCCESS;
    }
}
```

Utilisation:
```bash
php bin/console app:create-admin admin@sirius-solar.ch password123 Admin Sirius
```

## 4. Tester la Configuration

### 4.1 Démarrer le serveur de développement
```bash
# Avec Symfony CLI
symfony server:start

# Ou avec PHP
php -S localhost:8000 -t public
```

### 4.2 Vérifier les routes
```bash
php bin/console debug:router
```

Vous devriez voir:
- `admin_dashboard` → `/admin/`
- `admin_reservations_list` → `/admin/reservations/`
- `admin_customers_list` → `/admin/customers/`
- `app_login` → `/login`
- `reservation_form` → `/reserver`

### 4.3 Tester la login
1. Accédez à http://localhost:8000/login
2. Entrez les identifiants admin créés
3. Vous devriez être redirigé vers `/admin/`

## 5. Fichiers à Vérifier/Modifier

### Pour Doctrine:
- Vérifier que `config/doctrine.yaml` est correctement configuré
- `.env` doit avoir DATABASE_URL valide

### Pour Mailer:
- Configurer le mailer dans `.env` (MAILER_DSN)
- Pour développement: utiliser mailhog ou sendmail
- Pour production: utiliser SMTP valide

### Pour Security:
- `config/packages/security.yaml` - doit exister avec la config fournie
- Les routes admin protégées par `#[IsGranted('ROLE_ADMIN')]`

## 6. Vérifier les Entités Existantes

Avant de migrer, s'assurer que toutes les entités de Phase 1 sont présentes:

```bash
# Listers les entités
php bin/console doctrine:mapping:info

# Vous devriez voir:
# - App\Entity\Reservation
# - App\Entity\Customer
# - App\Entity\Intervention
# - App\Entity\ReservationHistory
# - App\Entity\Notification
# - App\Entity\User
```

## 7. Flux de Test Complet

### Étape 1: Admin crée une offre
1. Accédez `/admin/`
2. Voyez le dashboard vide

### Étape 2: Client fait une réservation
1. Accédez `/reserver`
2. Remplissez le formulaire
3. Soumettez

### Étape 3: Admin confirme
1. Retour à `/admin/`
2. Voyez la nouvelle demande dans "Demandes en attente"
3. Cliquez "Détails"
4. Cliquez "Confirmer"
5. Vérifiez la notification et l'historique

### Étape 4: Admin marque comme réalisée
1. Dans la même réservation
2. Entrez le prix réalisé
3. Cliquez "Marquer réalisée"
4. Le statut passe à "REALISEE"

## 8. Commandes Utiles pour Développement

```bash
# Réinitialiser complètement la DB
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Vérifier les routes
php bin/console debug:router

# Vérifier la config de sécurité
php bin/console debug:config security

# Tester une requête SQL
php bin/console doctrine:query:sql "SELECT * FROM reservation"

# Générer dummy data (optionnel)
# Créer des fixtures pour tests
```

## 9. Troubleshooting

### "Migration not found"
→ Vérifier que `bin/console make:migration` a créé un fichier dans `migrations/`

### "User provider not found"
→ Vérifier que `config/packages/security.yaml` contient la config users

### "Doctrine not configured"
→ Vérifier DATABASE_URL dans `.env`

### "CSRF token not valid"
→ Vérifier que les sessions sont configurées (devrait être auto)

## 10. Points Importants à Retenir

✅ **Database**
- Créer la DB avant de migrer
- Migrations créées automatiquement à partir des entités
- Toujours tester en développement d'abord

✅ **Security**
- Tous les contrôleurs admin utilisent `#[IsGranted('ROLE_ADMIN')]`
- Les passwords sont hashés avec la stratégie auto
- Les CSRF tokens sont protégés automatiquement

✅ **Services**
- EmailService dépend de MailerInterface
- ReservationService appelle NotificationService et EmailService
- Chaque transition crée un historique

✅ **Templates**
- Utiliser `{{ path('route_name') }}` pour les URLs
- Flash messages affichés automatiquement
- Responsive design intégré

## 11. Documentation Disponible

- **IMPLEMENTATION_GUIDE.md** - Architecture complète
- **PHASE2_CHANGELOG.md** - Fichiers créés en Phase 2
- Commentaires dans le code

Bonne chance! 🚀
