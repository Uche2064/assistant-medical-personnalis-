# PLAN D'ACTION DÉTAILLÉ - SUNU SANTÉ (3 SEMAINES)

## ANALYSE DE L'EXISTANT

### ✅ CE QUI EST DÉJÀ IMPLÉMENTÉ

1. **Structure de base solide**
   - Modèles User, Client, Assure, Contrat, Facture, etc.
   - Système d'authentification JWT + Sanctum
   - Gestion des rôles avec Spatie Permission
   - Système de questions/réponses dynamiques
   - Workflow de validation des factures
   - Système de parrainage pour commerciaux
   - Notifications par email (NotificationService)
   - Broadcasting avec Pusher configuré

2. **Fonctionnalités avancées**
   - Gestion des invitations employés (InvitationEmployes)
   - Système de soumission employés (SoumissionEmployeController)
   - Workflow de validation des demandes d'adhésion
   - Gestion des prestataires et réseaux
   - Système de sinistres

### 🔧 CE QUI MANQUE OU NÉCESSITE DES AMÉLIORATIONS

1. **Gestion des entreprises**
   - Modèle Entreprise manquant
   - Relations entre entreprises et employés à clarifier
   - Workflow d'adhésion entreprise incomplet

2. **Système de notifications temps réel**
   - Notifications in-app manquantes
   - WebSocket pour notifications temps réel

3. **Gestion des bénéficiaires**
   - Interface de gestion des bénéficiaires
   - Workflow d'ajout/suppression

4. **Dashboard par rôle**
   - Interfaces spécifiques par rôle
   - Statistiques et rapports

5. **API endpoints manquants**
   - Endpoints pour tous les rôles
   - Gestion des contrats
   - Gestion des prestataires

## PLAN D'IMPLÉMENTATION (3 SEMAINES)

### SEMAINE 1 : BACKEND - CORE BUSINESS LOGIC

#### Jour 1-2 : Finalisation des modèles et migrations

**Tâches :**
1. **Créer le modèle Entreprise**
   ```php
   // app/Models/Entreprise.php
   class Entreprise extends Model
   {
       protected $fillable = [
           'user_id', 'raison_sociale', 'siret', 'adresse_siege',
           'nombre_employes', 'statut', 'lien_adhesion'
       ];
       
       public function employes() {
           return $this->hasMany(Personnes::class, 'entreprise_id');
       }
       
       public function contrats() {
           return $this->hasMany(Contrat::class);
       }
   }
   ```

2. **Migration pour table entreprises**
   ```php
   // database/migrations/2025_01_XX_create_entreprises_table.php
   Schema::create('entreprises', function (Blueprint $table) {
       $table->id();
       $table->foreignId('user_id')->constrained('users');
       $table->string('raison_sociale');
       $table->string('siret')->unique();
       $table->text('adresse_siege');
       $table->integer('nombre_employes');
       $table->enum('statut', ['active', 'inactive']);
       $table->string('lien_adhesion')->unique();
       $table->timestamps();
       $table->softDeletes();
   });
   ```

3. **Améliorer le modèle Personnes**
   - Clarifier les relations entre employés et entreprises
   - Ajouter les relations manquantes

#### Jour 3-4 : Système de notifications temps réel

**Tâches :**
1. **Créer le modèle Notification**
   ```php
   // app/Models/Notification.php
   class Notification extends Model
   {
       protected $fillable = [
           'user_id', 'type', 'titre', 'message', 'data', 'lu'
       ];
       
       protected $casts = [
           'data' => 'array',
           'lu' => 'boolean'
       ];
   }
   ```

2. **Créer les événements de notification**
   ```php
   // app/Events/DemandeAdhesionSoumise.php
   // app/Events/FactureValidee.php
   // app/Events/ContratAccepte.php
   ```

3. **Améliorer NotificationService**
   - Ajouter notifications in-app
   - Intégrer WebSocket

#### Jour 5-7 : Workflow d'adhésion entreprise

**Tâches :**
1. **Créer EntrepriseController**
   ```php
   // app/Http/Controllers/v1/Api/EntrepriseController.php
   class EntrepriseController extends Controller
   {
       public function genererLienAdhesion()
       public function soumettreDemande()
       public function suivreEmployes()
       public function accepterContrat()
   }
   ```

2. **Améliorer SoumissionEmployeController**
   - Gestion des notifications
   - Validation des données
   - Intégration avec le workflow

3. **Créer les routes API**
   ```php
   // routes/api.php
   Route::prefix('v1/entreprise')->group(function () {
       Route::post('generer-lien', [EntrepriseController::class, 'genererLienAdhesion']);
       Route::post('soumettre-demande', [EntrepriseController::class, 'soumettreDemande']);
       Route::get('employes', [EntrepriseController::class, 'suivreEmployes']);
   });
   ```

### SEMAINE 2 : BACKEND - WORKFLOWS & API ENDPOINTS

#### Jour 1-3 : API Endpoints par rôle

**Tâches :**
1. **AssuréController**
   ```php
   // app/Http/Controllers/v1/Api/AssureController.php
   class AssureController extends Controller
   {
       public function dashboard()
       public function beneficiaires()
       public function ajouterBeneficiaire()
       public function supprimerBeneficiaire()
       public function centresSoins()
       public function historiqueRemoursements()
   }
   ```

2. **CommercialController**
   ```php
   // app/Http/Controllers/v1/Api/CommercialController.php
   class CommercialController extends Controller
   {
       public function dashboard()
       public function prospects()
       public function genererCodeParrainage()
       public function statistiques()
       public function commissions()
   }
   ```

3. **TechnicienController**
   ```php
   // app/Http/Controllers/v1/Api/TechnicienController.php
   class TechnicienController extends Controller
   {
       public function dashboard()
       public function demandesAdhesion()
       public function validerDemande()
       public function proposerContrat()
       public function factures()
   }
   ```

4. **MedecinControleurController**
   ```php
   // app/Http/Controllers/v1/Api/MedecinControleurController.php
   class MedecinControleurController extends Controller
   {
       public function dashboard()
       public function prestataires()
       public function validerPrestataire()
       public function questions()
       public function factures()
   }
   ```

5. **ComptableController**
   ```php
   // app/Http/Controllers/v1/Api/ComptableController.php
   class ComptableController extends Controller
   {
       public function dashboard()
       public function factures()
       public function validerRemboursement()
       public function rapports()
   }
   ```

#### Jour 4-5 : Gestion des contrats et prestataires

**Tâches :**
1. **ContratController**
   ```php
   // app/Http/Controllers/v1/Api/ContratController.php
   class ContratController extends Controller
   {
       public function accepter()
       public function modifier()
       public function signer()
       public function telecharger()
   }
   ```

2. **PrestataireController**
   ```php
   // app/Http/Controllers/v1/Api/PrestataireController.php
   class PrestataireController extends Controller
   {
       public function dashboard()
       public function assuresAssignes()
       public function genererFacture()
       public function historiqueFactures()
   }
   ```

#### Jour 6-7 : Système de sinistres et facturation

**Tâches :**
1. **SinistreController**
   ```php
   // app/Http/Controllers/v1/Api/SinistreController.php
   class SinistreController extends Controller
   {
       public function declarer()
       public function suivre()
       public function valider()
   }
   ```

2. **Améliorer FactureController**
   - Workflow complet de validation
   - Notifications automatiques
   - Calculs automatiques

### SEMAINE 3 : FRONTEND & TESTS

#### Jour 1-3 : Interfaces utilisateur par rôle

**Tâches :**
1. **Créer la structure Vue.js**
   ```bash
   # Structure des composants
   src/
   ├── components/
   │   ├── common/
   │   │   ├── Navigation.vue
   │   │   ├── Dashboard.vue
   │   │   ├── DataTable.vue
   │   │   └── NotificationToast.vue
   │   ├── assure/
   │   ├── entreprise/
   │   ├── commercial/
   │   ├── technicien/
   │   ├── medecin/
   │   ├── comptable/
   │   ├── gestionnaire/
   │   ├── admin/
   │   └── prestataire/
   ├── views/
   ├── stores/
   └── router/
   ```

2. **Dashboard par rôle**
   - Statistiques personnalisées
   - Actions rapides
   - Notifications temps réel

3. **Formulaires dynamiques**
   - Gestion des questionnaires
   - Validation en temps réel
   - Upload de fichiers

#### Jour 4-5 : Tests et documentation

**Tâches :**
1. **Tests d'intégration**
   ```php
   // tests/Feature/
   ├── AdhesionTest.php
   ├── ContratTest.php
   ├── FactureTest.php
   └── NotificationTest.php
   ```

2. **Documentation API**
   - Swagger/OpenAPI
   - Guide d'utilisation
   - Exemples de requêtes

#### Jour 6-7 : Déploiement et optimisation

**Tâches :**
1. **Optimisation des performances**
   - Cache Redis
   - Optimisation des requêtes
   - Compression des assets

2. **Sécurité**
   - Validation des données
   - Protection CSRF
   - Rate limiting

3. **Déploiement**
   - Configuration production
   - Monitoring
   - Backup

## FICHIERS À CRÉER/MODIFIER

### Migrations à créer :
1. `2025_01_XX_create_entreprises_table.php`
2. `2025_01_XX_create_notifications_table.php`
3. `2025_01_XX_add_entreprise_id_to_personnes_table.php`
4. `2025_01_XX_create_beneficiaires_table.php`

### Modèles à créer :
1. `app/Models/Entreprise.php`
2. `app/Models/Notification.php`
3. `app/Models/Beneficiaire.php`

### Contrôleurs à créer :
1. `app/Http/Controllers/v1/Api/EntrepriseController.php`
2. `app/Http/Controllers/v1/Api/AssureController.php`
3. `app/Http/Controllers/v1/Api/CommercialController.php`
4. `app/Http/Controllers/v1/Api/TechnicienController.php`
5. `app/Http/Controllers/v1/Api/MedecinControleurController.php`
6. `app/Http/Controllers/v1/Api/ComptableController.php`
7. `app/Http/Controllers/v1/Api/ContratController.php`
8. `app/Http/Controllers/v1/Api/PrestataireController.php`
9. `app/Http/Controllers/v1/Api/SinistreController.php`

### Services à améliorer :
1. `app/Services/NotificationService.php`
2. `app/Services/ContratService.php`
3. `app/Services/FactureService.php`

### Événements à créer :
1. `app/Events/DemandeAdhesionSoumise.php`
2. `app/Events/FactureValidee.php`
3. `app/Events/ContratAccepte.php`
4. `app/Events/NotificationEnvoyee.php`

## PROCHAINES ÉTAPES IMMÉDIATES

1. **Commencer par les migrations** - Créer les tables manquantes
2. **Implémenter le modèle Entreprise** - Base pour le workflow entreprise
3. **Créer les contrôleurs de base** - API endpoints essentiels
4. **Tester les workflows** - Validation des processus métier

Voulez-vous que je commence par créer les migrations et modèles manquants ? 