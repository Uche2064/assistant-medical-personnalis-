# AMP Backend - Diagramme de Classe UML

Ce document présente le diagramme de classe UML complet de l'application AMP Backend, incluant tous les modèles Laravel, leurs attributs, méthodes et relations.

## **Intégration Spatie Laravel Permission**

L'application utilise le package **Spatie Laravel Permission** pour la gestion des rôles et permissions. Voici comment cela s'intègre dans l'architecture :

### **Modèles Spatie Ajoutés**

#### **Role**
```php
class Role extends \Spatie\Permission\Models\Role
{
    // Attributs
    - id: bigint
    - name: string
    - guard_name: string
    - created_at: datetime
    - updated_at: datetime
    
    // Méthodes principales
    + hasPermissionTo($permission)
    + givePermissionTo($permission)
    + revokePermissionTo($permission)
    + syncPermissions($permissions)
}
```

#### **Permission**
```php
class Permission extends \Spatie\Permission\Models\Permission
{
    // Attributs
    - id: bigint
    - name: string
    - guard_name: string
    - created_at: datetime
    - updated_at: datetime
    
    // Méthodes principales
    + assignRole($role)
    + removeRole($role)
    + syncRoles($roles)
    + hasRole($role)
}
```

#### **Tables de Liaison (Pivot)**

**ModelHasRoles**
```php
// Table pivot pour User ↔ Role (N:N)
- role_id: bigint
- model_type: string
- model_id: bigint
```

**ModelHasPermissions**
```php
// Table pivot pour User ↔ Permission (N:N)
- permission_id: bigint
- model_type: string
- model_id: bigint
```

**RoleHasPermissions**
```php
// Table pivot pour Role ↔ Permission (N:N)
- permission_id: bigint
- role_id: bigint
```

### **Rôles Définis dans l'Application**

D'après `RoleEnum.php`, les rôles suivants sont définis :

#### **Rôles Internes (Personnel SUNU Santé)**
- `admin_global` - Super administrateur
- `gestionnaire` - Administrateur RH
- `technicien` - Analyste technique
- `medecin_controleur` - Contrôle médical
- `commercial` - Prospecteur
- `comptable` - Gestionnaire financier

#### **Rôles Externes (Clients/Partners)**
- `physique` - Client personne physique
- `entreprise` - Client moral
- `prestataire` - Centre de soins

### **Intégration dans le Modèle User**

Le modèle `User` utilise le trait `HasRoles` de Spatie :

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    
    // Méthodes disponibles automatiquement :
    // + hasRole($role)
    // + hasAnyRole($roles)
    // + hasAllRoles($roles)
    // + assignRole($role)
    // + removeRole($role)
    // + syncRoles($roles)
    // + hasPermissionTo($permission)
    // + hasAnyPermission($permissions)
    // + hasAllPermissions($permissions)
    // + givePermissionTo($permission)
    // + revokePermissionTo($permission)
    // + syncPermissions($permissions)
}
```

### **Relations dans le Diagramme UML**

1. **User ↔ Role** (N:N via ModelHasRoles)
2. **User ↔ Permission** (N:N via ModelHasPermissions)
3. **Role ↔ Permission** (N:N via RoleHasPermissions)

### **Middleware et Contrôle d'Accès**

L'application utilise plusieurs middlewares pour le contrôle d'accès :

- `CheckRole` - Middleware personnalisé
- `RoleMiddleware` - Middleware Spatie
- Middlewares spécifiques : `AdminMiddleware`, `GestionnaireMiddleware`, etc.

### **Utilisation dans les Contrôleurs**

```php
// Vérifier un rôle
if ($user->hasRole('admin_global')) {
    // Accès autorisé
}

// Vérifier une permission
if ($user->hasPermissionTo('edit-users')) {
    // Accès autorisé
}

// Assigner un rôle
$user->assignRole('gestionnaire');

// Récupérer tous les rôles
$roles = $user->roles->pluck('name');
```

## **Exemples d'Utilisation Pratique**

### **1. Création et Attribution de Rôles**

```php
// Dans un seeder ou service
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Créer des rôles
$adminRole = Role::create(['name' => 'admin_global']);
$gestionnaireRole = Role::create(['name' => 'gestionnaire']);

// Créer des permissions
$editUsersPermission = Permission::create(['name' => 'edit-users']);
$viewReportsPermission = Permission::create(['name' => 'view-reports']);

// Attribuer des permissions aux rôles
$adminRole->givePermissionTo([$editUsersPermission, $viewReportsPermission]);
$gestionnaireRole->givePermissionTo($viewReportsPermission);

// Attribuer un rôle à un utilisateur
$user->assignRole('admin_global');
```

### **2. Vérification des Rôles et Permissions**

```php
// Dans un contrôleur ou middleware
public function dashboard()
{
    $user = auth()->user();
    
    if ($user->hasRole('admin_global')) {
        return view('admin.dashboard');
    }
    
    if ($user->hasRole('gestionnaire')) {
        return view('gestionnaire.dashboard');
    }
    
    if ($user->hasAnyRole(['technicien', 'medecin_controleur'])) {
        return view('staff.dashboard');
    }
    
    // Vérifier des permissions spécifiques
    if ($user->hasPermissionTo('edit-users')) {
        // Afficher le bouton d'édition
    }
}
```

### **3. Middleware de Contrôle d'Accès**

```php
// Dans routes/api.php
Route::middleware(['auth:api', 'role:admin_global'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'index']);
});

Route::middleware(['auth:api', 'permission:edit-users'])->group(function () {
    Route::put('/users/{user}', [UserController::class, 'update']);
});
```

### **4. Gestion Dynamique des Rôles**

```php
// Dans un service d'administration
class RoleService
{
    public function assignRoleToUser($userId, $roleName)
    {
        $user = User::findOrFail($userId);
        $user->assignRole($roleName);
        
        return $user->fresh()->load('roles');
    }
    
    public function removeRoleFromUser($userId, $roleName)
    {
        $user = User::findOrFail($userId);
        $user->removeRole($roleName);
        
        return $user->fresh()->load('roles');
    }
    
    public function syncUserRoles($userId, array $roles)
    {
        $user = User::findOrFail($userId);
        $user->syncRoles($roles);
        
        return $user->fresh()->load('roles');
    }
}
```

### **5. Tests avec Spatie**

```php
// Dans un test
public function test_user_can_have_multiple_roles()
{
    $user = User::factory()->create();
    
    $user->assignRole(['admin_global', 'gestionnaire']);
    
    $this->assertTrue($user->hasRole('admin_global'));
    $this->assertTrue($user->hasRole('gestionnaire'));
    $this->assertTrue($user->hasAnyRole(['admin_global', 'technicien']));
}

public function test_role_has_permissions()
{
    $role = Role::create(['name' => 'test_role']);
    $permission = Permission::create(['name' => 'test_permission']);
    
    $role->givePermissionTo($permission);
    
    $this->assertTrue($role->hasPermissionTo('test_permission'));
}
```

## **Diagramme de Classe Complet**

### Classes principales

#### 1. User (Utilisateur)
```
+------------------+
|      User        |
+------------------+
| - id: bigint     |
| - email: string  |
| - contact: string|
| - password: string|
| - adresse: string|
| - photo: string  |
| - est_actif: bool|
| - email_verified_at: datetime|
| - mot_de_passe_a_changer: bool|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getJWTIdentifier()|
| + getJWTCustomClaims()|
| + getFullNameAttribute()|
| + getUserTypeAttribute()|
| + genererMotDePasse()|
+------------------+
```

#### 2. Personnel
```
+------------------+
|    Personnel     |
+------------------+
| - id: bigint     |
| - nom: string    |
| - prenoms: string|
| - sexe: enum     |
| - date_naissance: date|
| - code_parainage: string|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getFullNameAttribute()|
| + isGestionnaire()|
| + isCommercial() |
| + isTechnicien() |
| + isMedecinControleur()|
| + isComptable()  |
| + genererCodeParainage()|
+------------------+
```

#### 3. Entreprise
```
+------------------+
|    Entreprise    |
+------------------+
| - id: bigint     |
| - raison_sociale: string|
| - statut: enum   |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isActive()     |
| + isInactive()   |
| + generateAdhesionLink()|
| + getNameAttribute()|
| + getActiveEmployeesCountAttribute()|
+------------------+
```

#### 4. Assure
```
+------------------+
|      Assure      |
+------------------+
| - id: bigint     |
| - email: string  |
| - nom: string    |
| - prenoms: string|
| - date_naissance: date|
| - sexe: enum     |
| - lien_parente: enum|
| - est_principal: bool|
| - profession: string|
| - contact: string|
| - photo: string  |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPrincipal()  |
| + isBeneficiaire()|
| + isActive()     |
| + isInactive()   |
| + isSuspended()  |
| + getFullNameAttribute()|
| + getTypeAttribute()|
| + getSourceAttribute()|
| + hasContratActif()|
| + getContratAssocie()|
+------------------+
```

#### 5. Contrat
```
+------------------+
|     Contrat      |
+------------------+
| - id: bigint     |
| - libelle: string|
| - prime_standard: decimal|
| - frais_gestion: decimal|
| - couverture_moyenne: decimal|
| - couverture: decimal|
| - categories_garanties_standard: array|
| - est_actif: bool|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + generateNumeroPolice()|
| + isProposed()   |
| + isAccepted()   |
| + isRefused()    |
| + isActive()     |
| + isExpired()    |
| + isCancelled()  |
| + accept()       |
| + refuse()       |
| + activate()     |
| + getPrimeTotaleAttribute()|
| + getCommissionAmountAttribute()|
| + isValid()      |
+------------------+
```

#### 6. DemandeAdhesion
```
+------------------+
| DemandeAdhesion  |
+------------------+
| - id: bigint     |
| - type_demandeur: enum|
| - statut: enum   |
| - motif_rejet: text|
| - code_parainage: string|
| - valider_a: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPending()    |
| + isValidated()  |
| + isRejected()   |
| + validate()     |
| + reject()       |
| + getTypeDemandeurFrancaisAttribute()|
+------------------+
```

#### 7. Prestataire
```
+------------------+
|   Prestataire    |
+------------------+
| - id: bigint     |
| - type_prestataire: enum|
| - raison_sociale: string|
| - documents_requis: array|
| - code_parrainage: string|
| - statut: enum   |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPending()    |
| + isValidated()  |
| + isRejected()   |
| + isSuspended()  |
| + validate()     |
| + reject()       |
| + suspend()      |
| + getNameAttribute()|
| + getTypeFrancaisAttribute()|
+------------------+
```

#### 8. Facture
```
+------------------+
|     Facture      |
+------------------+
| - id: bigint     |
| - numero_facture: string|
| - montant_reclame: decimal|
| - montant_a_rembourser: decimal|
| - diagnostic: text|
| - ticket_moderateur: decimal|
| - statut: enum   |
| - motif_rejet: text|
| - est_valide_par_technicien: bool|
| - valide_par_technicien_a: datetime|
| - est_valide_par_medecin: bool|
| - valide_par_medecin_a: datetime|
| - est_autorise_par_comptable: bool|
| - autorise_par_comptable_a: datetime|
| - motif_rejet_technicien: text|
| - rejet_par_technicien_a: datetime|
| - motif_rejet_medecin: text|
| - rejet_par_medecin_a: datetime|
| - motif_rejet_comptable: text|
| - rejet_par_comptable_a: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isPending()    |
| + isValidatedByTechnicien()|
| + isValidatedByMedecin()|
| + isAuthorizedByComptable()|
| + isReimbursed() |
| + isRejectedByTechnicien()|
| + isRejectedByMedecin()|
| + isRejectedByComptable()|
| + isRejected()   |
| + canBeModified()|
| + resetToPending()|
| + validateByTechnicien()|
| + validateByMedecin()|
| + authorizeByComptable()|
| + rejectByTechnicien()|
| + rejectByMedecin()|
| + rejectByComptable()|
| + reject()       |
| + markAsReimbursed()|
| + getStatutFrancaisAttribute()|
| + getDifferenceAttribute()|
+------------------+
```

#### 9. CategorieGarantie
```
+------------------+
| CategorieGarantie|
+------------------+
| - id: bigint     |
| - libelle: string|
| - description: text|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isActive()     |
| + getTotalCoverageAttribute()|
+------------------+
```

#### 10. Garantie
```
+------------------+
|     Garantie     |
+------------------+
| - id: bigint     |
| - libelle: string|
| - plafond: decimal|
| - prix_standard: decimal|
| - taux_couverture: decimal|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getCoverageAmountAttribute()|
|
+------------------+
```

#### 11. Sinistre
```
+------------------+
|     Sinistre     |
+------------------+
| - id: bigint     |
| - description: text|
| - date_sinistre: date|
| - statut: enum   |
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isInProgress() |
| + isClosed()     |
| + updateStatus() |
| + getTotalAmountClaimedAttribute()|
| + getTotalAmountToReimburseAttribute()|
+------------------+
```

#### 12. Question
```
+------------------+
|     Question     |
+------------------+
| - id: bigint     |
| - libelle: string|
| - type_donnee: enum|
| - options: array |
| - destinataire: enum|
| - obligatoire: bool|
| - est_actif: bool|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + scopeActive()  |
| + scopeByDestinataire()|
| + scopeRequired()|
| + isActive()     |
| + isRequired()   |
| + scopeForDestinataire()|
| + getTypeDonneeFrancaisAttribute()|
| + getDestinataireFrancaisAttribute()|
+------------------+
```

#### 13. ReponseQuestionnaire
```
+------------------+
|ReponseQuestionnaire|
+------------------+
| - id: bigint     |
| - personne_type: string|
| - personne_id: bigint|
| - reponse_text: text|
| - reponse_bool: bool|
| - reponse_decimal: decimal|
| - reponse_date: date|
| - reponse_fichier: string|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + getReponseValueAttribute()|
| + setReponseValueAttribute()|
+------------------+
```

#### 14. ClientContrat
```
+------------------+
|  ClientContrat   |
+------------------+
| - id: bigint     |
| - type_client: string|
| - date_debut: date|
| - date_fin: date |
| - statut: enum   |
| - numero_police: string|
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + genererNumeroPolice()|
| + isActif()      |
| + isExpire()     |
+------------------+
```

#### 15. PropositionContrat
```
+------------------+
| PropositionContrat|
+------------------+
| - id: bigint     |
| - commentaires_technicien: text|
| - statut: enum   |
| - date_acceptation: datetime|
| - date_refus: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isProposee()   |
| + getPrimeAttribute()|
| + getPrimeFormattedAttribute()|
| + getTauxCouvertureAttribute()|
| + getFraisGestionAttribute()|
| + getPrimeTotaleAttribute()|
| + getPrimeTotaleFormattedAttribute()|
| + isAcceptee()   |
| + isRefusee()    |
| + isExpiree()    |
| + accepter()     |
| + refuser()      |
| + expirer()      |
+------------------+
```

#### 18. Notification
```
+------------------+
|   Notification   |
+------------------+
| - id: bigint     |
| - type: string   |
| - titre: string  |
| - message: text  |
| - data: array    |
| - lu: bool       |
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + scopeUnread()  |
| + scopeRead()    |
| + scopeByType()  |
| + markAsRead()   |
| + markAsUnread() |
| + isRead()       |
| + isUnread()     |
+------------------+
```

#### 19. Otp
```
+------------------+
|       Otp        |
+------------------+
| - id: bigint     |
| - email: string  |
| - otp: string    |
| - expire_at: datetime|
| - verifier_a: datetime|
| - type: enum     |
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + isExpired()    |
| + isValid()      |
| + generateOtp()  |
| + verifyOtp()    |
| + cleanExpired() |
+------------------+
```

#### 20. InvitationEmploye
```
+------------------+
| InvitationEmploye|
+------------------+
| - id: bigint     |
| - token: string  |
| - expire_at: datetime|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + isExpired()    |
| + isValid()      |
| + generateToken()|
| + createInvitation()|
| + getInvitationUrlAttribute()|
+------------------+
```

#### 21. LigneFacture
```
+------------------+
|  LigneFacture    |
+------------------+
| - id: bigint     |
| - libelle_acte: string|
| - prix_unitaire: decimal|
| - quantite: integer|
| - prix_total: decimal|
| - taux_couverture: decimal|
| - montant_couvert: decimal|
| - ticket_moderateur: decimal|
| - created_at: datetime|
| - updated_at: datetime|
| - deleted_at: datetime|
+------------------+
| + calculateCoverage()|
+------------------+
```

#### 22. ClientPrestataire
```
+------------------+
| ClientPrestataire|
+------------------+
| - id: bigint     |
| - type_prestataire: string|
| - statut: string |
| - created_at: datetime|
| - updated_at: datetime|
+------------------+
| + isActif()      |
| + getTypePrestataireLabel()|
+------------------+
```

## Relations entre les classes

### Relations principales

1. **User** ↔ **Personnel** (1:1)
2. **User** ↔ **Entreprise** (1:1)
3. **User** ↔ **Assure** (1:1)
4. **User** ↔ **Prestataire** (1:1)
5. **User** ↔ **DemandeAdhesion** (1:N)
6. **User** ↔ **ClientContrat** (1:N)
7. **User** ↔ **Notification** (1:N)


### Relations métier

1. **Personnel** ↔ **Personnel** (1:N)
2. **Personnel** ↔ **Contrat** (1:N)
3. **Personnel** ↔ **CategorieGarantie** (1:N)
4. **Personnel** ↔ **Garantie** (1:N)
5. **Personnel** ↔ **Prestataire** (1:N)
6. **Personnel** ↔ **DemandeAdhesion** (1:N)
7. **Personnel** ↔ **Facture** (1:N)
8. **Personnel** ↔ **Question** (1:N)
9. **Personnel** ↔ **PropositionContrat** (1:N)

### Relations d'assurance

1. **Entreprise** ↔ **Assure** (1:N)
2. **Entreprise** ↔ **InvitationEmploye** (1:N)
3. **Assure** ↔ **Assure** (1:N)
4. **Assure** ↔ **Contrat** (N:1)
5. **Assure** ↔ **Sinistre** (1:N)
6. **Assure** ↔ **ReponseQuestionnaire** (1:N)

### Relations de contrats et garanties

1. **Contrat** ↔ **CategorieGarantie** (N:N)
2. **CategorieGarantie** ↔ **Garantie** (1:N)
3. **ClientContrat** ↔ **Contrat** (N:1)
4. **ClientContrat** ↔ **ClientPrestataire** (1:N)
5. **PropositionContrat** ↔ **DemandeAdhesion** (N:1)
6. **PropositionContrat** ↔ **Contrat** (N:1)
7. **PropositionContrat** ↔ **Garantie** (N:N)

### Relations de facturation

1. **Sinistre** ↔ **Facture** (1:N)
2. **Prestataire** ↔ **Facture** (1:N)
3. **Prestataire** ↔ **Sinistre** (1:N)
4. **Facture** ↔ **LigneFacture** (1:N)
5. **LigneFacture** ↔ **Garantie** (N:1)

### Relations de communication

2. **Question** ↔ **ReponseQuestionnaire** (1:N)

## Enums utilisés

- **StatutClientEnum**: active, inactive
- **LienParenteEnum**: conjoint, enfant, parent, autre
- **SexeEnum**: masculin, feminin
- **StatutContratEnum**: propose, accepte, refuse, actif, expire, resilie
- **StatutDemandeAdhesionEnum**: en_attente, validee, rejetee
- **TypeDemandeurEnum**: client, entreprise, prestataire
- **StatutPrestataireEnum**: en_attente, valide, rejete, suspendu
- **TypePrestataireEnum**: pharmacie, centre_soins, optique, laboratoire
- **StatutFactureEnum**: en_attente, validee_technicien, validee_medecin, autorisee_comptable, remboursee, rejetee
- **StatutSinistreEnum**: en_cours, cloture
- **TypeDonneeEnum**: text, number, boolean, date, file
- **OtpTypeEnum**: verification, reset_password
- **StatutPropositionContratEnum**: proposee, acceptee, refusee, expiree

## Notes importantes

1. **Soft Deletes**: La plupart des modèles utilisent SoftDeletes pour la suppression logique
2. **Polymorphic Relations**: ReponseQuestionnaire utilise des relations polymorphiques
3. **Pivot Tables**: Plusieurs relations many-to-many utilisent des tables pivot avec des attributs supplémentaires
4. **Enums**: Le système utilise des enums pour les statuts et types
5. **Timestamps**: Tous les modèles incluent created_at et updated_at
6. **JWT**: Le modèle User implémente JWTSubject pour l'authentification
7. **Roles**: Le modèle User utilise Spatie Permission pour la gestion des rôles
