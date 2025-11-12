# 🎯 PROMPT FRONTEND - Module Proposition de Contrats par Technicien

## 📋 CONTEXTE ET OBJECTIF

Vous devez implémenter un module complet permettant aux **techniciens** de proposer des contrats aux clients après analyse de leurs demandes d'adhésion. Ce module fait partie d'un système d'assurance avec gestion des demandes d'adhésion, propositions de contrats et notifications.

## 🔄 FLUX MÉTIER PRINCIPAL

```
Demande EN_ATTENTE → Technicien analyse → Proposition CONTRAT → Client notifié → Client accepte/refuse
```

### États des Demandes :
- `en_attente` : Demande soumise, en attente d'analyse
- `proposee` : Contrat proposé par le technicien
- `acceptee` : Contrat accepté par le client
- `rejetee` : Demande rejetée

### États des Propositions :
- `proposee` : Proposition créée par le technicien
- `acceptee` : Acceptée par le client
- `refusee` : Refusée par le client
- `expiree` : Proposition expirée

## 🏗️ ARCHITECTURE COMPOSANTS

### 1. **Composants Principaux**

```
src/app/modules/technicien/
├── proposition-contrats/
│   ├── PropositionContratsComponent.ts
│   ├── ListeDemandesComponent.ts
│   ├── DetailDemandeComponent.ts
│   ├── ProposerContratComponent.ts
│   ├── HistoriquePropositionsComponent.ts
│   └── StatistiquesPropositionsComponent.ts
├── shared/
│   ├── DemandeCardComponent.ts
│   ├── PropositionCardComponent.ts
│   ├── ContratSelectorComponent.ts
│   └── StatusBadgeComponent.ts
└── services/
    ├── PropositionContratService.ts
    ├── DemandeAdhesionService.ts
    └── NotificationService.ts
```

### 2. **Services API**

```typescript
// PropositionContratService.ts
export class PropositionContratService {
  // Proposer un contrat pour une demande
  proposerContrat(demandeId: number, data: ProposerContratRequest): Observable<PropositionContrat>
  
  // Récupérer les propositions d'un technicien
  getPropositionsTechnicien(filters?: PropositionFilters): Observable<PaginatedResponse<PropositionContrat>>
  
  // Récupérer l'historique des propositions
  getHistoriquePropositions(): Observable<PropositionContrat[]>
  
  // Récupérer les statistiques
  getStatistiquesPropositions(): Observable<PropositionStats>
}

// DemandeAdhesionService.ts
export class DemandeAdhesionService {
  // Récupérer les demandes en attente
  getDemandesEnAttente(): Observable<DemandeAdhesion[]>
  
  // Récupérer le détail d'une demande
  getDetailDemande(id: number): Observable<DemandeAdhesion>
  
  // Valider une demande
  validerDemande(id: number, motif: string): Observable<DemandeAdhesion>
  
  // Rejeter une demande
  rejeterDemande(id: number, motif: string): Observable<DemandeAdhesion>
}
```

## 🎨 INTERFACES UTILISATEUR

### 1. **Dashboard Technicien - Vue d'ensemble**

```typescript
interface TechnicienDashboard {
  // Statistiques principales
  stats: {
    demandesEnAttente: number;
    propositionsEnCours: number;
    contratsAcceptes: number;
    tauxAcceptation: number;
  };
  
  // Demandes récentes
  demandesRecentes: DemandeAdhesion[];
  
  // Propositions récentes
  propositionsRecentes: PropositionContrat[];
}
```

**Composant :** `TechnicienDashboardComponent`
- **Layout :** Grid avec cartes de statistiques
- **Actions rapides :** Boutons "Analyser demandes", "Voir propositions"
- **Notifications :** Badge avec nombre de demandes en attente

### 2. **Liste des Demandes d'Adhésion**

```typescript
interface DemandeAdhesion {
  id: number;
  type_demandeur: 'client' | 'prestataire';
  demandeur: string; // Nom complet ou raison sociale
  email: string;
  contact: string;
  statut: 'en_attente' | 'proposee' | 'acceptee' | 'rejetee';
  created_at: string;
  updated_at: string;
  
  // Informations détaillées
  reponses_questions: ReponseQuestion[];
  beneficiaires: Beneficiaire[];
  propositions_contrat?: PropositionContrat[];
}
```

**Composant :** `ListeDemandesComponent`
- **Filtres :** Statut, type demandeur, date
- **Actions :** Voir détail, Proposer contrat, Valider, Rejeter
- **Pagination :** 10 éléments par page
- **Recherche :** Par nom, email, contact

### 3. **Détail d'une Demande d'Adhésion**

**Composant :** `DetailDemandeComponent`

#### Onglets :
1. **Informations Générales**
   - Données du demandeur
   - Statut et dates
   - Historique des actions

2. **Réponses au Questionnaire**
   - Questions et réponses de l'assuré principal
   - Affichage par question avec type de données

3. **Bénéficiaires**
   - Liste des bénéficiaires ajoutés
   - Réponses au questionnaire de chaque bénéficiaire
   - Informations de contact

4. **Propositions de Contrat**
   - Historique des propositions
   - Statut de chaque proposition
   - Actions disponibles

#### Actions Disponibles :
```typescript
interface DemandeActions {
  proposerContrat: () => void;
  validerDemande: (motif: string) => void;
  rejeterDemande: (motif: string) => void;
  voirHistorique: () => void;
}
```

### 4. **Proposer un Contrat**

```typescript
interface ProposerContratRequest {
  contrat_id: number;
  commentaires_technicien?: string;
}

interface TypeContrat {
  id: number;
  libelle: string;
  prime_standard: number;
  frais_gestion: number;
  est_actif: boolean;
  categories_garanties: CategorieGarantie[];
}
```

**Composant :** `ProposerContratComponent`

#### Étapes :
1. **Sélection du Contrat**
   - Liste des contrats disponibles
   - Détails du contrat sélectionné
   - Calcul automatique de la prime

2. **Commentaires**
   - Zone de texte pour commentaires du technicien
   - Limite : 1000 caractères
   - Prévisualisation

3. **Confirmation**
   - Récapitulatif de la proposition
   - Informations du client
   - Détails du contrat proposé

#### Validation :
```typescript
const validationRules = {
  contrat_id: ['required'],
  commentaires_technicien: ['max:1000']
};
```

### 5. **Historique des Propositions**

**Composant :** `HistoriquePropositionsComponent`

```typescript
interface PropositionContrat {
  id: number;
  demande_adhesion_id: number;
  contrat_id: number;
  commentaires_technicien?: string;
  technicien_id: number;
  statut: 'proposee' | 'acceptee' | 'refusee' | 'expiree';
  date_proposition: string;
  date_acceptation?: string;
  date_refus?: string;
  
  // Relations
  demande_adhesion: DemandeAdhesion;
  contrat: TypeContrat;
  technicien: Personnel;
}
```

#### Fonctionnalités :
- **Filtres :** Statut, période, technicien
- **Tri :** Par date, statut, montant
- **Actions :** Voir détail, Modifier (si proposee)
- **Export :** PDF des propositions

## 🔧 LOGIQUE MÉTIER

### 1. **Gestion des États**

```typescript
class DemandeStateManager {
  // Vérifier si une demande peut être proposée
  canProposerContrat(demande: DemandeAdhesion): boolean {
    return demande.statut === 'en_attente' && 
           demande.reponses_questions.length > 0;
  }
  
  // Vérifier si une proposition peut être modifiée
  canModifierProposition(proposition: PropositionContrat): boolean {
    return proposition.statut === 'proposee';
  }
  
  // Calculer le taux d'acceptation
  calculateTauxAcceptation(propositions: PropositionContrat[]): number {
    const acceptees = propositions.filter(p => p.statut === 'acceptee').length;
    return (acceptees / propositions.length) * 100;
  }
}
```

### 2. **Notifications en Temps Réel**

```typescript
class NotificationManager {
  // Écouter les nouvelles demandes
  listenNewDemandes(): Observable<DemandeAdhesion> {
    return this.webSocketService.listen('new-demande');
  }
  
  // Écouter les réponses aux propositions
  listenPropositionResponse(): Observable<PropositionContrat> {
    return this.webSocketService.listen('proposition-response');
  }
  
  // Marquer les notifications comme lues
  markAsRead(notificationId: number): Observable<void> {
    return this.notificationService.markAsRead(notificationId);
  }
}
```

### 3. **Gestion des Erreurs**

```typescript
class ErrorHandler {
  handlePropositionError(error: any): string {
    switch (error.status) {
      case 400:
        return 'Données de proposition invalides';
      case 403:
        return 'Vous n\'êtes pas autorisé à proposer des contrats';
      case 404:
        return 'Demande d\'adhésion non trouvée';
      case 500:
        return 'Erreur serveur lors de la proposition';
      default:
        return 'Erreur inconnue';
    }
  }
}
```

## 📱 RESPONSIVE DESIGN

### 1. **Breakpoints**
```scss
$breakpoints: (
  mobile: 768px,
  tablet: 1024px,
  desktop: 1200px
);
```

### 2. **Adaptations Mobile**
- **Liste des demandes :** Cartes empilées verticalement
- **Détail demande :** Onglets en accordéon
- **Proposition contrat :** Formulaire en étapes (stepper)
- **Actions :** Boutons pleine largeur

### 3. **Adaptations Desktop**
- **Liste des demandes :** Tableau avec colonnes
- **Détail demande :** Onglets horizontaux
- **Proposition contrat :** Formulaire en une page
- **Actions :** Boutons groupés

## 🎨 STYLE ET DESIGN

### 1. **Palette de Couleurs**
```scss
$colors: (
  primary: #2563eb,      // Bleu principal
  secondary: #64748b,    // Gris secondaire
  success: #059669,      // Vert succès
  warning: #d97706,      // Orange attention
  error: #dc2626,        // Rouge erreur
  info: #0891b2          // Bleu info
);
```

### 2. **Composants de Statut**
```scss
.status-badge {
  &.en-attente { background: $warning; }
  &.proposee { background: $info; }
  &.acceptee { background: $success; }
  &.rejetee { background: $error; }
}
```

### 3. **Animations**
- **Chargement :** Skeleton loaders pour les listes
- **Transitions :** Fade in/out pour les modales
- **Feedback :** Toast notifications pour les actions

## 🔐 SÉCURITÉ ET PERMISSIONS

### 1. **Vérification des Rôles**
```typescript
@Injectable()
export class RoleGuard implements CanActivate {
  canActivate(): boolean {
    return this.authService.hasRole('technicien');
  }
}
```

### 2. **Protection des Routes**
```typescript
const routes: Routes = [
  {
    path: 'technicien',
    canActivate: [RoleGuard],
    children: [
      { path: 'propositions', component: PropositionContratsComponent },
      { path: 'demandes', component: ListeDemandesComponent },
      { path: 'demandes/:id', component: DetailDemandeComponent }
    ]
  }
];
```

## 📊 TESTS ET VALIDATION

### 1. **Tests Unitaires**
```typescript
describe('PropositionContratService', () => {
  it('should propose contract successfully', () => {
    // Test de proposition de contrat
  });
  
  it('should handle proposal errors', () => {
    // Test de gestion d'erreurs
  });
});
```

### 2. **Tests d'Intégration**
```typescript
describe('Proposition Workflow', () => {
  it('should complete proposal flow', () => {
    // Test du flux complet
  });
});
```

## 🚀 CHECKLIST DE DÉVELOPPEMENT

### Phase 1 - Infrastructure
- [ ] Créer les services API
- [ ] Implémenter les guards de sécurité
- [ ] Configurer les intercepteurs HTTP
- [ ] Mettre en place la gestion d'état

### Phase 2 - Composants de Base
- [ ] Dashboard technicien
- [ ] Liste des demandes
- [ ] Détail d'une demande
- [ ] Composants de statut

### Phase 3 - Fonctionnalités Avancées
- [ ] Proposition de contrat
- [ ] Historique des propositions
- [ ] Statistiques et rapports
- [ ] Notifications temps réel

### Phase 4 - Optimisations
- [ ] Tests unitaires et intégration
- [ ] Optimisation des performances
- [ ] Responsive design
- [ ] Accessibilité

## 📝 NOTES IMPORTANTES

1. **Performance :** Utiliser la pagination pour les listes longues
2. **UX :** Feedback visuel pour toutes les actions utilisateur
3. **Sécurité :** Validation côté client ET serveur
4. **Maintenance :** Code modulaire et bien documenté
5. **Accessibilité :** Support des lecteurs d'écran

## 🔗 ENDPOINTS API PRINCIPAUX

```typescript
// Demandes d'adhésion
GET    /api/v1/demandes-adhesions                    // Liste des demandes
GET    /api/v1/demandes-adhesions/{id}              // Détail d'une demande
POST   /api/v1/demandes-adhesions/{id}/proposer-contrat  // Proposer contrat
PUT    /api/v1/demandes-adhesions/{id}/valider-client    // Valider demande
PUT    /api/v1/demandes-adhesions/{id}/rejeter          // Rejeter demande

// Propositions de contrats
GET    /api/v1/propositions-contrats                // Historique propositions
GET    /api/v1/propositions-contrats/{id}           // Détail proposition
PUT    /api/v1/propositions-contrats/{id}           // Modifier proposition

// Types de contrats
GET    /api/v1/types-contrats                       // Liste des contrats
GET    /api/v1/types-contrats/{id}                  // Détail contrat

// Statistiques
GET    /api/v1/technicien/stats                     // Statistiques technicien
```

---

**🎯 Objectif :** Créer une interface intuitive et performante permettant aux techniciens de gérer efficacement les propositions de contrats avec un workflow fluide et des notifications en temps réel.
