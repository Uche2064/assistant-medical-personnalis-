# 🚀 PROMPT COMPLET POUR IA - DÉVELOPPEMENT FRONTEND ANGULAR

## 📋 CONTEXTE DU PROJET

Tu es une IA experte en développement frontend Angular. Tu vas développer l'interface utilisateur complète pour **AMP - SUNU Santé**, une plateforme de gestion d'assurance santé. Le backend Laravel est déjà développé et documenté. Ton rôle est de créer une application Angular moderne, responsive et performante.

---

## 🎯 STACK TECHNIQUE IMPOSÉE

### Technologies principales :
- **Angular 15+** (dernière version stable)
- **TypeScript** (strict mode)
- **TailwindCSS** pour le styling
- **DaisyUI** pour les composants UI
- **RxJS** pour la gestion d'état réactive
- **HttpClient** pour les appels API

### Bibliothèques complémentaires :
- **Angular Router** pour la navigation
- **Angular Forms** (Reactive Forms)
- **Chart.js** ou **ApexCharts** pour les graphiques
- **ngx-file-drop** pour l'upload de fichiers
- **date-fns** pour la manipulation des dates
- **sweetalert2** pour les modales et alertes

---

## 🏗️ ARCHITECTURE BACKEND (À CONSOMMER)

### 🔐 Authentification et Sécurité

#### Headers obligatoires pour TOUTES les requêtes :
```typescript
{
  'X-API-Key': 'votre_cle_api', // OBLIGATOIRE pour toutes les routes
  'Authorization': 'Bearer {token}', // Pour les routes protégées
  'Content-Type': 'application/json'
}
```

#### Endpoints d'authentification :
```
POST /v1/auth/register - Inscription (client/prestataire)
POST /v1/auth/verify-otp - Vérification OTP
POST /v1/auth/login - Connexion
GET  /v1/auth/me - Profil utilisateur
POST /v1/auth/send-otp - Envoi OTP
POST /v1/auth/forgot-password - Mot de passe oublié
POST /v1/auth/reset-password - Réinitialisation
POST /v1/auth/change-password - Changement
POST /v1/auth/refresh-token - Refresh token
GET  /v1/auth/logout - Déconnexion
POST /v1/auth/check-unique - Vérifier unicité email/contact
```

### 👥 RÔLES UTILISATEURS

Le système gère 8 rôles différents avec des permissions spécifiques :

#### 1. **admin_global** - Super Administrateur
- Gestion complète des gestionnaires
- Accès à toutes les statistiques
- Supervision globale du système

#### 2. **gestionnaire** - Gestionnaire RH
- Gestion du personnel (techniciens, médecins, commerciaux, comptables)
- Création/modification/suppression des comptes personnel
- Statistiques RH

#### 3. **technicien** - Technicien Assurance
- Validation des demandes d'adhésion
- Création de propositions de contrats
- Gestion des garanties et contrats
- Assignation des réseaux prestataires

#### 4. **medecin_controleur** - Médecin Contrôleur
- Validation médicale des prestataires
- Gestion des questionnaires médicaux
- Validation des actes médicaux et factures
- Contrôle des sinistres

#### 5. **commercial** - Commercial
- Génération de codes parrainage
- Création de comptes clients
- Suivi des clients parrainés
- Statistiques commerciales

#### 6. **comptable** - Comptable
- Validation financière des factures
- Autorisation des remboursements
- Gestion des paiements
- Rapports financiers

#### 7. **client** - Client (Physique ou Moral)
- Soumission de demandes d'adhésion
- Gestion de son profil
- Suivi des contrats et remboursements
- Gestion des bénéficiaires (pour entreprises)

#### 8. **prestataire** - Prestataire de Soins
- Soumission de demandes d'adhésion
- Création de sinistres et factures
- Gestion des patients assignés
- Suivi des remboursements

---

## 📡 MODULES API DISPONIBLES

### 1. 🔐 MODULE AUTHENTIFICATION
**Base URL:** `/v1/auth`

**Flux d'inscription :**
```
1. Client/Prestataire → POST /register (avec code_parrainage optionnel)
2. Système → Envoi OTP par email
3. Client → POST /verify-otp
4. Système → Activation du compte
5. Client → POST /login
6. Système → Retour token JWT
```

**Modèles TypeScript requis :**
```typescript
interface RegisterRequest {
  type_demandeur: 'client' | 'prestataire';
  type_client?: 'physique' | 'moral';
  type_prestataire?: 'hopital' | 'clinique' | 'pharmacie' | 'laboratoire';
  email: string;
  password: string;
  contact: string;
  adresse: string;
  nom: string;
  prenoms?: string;
  date_naissance?: string;
  sexe?: 'M' | 'F';
  profession?: string;
  photo?: File;
  code_parrainage?: string; // Optionnel
}

interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    access_token: string;
    token_type: string;
    expires_in: number;
    user: User;
  };
}

interface User {
  id: number;
  email: string;
  contact: string;
  role: string;
  adresse: string;
  est_actif: boolean;
  mot_de_passe_a_changer: boolean;
  nom?: string;
  prenoms?: string;
  sexe?: string;
  date_naissance?: string;
  photo_url?: string;
  solde?: number;
}
```

### 2. 📝 MODULE DEMANDES D'ADHÉSION
**Base URL:** `/v1/demandes-adhesion`

**Endpoints :**
```
GET    / - Liste des demandes (avec filtres)
POST   / - Créer une demande
GET    /{id} - Détails d'une demande
POST   /{id}/valider - Valider (technicien/médecin)
POST   /{id}/rejeter - Rejeter
GET    /stats - Statistiques
```

**Workflow :**
```
Client → Soumission demande avec questionnaire
     ↓
Technicien → Analyse technique
     ↓
Médecin → Validation médicale (si nécessaire)
     ↓
Technicien → Création proposition contrat
     ↓
Client → Acceptation/Refus
     ↓
Système → Création contrat si accepté
```

**Modèle TypeScript :**
```typescript
interface DemandeAdhesion {
  id: number;
  user_id: number;
  client_id?: number;
  prestataire_id?: number;
  statut: 'en_attente' | 'validee_technicien' | 'validee_medecin' | 
          'rejetee' | 'en_cours_traitement';
  date_soumission: string;
  date_validation?: string;
  commentaire_rejet?: string;
  reponses_questionnaire: ReponseQuestion[];
  beneficiaires?: Beneficiaire[];
}

interface ReponseQuestion {
  question_id: number;
  question: Question;
  reponse: string | number | boolean | File;
}
```

### 3. 🏢 MODULE ENTREPRISE
**Base URL:** `/v1/entreprise`

**Endpoints :**
```
POST   /generer-lien-invitation - Générer lien pour employés
GET    /invitations - Liste des invitations
POST   /soumettre-demande-groupe - Soumission groupée
GET    /employes - Liste des employés
GET    /dashboard - Dashboard entreprise
```

**Fonctionnalités :**
- Génération de liens d'invitation uniques pour employés
- Soumission groupée des demandes d'adhésion
- Gestion des bénéficiaires par employé
- Statistiques entreprise

### 4. 🏥 MODULE PRESTATAIRES
**Base URL:** `/v1/prestataires`

**Endpoints :**
```
POST   /demande-adhesion - Soumission demande
GET    /mes-assures - Liste des assurés assignés
POST   /sinistres - Créer un sinistre
GET    /sinistres - Liste des sinistres
POST   /sinistres/{id}/facture - Créer facture
GET    /search-assures - Rechercher assurés
```

**Workflow Sinistre :**
```
Prestataire → Création sinistre
     ↓
Prestataire → Ajout facture avec lignes
     ↓
Technicien → Validation technique
     ↓
Médecin → Validation médicale
     ↓
Comptable → Autorisation remboursement
     ↓
Système → Remboursement effectué
```

### 5. 📄 MODULE CONTRATS
**Base URL:** `/v1/contrats`

**Endpoints :**
```
GET    / - Liste des contrats
POST   / - Créer contrat (technicien)
GET    /{id} - Détails contrat
PUT    /{id} - Modifier contrat
DELETE /{id} - Supprimer contrat
GET    /categories-garanties - Catégories de garanties
GET    /stats - Statistiques contrats
```

**Modèle TypeScript :**
```typescript
interface Contrat {
  id: number;
  nom: string;
  description: string;
  type_contrat: 'individuel' | 'familial' | 'groupe';
  prime_mensuelle: number;
  prime_annuelle: number;
  duree_mois: number;
  age_min: number;
  age_max: number;
  nombre_beneficiaires_max: number;
  garanties: Garantie[];
  statut: 'actif' | 'inactif' | 'archive';
}

interface Garantie {
  id: number;
  nom: string;
  description: string;
  montant_max: number;
  pourcentage_remboursement: number;
  categorie: CategorieGarantie;
}
```

### 6. 💰 MODULE FACTURES & REMBOURSEMENTS
**Base URL:** `/v1/factures`

**Endpoints :**
```
GET    / - Liste des factures
POST   / - Créer facture
GET    /{id} - Détails facture
POST   /{id}/valider-technicien - Validation technicien
POST   /{id}/valider-medecin - Validation médecin
POST   /{id}/autoriser-remboursement - Autorisation comptable
POST   /{id}/rejeter - Rejeter facture
GET    /{id}/pdf - Télécharger PDF
```

**Workflow de validation (3 étapes) :**
```
1. Technicien → Validation conformité technique
2. Médecin → Validation justification médicale
3. Comptable → Autorisation financière
```

### 7. ❓ MODULE QUESTIONS (Questionnaires dynamiques)
**Base URL:** `/v1/questions`

**Endpoints :**
```
GET    / - Liste des questions (avec filtres)
POST   / - Créer questions (bulk)
GET    /{id} - Détails question
PUT    /{id} - Modifier question
DELETE /{id} - Supprimer question
GET    /stats - Statistiques questions
```

**Types de questions :**
- `text` - Texte libre
- `number` - Numérique
- `boolean` - Oui/Non
- `date` - Date
- `file` - Fichier (PDF, images)

**Destinataires :**
- `prospect_client` - Clients potentiels
- `prospect_prestataire` - Prestataires potentiels

### 8. 🛡️ MODULE GARANTIES
**Base URL:** `/v1/garanties`

**Endpoints :**
```
GET    / - Liste des garanties
POST   / - Créer garantie
GET    /{id} - Détails garantie
PUT    /{id} - Modifier garantie
DELETE /{id} - Supprimer garantie
PATCH  /{id} - Toggle statut
```

### 9. 🔔 MODULE NOTIFICATIONS
**Base URL:** `/v1/notifications`

**Endpoints :**
```
GET    / - Liste des notifications
GET    /unread-count - Nombre non lues
POST   /{id}/mark-as-read - Marquer comme lue
POST   /mark-all-as-read - Tout marquer comme lu
DELETE /{id} - Supprimer notification
```

**Types de notifications :**
- Nouveau compte créé
- Nouvelle demande d'adhésion
- Demande validée/rejetée
- Nouvelle facture
- Facture validée
- Remboursement effectué

### 10. 📊 MODULE STATISTIQUES
**Base URL:** `/v1/statistiques`

**Endpoints :**
```
GET /dashboard-stats - Statistiques globales
```

**Données retournées :**
- Nombre total de clients
- Nombre total de prestataires
- Nombre de demandes en attente
- Montant total des remboursements
- Graphiques d'évolution

### 11. 👑 MODULE ADMIN
**Base URL:** `/v1/admin/gestionnaires`

**Endpoints :**
```
GET    / - Liste des gestionnaires
POST   / - Créer gestionnaire
GET    /stats - Statistiques
GET    /{id} - Détails gestionnaire
PATCH  /{id}/change-status - Changer statut
DELETE /{id} - Supprimer gestionnaire
```

### 12. 🔧 MODULE TECHNICIEN
**Base URL:** `/v1/technicien`

**Endpoints :**
```
GET    /demandes-adhesion - Demandes à traiter
POST   /demandes-adhesion/{id}/valider - Valider demande
POST   /propositions-contrat - Créer proposition
GET    /propositions-contrat - Liste propositions
POST   /assigner-reseau - Assigner prestataire à client
GET    /factures - Factures à valider
POST   /factures/{id}/valider - Valider facture
```

### 13. 💼 MODULE COMPTABLE
**Base URL:** `/v1/comptable`

**Endpoints :**
```
GET    /dashboard - Dashboard comptable
GET    /factures - Factures à autoriser
POST   /factures/{id}/valider-remboursement - Valider
POST   /factures/{id}/effectuer-remboursement - Effectuer
POST   /factures/{id}/rejeter - Rejeter
```

### 14. 🎯 MODULE COMMERCIAL (MIS À JOUR)
**Base URL:** `/v1/commercial`

**Endpoints :**
```
POST   /generer-code-parrainage - Générer code unique (durée 1 an)
GET    /mon-code-parrainage - Voir le code actuel
GET    /historique-codes-parrainage - Historique des codes
POST   /renouveler-code-parrainage - Renouveler après expiration
POST   /creer-compte-client - Créer compte client
GET    /mes-clients-parraines - Liste clients parrainés
GET    /mes-statistiques - Statistiques commerciales
```

**Système de parrainage amélioré :**
- **Durée contrôlée** : Chaque code parrainage est valide pendant exactement 1 an
- **Un seul code actif** : Un commercial ne peut avoir qu'un seul code actif à la fois
- **Historique complet** : Consultation de tous les codes précédents avec leurs statuts
- **Renouvellement contrôlé** : Nouveau code seulement après expiration du précédent
- **Messages informatifs** : Si un commercial essaie de générer un nouveau code alors qu'il en a un actif, le système lui renvoie le code actuel avec sa date d'expiration
- Commercial peut créer des comptes clients directement
- Mot de passe généré automatiquement et envoyé par email
- Clients peuvent s'inscrire avec code parrainage (optionnel)
- Suivi des performances commerciales

### 15. 👥 MODULE GESTIONNAIRE
**Base URL:** `/v1/gestionnaire/personnels`

**Endpoints :**
```
GET    / - Liste du personnel
POST   / - Créer personnel
GET    /stats - Statistiques
GET    /{id} - Détails personnel
PATCH  /{id}/change-status - Changer statut
DELETE /{id} - Supprimer personnel
```

### 16. 🏥 MODULE ASSURÉS
**Base URL:** `/v1/assures`

**Endpoints :**
```
GET    / - Liste des assurés
GET    /{id} - Détails assuré
GET    /{id}/contrats - Contrats de l'assuré
GET    /{id}/sinistres - Sinistres de l'assuré
GET    /{id}/remboursements - Remboursements
```

### 17. 📁 MODULE TÉLÉCHARGEMENTS
**Base URL:** `/v1/downloads`

**Endpoints :**
```
GET /facture/{id} - Télécharger facture PDF
GET /contrat/{id} - Télécharger contrat PDF
GET /justificatif/{filename} - Télécharger justificatif
```

### 18. 🔗 MODULE CLIENT-PRESTATAIRES
**Base URL:** `/v1/client-prestataires`

**Endpoints :**
```
GET    / - Liste des relations
POST   /assigner - Assigner prestataire à client
DELETE /{id} - Supprimer assignation
GET    /client/{id}/prestataires - Prestataires du client
GET    /prestataire/{id}/clients - Clients du prestataire
```

---

## 🎨 DESIGN SYSTEM & UI/UX

### Palette de couleurs (TailwindCSS + DaisyUI)
```css
/* Couleurs principales */
primary: #2c5aa0 (Bleu SUNU)
secondary: #f59e0b (Orange)
accent: #10b981 (Vert)
neutral: #3d4451
base-100: #ffffff

/* Couleurs de statut */
success: #10b981
warning: #f59e0b
error: #ef4444
info: #3b82f6

/* Couleurs de texte */
text-primary: #1f2937
text-secondary: #6b7280
text-muted: #9ca3af
```

### Composants DaisyUI à utiliser :
- **navbar** - Navigation principale
- **drawer** - Menu latéral
- **card** - Cartes d'information
- **badge** - Badges de statut
- **button** - Boutons d'action
- **modal** - Modales
- **alert** - Alertes et messages
- **table** - Tableaux de données
- **form-control** - Contrôles de formulaire
- **tabs** - Onglets
- **dropdown** - Menus déroulants
- **stats** - Statistiques
- **progress** - Barres de progression
- **loading** - Indicateurs de chargement

### Responsive Design :
- **Mobile First** : Conception prioritaire mobile
- **Breakpoints** : sm (640px), md (768px), lg (1024px), xl (1280px), 2xl (1536px)
- **Navigation** : Drawer sur mobile, sidebar sur desktop

---

## 🏗️ STRUCTURE DE L'APPLICATION ANGULAR

### Architecture des dossiers :
```
src/
├── app/
│   ├── core/                    # Services singleton, guards, interceptors
│   │   ├── guards/
│   │   │   ├── auth.guard.ts
│   │   │   └── role.guard.ts
│   │   ├── interceptors/
│   │   │   ├── api-key.interceptor.ts
│   │   │   ├── auth.interceptor.ts
│   │   │   └── error.interceptor.ts
│   │   ├── services/
│   │   │   ├── api.service.ts
│   │   │   ├── auth.service.ts
│   │   │   ├── notification.service.ts
│   │   │   └── storage.service.ts
│   │   └── core.module.ts
│   │
│   ├── shared/                  # Composants, pipes, directives partagés
│   │   ├── components/
│   │   │   ├── navbar/
│   │   │   ├── sidebar/
│   │   │   ├── footer/
│   │   │   ├── loader/
│   │   │   ├── pagination/
│   │   │   └── breadcrumb/
│   │   ├── pipes/
│   │   │   ├── date-format.pipe.ts
│   │   │   └── currency-format.pipe.ts
│   │   ├── directives/
│   │   └── shared.module.ts
│   │
│   ├── features/                # Modules fonctionnels
│   │   ├── auth/
│   │   │   ├── login/
│   │   │   ├── register/
│   │   │   ├── forgot-password/
│   │   │   └── verify-otp/
│   │   │
│   │   ├── dashboard/           # Dashboards par rôle
│   │   │   ├── admin/
│   │   │   ├── client/
│   │   │   ├── commercial/
│   │   │   ├── technicien/
│   │   │   ├── medecin/
│   │   │   ├── comptable/
│   │   │   ├── gestionnaire/
│   │   │   └── prestataire/
│   │   │
│   │   ├── demandes-adhesion/
│   │   │   ├── list/
│   │   │   ├── create/
│   │   │   ├── detail/
│   │   │   └── validate/
│   │   │
│   │   ├── contrats/
│   │   │   ├── list/
│   │   │   ├── create/
│   │   │   ├── detail/
│   │   │   └── propositions/
│   │   │
│   │   ├── factures/
│   │   │   ├── list/
│   │   │   ├── create/
│   │   │   ├── detail/
│   │   │   └── validate/
│   │   │
│   │   ├── commercial/
│   │   │   ├── dashboard/
│   │   │   ├── create-client/
│   │   │   ├── clients-list/
│   │   │   └── statistics/
│   │   │
│   │   ├── entreprise/
│   │   │   ├── dashboard/
│   │   │   ├── invitations/
│   │   │   └── employes/
│   │   │
│   │   ├── prestataires/
│   │   │   ├── dashboard/
│   │   │   ├── sinistres/
│   │   │   └── assures/
│   │   │
│   │   ├── questions/
│   │   │   ├── list/
│   │   │   ├── create/
│   │   │   └── edit/
│   │   │
│   │   ├── garanties/
│   │   │   ├── list/
│   │   │   ├── create/
│   │   │   └── edit/
│   │   │
│   │   ├── notifications/
│   │   │   └── list/
│   │   │
│   │   └── profile/
│   │       ├── view/
│   │       └── edit/
│   │
│   ├── models/                  # Interfaces TypeScript
│   │   ├── user.model.ts
│   │   ├── demande-adhesion.model.ts
│   │   ├── contrat.model.ts
│   │   ├── facture.model.ts
│   │   ├── garantie.model.ts
│   │   ├── question.model.ts
│   │   ├── notification.model.ts
│   │   └── api-response.model.ts
│   │
│   ├── app-routing.module.ts
│   ├── app.component.ts
│   └── app.module.ts
│
├── assets/
│   ├── images/
│   ├── icons/
│   └── fonts/
│
├── environments/
│   ├── environment.ts
│   └── environment.prod.ts
│
└── styles/
    ├── tailwind.css
    └── custom.css
```

---

## 🔒 SÉCURITÉ & AUTHENTIFICATION

### 1. Intercepteur API Key
```typescript
// core/interceptors/api-key.interceptor.ts
@Injectable()
export class ApiKeyInterceptor implements HttpInterceptor {
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const apiKey = environment.apiKey;
    const clonedReq = req.clone({
      setHeaders: {
        'X-API-Key': apiKey
      }
    });
    return next.handle(clonedReq);
  }
}
```

### 2. Intercepteur Auth JWT
```typescript
// core/interceptors/auth.interceptor.ts
@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private authService: AuthService) {}

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = this.authService.getToken();
    if (token) {
      const clonedReq = req.clone({
        setHeaders: {
          'Authorization': `Bearer ${token}`
        }
      });
      return next.handle(clonedReq);
    }
    return next.handle(req);
  }
}
```

### 3. Garde d'authentification
```typescript
// core/guards/auth.guard.ts
@Injectable()
export class AuthGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(): boolean {
    if (this.authService.isLoggedIn()) {
      return true;
    }
    this.router.navigate(['/auth/login']);
    return false;
  }
}
```

### 4. Garde de rôle
```typescript
// core/guards/role.guard.ts
@Injectable()
export class RoleGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(route: ActivatedRouteSnapshot): boolean {
    const expectedRoles = route.data['roles'] as string[];
    const userRole = this.authService.getUserRole();
    
    if (expectedRoles.includes(userRole)) {
      return true;
    }
    
    this.router.navigate(['/unauthorized']);
    return false;
  }
}
```

---

## 📋 FONCTIONNALITÉS PRIORITAIRES PAR RÔLE

### 🔐 Pour tous les utilisateurs :
- [x] Inscription avec validation OTP
- [x] Connexion/Déconnexion
- [x] Gestion du profil
- [x] Changement de mot de passe
- [x] Notifications en temps réel
- [x] Responsive design

### 👑 Admin Global :
- [x] Dashboard avec statistiques globales
- [x] Gestion des gestionnaires (CRUD)
- [x] Visualisation de toutes les activités
- [x] Rapports et exports

### 👥 Gestionnaire :
- [x] Dashboard RH
- [x] Gestion du personnel (CRUD)
- [x] Statistiques du personnel
- [x] Gestion des rôles et permissions

### 🔧 Technicien :
- [x] Dashboard des demandes en attente
- [x] Validation des demandes d'adhésion
- [x] Création de propositions de contrats
- [x] Gestion des contrats et garanties
- [x] Validation technique des factures
- [x] Assignation des réseaux prestataires

### 🏥 Médecin Contrôleur :
- [x] Dashboard médical
- [x] Validation médicale des prestataires
- [x] Gestion des questionnaires médicaux
- [x] Validation médicale des factures
- [x] Contrôle des actes médicaux

### 🎯 Commercial :
- [x] Dashboard commercial
- [x] Génération de codes parrainage
- [x] Création de comptes clients
- [x] Liste des clients parrainés
- [x] Statistiques de performance
- [x] Suivi des conversions

### 💼 Comptable :
- [x] Dashboard financier
- [x] Liste des factures à autoriser
- [x] Validation financière
- [x] Autorisation des remboursements
- [x] Rapports financiers
- [x] Suivi des paiements

### 👤 Client :
- [x] Dashboard personnel
- [x] Soumission de demande d'adhésion
- [x] Suivi des demandes
- [x] Visualisation des contrats
- [x] Historique des remboursements
- [x] Gestion des bénéficiaires (entreprise)
- [x] Génération de liens d'invitation (entreprise)

### 🏥 Prestataire :
- [x] Dashboard prestataire
- [x] Soumission de demande d'adhésion
- [x] Liste des assurés assignés
- [x] Création de sinistres
- [x] Gestion des factures
- [x] Suivi des remboursements

---

## 🎯 EXIGENCES FONCTIONNELLES DÉTAILLÉES

### 1. Module d'Authentification

#### Page d'inscription :
- Formulaire multi-étapes (wizard)
- Étape 1 : Type de demandeur (client/prestataire)
- Étape 2 : Type spécifique (physique/moral pour client, type pour prestataire)
- Étape 3 : Informations personnelles
- Étape 4 : Questionnaire (si applicable)
- Validation en temps réel
- Upload de photo avec preview
- Champ code parrainage optionnel
- Affichage des erreurs de validation

#### Page de connexion :
- Formulaire email/mot de passe
- Option "Se souvenir de moi"
- Lien "Mot de passe oublié"
- Redirection selon le rôle après connexion

#### Vérification OTP :
- Saisie du code à 6 chiffres
- Compte à rebours pour expiration
- Bouton "Renvoyer le code"
- Validation automatique

### 2. Dashboards par rôle

Chaque dashboard doit contenir :
- **Statistiques clés** (cards avec icônes)
- **Graphiques** (évolution, répartition)
- **Tableau des actions récentes**
- **Notifications importantes**
- **Actions rapides** (boutons d'action)

### 3. Gestion des demandes d'adhésion

#### Liste des demandes :
- Tableau avec filtres (statut, date, type)
- Recherche par nom/email
- Pagination
- Actions : Voir, Valider, Rejeter
- Badges de statut colorés

#### Création de demande :
- Formulaire dynamique selon le type
- Questionnaire avec validation conditionnelle
- Upload de documents
- Ajout de bénéficiaires (pour entreprises)
- Sauvegarde brouillon
- Prévisualisation avant soumission

#### Validation de demande :
- Affichage complet des informations
- Réponses au questionnaire
- Documents joints
- Historique des actions
- Formulaire de validation/rejet
- Commentaires

### 4. Gestion des contrats

#### Liste des contrats :
- Tableau avec filtres
- Recherche
- Actions : Voir, Modifier, Supprimer
- Statut actif/inactif

#### Création de contrat :
- Formulaire complet
- Sélection des garanties
- Calcul automatique des primes
- Validation des règles métier

#### Propositions de contrats :
- Création de proposition personnalisée
- Envoi au client
- Suivi de l'acceptation/refus

### 5. Gestion des factures

#### Liste des factures :
- Tableau avec filtres par statut
- Recherche
- Workflow de validation visible
- Actions selon le rôle

#### Création de facture :
- Sélection de l'assuré
- Ajout de lignes de facture
- Calcul automatique des montants
- Upload de justificatifs
- Prévisualisation

#### Validation de facture :
- Affichage des détails
- Justificatifs joints
- Formulaire de validation
- Commentaires
- Historique des validations

### 6. Module Commercial

#### Dashboard :
- Code parrainage affiché
- Bouton "Générer nouveau code"
- Statistiques : Total clients, Actifs, Taux d'activation
- Graphique d'évolution
- Liste des clients récents

#### Création de client :
- Formulaire simplifié (pas de mot de passe)
- Type client (physique/moral)
- Validation conditionnelle
- Upload de photo optionnel
- Affichage du mot de passe généré après création

#### Liste des clients parrainés :
- Tableau avec informations clés
- Filtres et recherche
- Badges de statut
- Actions : Voir détails

### 7. Module Entreprise

#### Dashboard :
- Statistiques employés
- Liens d'invitation actifs
- Demandes en cours
- Actions rapides

#### Génération de liens :
- Formulaire simple
- Génération de lien unique
- Copie automatique
- Partage par email
- Liste des liens générés

#### Soumission groupée :
- Upload fichier CSV/Excel
- Mapping des colonnes
- Validation des données
- Prévisualisation
- Soumission en masse

### 8. Module Prestataire

#### Dashboard :
- Statistiques patients
- Sinistres en cours
- Factures en attente
- Actions rapides

#### Création de sinistre :
- Recherche d'assuré
- Informations du sinistre
- Ajout de facture
- Upload de justificatifs

#### Gestion des factures :
- Liste des factures
- Statut de validation
- Suivi des remboursements

---

## 🎨 COMPOSANTS UI RÉUTILISABLES À CRÉER

### 1. Composants de base :
```typescript
// shared/components/
- ButtonComponent (primary, secondary, danger, etc.)
- InputComponent (text, email, password, number, date)
- SelectComponent (simple, multiple, searchable)
- TextareaComponent
- CheckboxComponent
- RadioComponent
- FileUploadComponent (single, multiple, drag-drop)
- DatePickerComponent
- TimePickerComponent
- SearchBarComponent
- PaginationComponent
- LoaderComponent (spinner, skeleton)
- AlertComponent (success, error, warning, info)
- ModalComponent
- ToastComponent
- BreadcrumbComponent
- TabsComponent
- AccordionComponent
- TooltipComponent
- BadgeComponent
- CardComponent
- TableComponent (avec tri, filtres, pagination)
```

### 2. Composants métier :
```typescript
// shared/components/business/
- UserAvatarComponent
- StatusBadgeComponent
- RoleChipComponent
- NotificationItemComponent
- StatCardComponent
- ChartComponent (line, bar, pie, donut)
- TimelineComponent
- QuestionnaireComponent
- BeneficiaireFormComponent
- DocumentViewerComponent
- InvoiceLineItemComponent
```

---

## 📊 GESTION D'ÉTAT

### Utilisation de RxJS et Services :
```typescript
// core/services/state.service.ts
@Injectable({ providedIn: 'root' })
export class StateService {
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  private notificationsSubject = new BehaviorSubject<Notification[]>([]);
  public notifications$ = this.notificationsSubject.asObservable();

  setCurrentUser(user: User): void {
    this.currentUserSubject.next(user);
  }

  updateNotifications(notifications: Notification[]): void {
    this.notificationsSubject.next(notifications);
  }
}
```

---

## 🧪 TESTS

### Tests unitaires requis :
- Services (auth, api, etc.)
- Composants (logique métier)
- Guards
- Interceptors
- Pipes

### Tests E2E requis :
- Flux d'inscription complet
- Flux de connexion
- Création de demande d'adhésion
- Validation de demande
- Création de facture

---

## 📦 CONFIGURATION INITIALE

### 1. Installation des dépendances :
```bash
npm install -D tailwindcss postcss autoprefixer
npm install daisyui
npm install chart.js ng2-charts
npm install sweetalert2
npm install date-fns
npm install ngx-file-drop
```

### 2. Configuration TailwindCSS :
```javascript
// tailwind.config.js
module.exports = {
  content: ['./src/**/*.{html,ts}'],
  theme: {
    extend: {
      colors: {
        primary: '#2c5aa0',
        secondary: '#f59e0b',
      }
    }
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: ['light', 'dark'],
  }
}
```

### 3. Configuration des environnements :
```typescript
// environments/environment.ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
  apiKey: 'your_api_key_here',
  wsUrl: 'ws://localhost:6001'
};
```

---

## 🚀 LIVRABLES ATTENDUS

### Phase 1 - Authentification & Base (Semaine 1-2) :
- [ ] Configuration du projet Angular
- [ ] Installation et configuration TailwindCSS + DaisyUI
- [ ] Structure des dossiers
- [ ] Services de base (API, Auth, Storage)
- [ ] Intercepteurs (API Key, Auth, Error)
- [ ] Guards (Auth, Role)
- [ ] Module d'authentification complet
- [ ] Layout principal (navbar, sidebar, footer)
- [ ] Page de connexion
- [ ] Page d'inscription
- [ ] Vérification OTP
- [ ] Mot de passe oublié

### Phase 2 - Dashboards (Semaine 3-4) :
- [ ] Dashboard Admin
- [ ] Dashboard Gestionnaire
- [ ] Dashboard Technicien
- [ ] Dashboard Médecin
- [ ] Dashboard Commercial
- [ ] Dashboard Comptable
- [ ] Dashboard Client
- [ ] Dashboard Prestataire
- [ ] Composants de statistiques
- [ ] Graphiques

### Phase 3 - Modules métier (Semaine 5-8) :
- [ ] Module Demandes d'adhésion
- [ ] Module Contrats
- [ ] Module Factures
- [ ] Module Questions
- [ ] Module Garanties
- [ ] Module Notifications
- [ ] Module Commercial (parrainage)
- [ ] Module Entreprise
- [ ] Module Prestataire

### Phase 4 - Fonctionnalités avancées (Semaine 9-10) :
- [ ] Gestion du profil
- [ ] Upload de fichiers
- [ ] Téléchargement de PDF
- [ ] Notifications en temps réel
- [ ] Recherche globale
- [ ] Filtres avancés
- [ ] Exports (CSV, Excel, PDF)

### Phase 5 - Tests & Optimisation (Semaine 11-12) :
- [ ] Tests unitaires
- [ ] Tests E2E
- [ ] Optimisation des performances
- [ ] Lazy loading
- [ ] PWA
- [ ] Documentation
- [ ] Déploiement

---

## 📝 CONVENTIONS DE CODE

### Naming :
- **Composants** : PascalCase (ex: `UserListComponent`)
- **Services** : PascalCase + Service (ex: `AuthService`)
- **Interfaces** : PascalCase (ex: `User`, `DemandeAdhesion`)
- **Variables** : camelCase (ex: `currentUser`, `isLoading`)
- **Constantes** : UPPER_SNAKE_CASE (ex: `API_URL`, `MAX_FILE_SIZE`)

### Structure des fichiers :
```
feature-name/
├── feature-name.component.ts
├── feature-name.component.html
├── feature-name.component.css
├── feature-name.component.spec.ts
└── feature-name.module.ts (si module)
```

### Commentaires :
- Documenter toutes les fonctions publiques
- Expliquer la logique complexe
- Utiliser JSDoc pour les services

---

## 🎯 CRITÈRES DE QUALITÉ

### Performance :
- Lazy loading des modules
- OnPush change detection
- TrackBy dans les ngFor
- Unsubscribe des observables
- Optimisation des images

### Accessibilité :
- Labels pour tous les inputs
- Attributs ARIA
- Navigation au clavier
- Contraste des couleurs

### SEO :
- Meta tags
- Titres de pages
- Descriptions

### Sécurité :
- Validation côté client ET serveur
- Sanitization des inputs
- Protection CSRF
- Gestion sécurisée des tokens

---

## 📞 SUPPORT & RESSOURCES

### Documentation API :
- Collections Postman disponibles dans `.documentation_postman/`
- 19 modules documentés
- Exemples de requêtes/réponses

### Backend :
- Laravel 10+
- PHP 8+
- MySQL
- JWT Authentication

### Contact :
- Email : dev@sunusante.com
- Documentation : README_Frontend_Commercial_Integration.md

---

## ✅ CHECKLIST FINALE

Avant de considérer le projet terminé, vérifier :

- [ ] Toutes les routes API sont intégrées
- [ ] Tous les rôles ont leur dashboard fonctionnel
- [ ] L'authentification fonctionne correctement
- [ ] Les guards protègent les routes
- [ ] Les intercepteurs ajoutent les headers requis
- [ ] Les formulaires ont une validation complète
- [ ] Les erreurs sont gérées et affichées
- [ ] Le design est responsive (mobile, tablet, desktop)
- [ ] Les notifications fonctionnent
- [ ] L'upload de fichiers fonctionne
- [ ] Le téléchargement de PDF fonctionne
- [ ] Les graphiques s'affichent correctement
- [ ] Les tableaux ont tri, filtres et pagination
- [ ] Le code est propre et commenté
- [ ] Les tests passent
- [ ] L'application est optimisée
- [ ] La documentation est à jour

---

## 🎉 CONCLUSION

Tu as maintenant toutes les informations nécessaires pour développer une application Angular complète, moderne et performante pour SUNU Santé. Le backend est entièrement fonctionnel et documenté. Concentre-toi sur :

1. **L'expérience utilisateur** : Interface intuitive et fluide
2. **La performance** : Application rapide et réactive
3. **La qualité du code** : Code propre, testé et maintenable
4. **Le design** : Interface moderne avec TailwindCSS et DaisyUI

Bonne chance ! 🚀

