# Prompt Expert pour Cursor/Claude Code/GitHub Copilot

**Copier-coller ce prompt complet dans votre IDE pour implémenter les nouvelles fonctionnalités de manière sécurisée.**

---

## 🎯 CONTEXTE DU PROJET

Tu travailles sur **Sirius Solar**, une plateforme Symfony 6.4 de gestion de réservations pour service de nettoyage de panneaux solaires en Suisse.

**Architecture existante:**
- 8 entités Doctrine (User, Customer, Reservation, Intervention, ReservationHistory, Notification)
- 3 services métier (ReservationService, NotificationService, EmailService)
- 7 contrôleurs (Admin + Public)
- Tests nécessaires sur le site actuel: NO breaking changes

**Technologies:**
- Backend: Symfony 6.4, Doctrine ORM, PHP 8.1
- Frontend: Twig, HTML5, CSS3, Vanilla JavaScript
- Database: MySQL/MariaDB
- Security: Symfony Security avec ROLE_ADMIN

---

## 📋 TÂCHE: Implémenter Calendrier + Workflow + Boutons Avancés

### Phase 1: CALENDRIER INTERACTIF (Étape 1/4)

#### 1.1 - Créer la nouvelle entité AvailableSlot

**Fichier: `src/Entity/AvailableSlot.php`**

Créer une entité Doctrine pour les créneaux disponibles avec:
- `id` (PK auto-increment)
- `dateCreneau` (DateTime, unique)
- `heureCreneau` (Time)
- `nombreMaxReservations` (int, default 5)
- `nombreReservationsActuelles` (int, default 0)
- `isActive` (boolean, default true)
- `createdAt` (DateTimeImmutable)
- `updatedAt` (DateTime)

Ajouter:
- Relations: One-to-Many vers Reservation
- Lifecycle callbacks: `@ORM\PrePersist`, `@ORM\PreUpdate`
- Validation: minDate = today + 1 day

**Contraintes:**
- ✅ Ne pas modifier les entités existantes
- ✅ Respecter les naming conventions Symfony
- ✅ Ajouter des getters/setters
- ✅ Ajouter les annotations @ORM correctement

#### 1.2 - Créer le repository AvailableSlotRepository

**Fichier: `src/Repository/AvailableSlotRepository.php`**

Ajouter les méthodes de query:
- `findAvailableByDate(DateTime $date)` - Créneaux dispo pour une date
- `findAvailableByMonth(DateTime $month)` - Tous créneaux du mois
- `findByDateRange(DateTime $start, DateTime $end)` - Plage de dates
- `countAvailableSlots()` - Nombre total de créneaux disponibles
- `findExpiredSlots()` - Créneaux dans le passé

**Contraintes:**
- ✅ Utiliser QueryBuilder
- ✅ Ajouter des conditions WHERE appropriées
- ✅ Retourner arrays ou null selon le cas

#### 1.3 - Créer la migration

**Commande à exécuter:**
```bash
php bin/console make:migration
# Vérifier que la migration inclut la table available_slot
# Vérifier que la clé étrangère vers reservation est correcte
```

**Contraintes:**
- ✅ Ne PAS modifier les migrations existantes
- ✅ Générer une nouvelle migration uniquement
- ✅ Vérifier la création des index (datetime)

---

### Phase 2: CONTRÔLEUR CALENDRIER (Étape 2/4)

#### 2.1 - Créer AdminCalendarController

**Fichier: `src/Controller/Admin/AdminCalendarController.php`**

Routes à créer:
- `GET /admin/calendar` - Vue du calendrier mensuel
  - Passer: `month`, `year`, `availableSlots` (array)
  - Template: `admin/calendar/index.html.twig`

- `GET /admin/calendar/api/month` - API pour récupérer les créneaux
  - Paramètres: `month` (int), `year` (int)
  - Retour: JSON avec créneaux et stats

- `POST /admin/calendar/slots` - Créer des créneaux
  - Paramètres: `dateStart`, `dateEnd`, `heure`, `nombreMax`
  - Validation: dates futures seulement
  - Retour: JSON avec succès/erreur

- `DELETE /admin/calendar/slots/{id}` - Supprimer un créneau
  - Vérifier: aucune réservation sur ce créneau
  - Retour: JSON succès/erreur

#### 2.2 - Contraintes importantes

- ✅ Protéger TOUS les routes avec `#[IsGranted('ROLE_ADMIN')]`
- ✅ Vérifier que le créneau n'est pas dans le passé
- ✅ Vérifier les conflits de dates
- ✅ Logger toutes les actions (create/delete)
- ✅ Créer des notifications lors de création de créneaux
- ✅ Retourner des JSON avec format cohérent: `{success, message, data}`

---

### Phase 3: TEMPLATES ET FRONTEND (Étape 3/4)

#### 3.1 - Template Calendrier (`templates/admin/calendar/index.html.twig`)

Créer avec:
- **Vue calendrier:** Grid CSS 7 colonnes (lundi-dimanche)
- **Affichage des jours:**
  - Gris: jours du mois précédent/suivant
  - Blanc: jours du mois actuel SANS créneaux
  - Bleu: jours du mois actuel AVEC créneaux disponibles
  - Rouge: jours SANS places disponibles
  - Orange: jours du passé (disabled)

- **Interaction:**
  - Cliquer sur un jour → Voir les créneaux
  - Popup avec liste des créneaux: `08:00 (3/5)`, `10:00 (5/5)`, etc.
  - Boutons: `+ Ajouter créneau`, `- Supprimer`, `Éditer`

- **Formulaire de création:**
  - Inputs: `dateStart`, `dateEnd`, `heure`, `nombreMax`
  - Dropdown: Répétition (chaque jour, lun-ven, custom)
  - Preview: "Créer 20 créneaux du 1/1 au 20/1 à 09:00"
  - Bouton: `Créer les créneaux`

- **Navigation:**
  - Flèches: mois précédent/suivant
  - Dropdown: sélectionner mois/année
  - Bouton "Aujourd'hui"

#### 3.2 - Design et UX

- ✅ Responsive (mobile/tablet/desktop)
- ✅ Couleurs cohérentes avec thème existant (bleu #667eea, vert #28a745)
- ✅ Icônes: 📅 (calendar), ➕ (add), ❌ (delete), ✏️ (edit)
- ✅ Loading states: spinner pendant les appels API
- ✅ Messages flash: succès/erreur des actions

#### 3.3 - JavaScript pour le Calendrier

Créer avec:
- Fetch API pour appels au backend
- Gestion des clics sur les jours
- Affichage/masquage des popups
- Validation des dates (futures seulement)
- Affichage des erreurs
- Refresh du calendrier après création/suppression

---

### Phase 4: SERVICE WORKFLOW (Étape 4/4)

#### 4.1 - Étendre ReservationService pour le workflow avancé

**Fichier: `src/Service/ReservationService.php`**

Ajouter les méthodes:
- `assignSlot(Reservation $res, AvailableSlot $slot)` - Assigner un créneau
  - Vérifier: slot disponible (nombreReservationsActuelles < nombreMaxReservations)
  - Incrémenter: nombreReservationsActuelles
  - Créer: entrée historique "SLOT_ASSIGNED"
  - Créer: notification "Créneau assigné"

- `canTransition(Reservation $res, string $newStatut)` - Valider transition
  - Retour: boolean
  - Vérifier: transitions valides selon le workflow
  - Exemple: EN_ATTENTE → CONFIRMEE ✅, REALISEE → EN_ATTENTE ❌

- `getAvailableActions(Reservation $res)` - Lister les actions possibles
  - Retour: array de string ['confirm', 'refuse', 'reschedule', ...]
  - Exemple: EN_ATTENTE → ['confirm', 'refuse']

#### 4.2 - Boutons Workflow Contextuels

Modifier `templates/admin/reservations/show.html.twig`:

**Remplacer** les boutons statiques par:
```twig
{% for action in availableActions %}
  <button class="btn-{{ action }}">{{ getActionLabel(action) }}</button>
{% endfor %}
```

**Actions et labels:**
- `confirm` → ✅ Confirmer
- `refuse` → ❌ Refuser
- `reschedule` → 📅 Reporter
- `cancel` → 🚫 Annuler
- `complete` → ✨ Marquer réalisée
- `assignSlot` → 📍 Assigner créneau (nouveau)
- `releaseSlot` → 🗑️ Libérer créneau (nouveau)

#### 4.3 - Validation et Sécurité

- ✅ Vérifier les transitions côté serveur (pas uniquement côté client)
- ✅ Vérifier les permissions (ROLE_ADMIN)
- ✅ Vérifier l'état de la réservation avant action
- ✅ Logguer les erreurs de transition invalides
- ✅ Ne JAMAIS faire confiance aux données du client

---

## 🚨 RÈGLES ABSOLUES À RESPECTER

### 1. Ne Pas Casser le Site Actuel
- ✅ Aucune modification des entités existantes (sauf AvailableSlot nouvelle)
- ✅ Les contrôleurs existants continuent à fonctionner
- ✅ Les templates existants ne sont modifiés que si nécessaire
- ✅ Les tests existants passent tous

### 2. Architecture et Patterns
- ✅ Respecter le pattern Repository
- ✅ Utiliser les Services pour la business logic
- ✅ Valider au niveau du Service, pas du Controller
- ✅ Utiliser Doctrine Lifecycle callbacks si pertinent

### 3. Sécurité
- ✅ CSRF tokens sur tous les formulaires
- ✅ Vérifier `#[IsGranted('ROLE_ADMIN')]` sur toutes les routes admin
- ✅ Sanitizer les inputs utilisateur
- ✅ Utiliser prepared statements (Doctrine le fait automatiquement)
- ✅ Logger les actions sensibles

### 4. Base de Données
- ✅ Créer une migration via `make:migration`
- ✅ Ne PAS modifier les migrations existantes
- ✅ Ajouter les index sur les colonnes fréquemment cherchées
- ✅ Respecter les relations Doctrine

### 5. Frontend
- ✅ Design responsive
- ✅ Accessibilité (labels, alt text, semantic HTML)
- ✅ Validation côté client ET serveur
- ✅ Messages d'erreur clairs
- ✅ Feedback utilisateur (loading states, confirmations)

### 6. Code Quality
- ✅ Suivre PSR-12 (code style PHP)
- ✅ Nommer les variables de manière explicite
- ✅ Ajouter des docblocks aux méthodes publiques
- ✅ Pas de `var_dump()` ou `console.log()` en production
- ✅ Utiliser les constants pour les statuts

---

## 📋 CHECKLIST D'IMPLÉMENTATION

### Entités et Migrations
- [ ] AvailableSlot créée avec toutes les propriétés
- [ ] AvailableSlotRepository créé avec 5 méthodes
- [ ] Migration générée via `make:migration`
- [ ] Migration exécutée sans erreur

### Contrôleurs
- [ ] AdminCalendarController créé avec 4 routes
- [ ] CSRF tokens sur les formulaires POST
- [ ] Vérifications `#[IsGranted('ROLE_ADMIN')]`
- [ ] Retours JSON avec format cohérent
- [ ] Gestion des erreurs (404, 400, 500)

### Templates
- [ ] `admin/calendar/index.html.twig` créé
- [ ] Calendrier responsive
- [ ] Formulaire de création des créneaux
- [ ] Popups avec liste des créneaux
- [ ] Icônes et couleurs cohérentes

### JavaScript/AJAX
- [ ] Fetch API pour appels au backend
- [ ] Gestion des erreurs réseau
- [ ] Loading states
- [ ] Validation côté client
- [ ] Refresh du calendrier après actions

### Services
- [ ] ReservationService enrichi avec 3 méthodes
- [ ] `assignSlot()` avec validation
- [ ] `canTransition()` pour workflow
- [ ] `getAvailableActions()` pour UI dynamique

### Workflow
- [ ] Boutons contextuels dans `admin/reservations/show.html.twig`
- [ ] Actions possibles selon le statut
- [ ] Validation des transitions côté serveur
- [ ] Notifications créées pour chaque transition
- [ ] Historique enregistré

### Tests
- [ ] Accéder à `/admin/calendar` → page charge
- [ ] Créer des créneaux → enregistrés en DB
- [ ] Créer réservation avec slot → fonctionne
- [ ] Boutons contextuels affichés correctement
- [ ] Transitions invalides → erreur
- [ ] Site existant toujours fonctionnel

---

## 🎬 COMMANDES À EXÉCUTER APRÈS IMPLÉMENTATION

```bash
# 1. Générer la migration
php bin/console make:migration

# 2. Vérifier la migration (optionnel)
php bin/console doctrine:migrations:list

# 3. Exécuter la migration
php bin/console doctrine:migrations:migrate

# 4. Vider le cache
php bin/console cache:clear

# 5. Vérifier les routes
php bin/console debug:router | grep calendar

# 6. Tests (quand créés)
php bin/phpunit
```

---

## 💡 CONSEILS D'IMPLÉMENTATION

1. **Commencer par l'entité AvailableSlot**
   - Définir les propriétés d'abord
   - Générer les getters/setters
   - Créer le repository

2. **Puis le contrôleur**
   - Commencer par la route GET (affichage)
   - Puis les routes POST/DELETE (actions)
   - Tester chaque route individuellement

3. **Ensuite le template**
   - Structure HTML d'abord
   - CSS et styling
   - JavaScript pour l'interaction

4. **Puis le service**
   - Implémenter les méthodes une par une
   - Tester avec des données de test
   - Intégrer avec les contrôleurs

5. **Enfin les tests**
   - Tests unitaires pour les services
   - Tests d'intégration pour les contrôleurs
   - Tests E2E pour les workflows

---

## 📞 SUPPORT ET QUESTIONS

Si tu as besoin de:
- **Rappels sur Symfony:** Consulte la documentation officielle
- **Patterns Doctrine:** Regarde les repositories existants
- **Design:** Consulte le fichier `templates/admin/base.html.twig`
- **Validation:** Regarde `ReservationService::validateReservation()`

---

## 🎯 OBJECTIF FINAL

À la fin de cette implémentation:
- ✅ Calendrier interactif pour gérer les créneaux
- ✅ Workflow avancé avec actions contextuelles
- ✅ Boutons dynamiques selon le statut
- ✅ Assignation de créneaux aux réservations
- ✅ Site actuel 100% fonctionnel
- ✅ Zéro breaking changes

**Bon développement! 🚀**
