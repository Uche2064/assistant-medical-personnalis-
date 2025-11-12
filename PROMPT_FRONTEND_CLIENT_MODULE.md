# 🎯 PROMPT COMPLET FRONTEND - MODULE CLIENT

## 📋 CONTEXTE ET OBJECTIF

Vous devez développer une interface utilisateur complète pour le module Client d'un système d'assurance. Cette interface doit gérer deux types de clients :

1. **Clients Particuliers (Physiques)** : Personnes physiques avec possibilité d'ajouter des bénéficiaires
2. **Clients Entreprises (Moraux)** : Entreprises avec gestion des employés via liens d'invitation

## 🔄 FLUX PRINCIPAL D'UTILISATION

### Phase 1 : Authentification et Vérification d'État
1. **Connexion** : Le client se connecte avec ses identifiants
2. **Vérification Demande** : Le système vérifie automatiquement l'état de sa demande d'adhésion
3. **Affichage Conditionnel** : 
   - Si aucune demande : Afficher un bouton central "Faire ma demande maintenant"
   - Si demande en cours : Afficher l'état de la demande (en attente, validée, rejetée, etc.)
   - Si contrat conclu : Afficher le dashboard complet avec contrats et bénéficiaires

### Phase 2 : Processus de Demande d'Adhésion

#### Pour Clients Particuliers :
1. **Étape 1** : Chargement et affichage des questions destinées aux clients
2. **Étape 2** : Option d'ajout de bénéficiaires (conjoint, enfants, etc.)
3. **Étape 3** : Récapitulatif et soumission
4. **Étape 4** : Affichage de l'état de la demande

#### Pour Clients Entreprises :
1. **Étape 1** : Génération d'un lien d'invitation pour les employés
2. **Étape 2** : Les employés soumettent leurs fiches via le lien (sans authentification)
3. **Étape 3** : Le responsable soumet la demande d'adhésion groupe
4. **Étape 4** : Affichage de l'état de la demande

### Phase 3 : Gestion Post-Adhésion
- Gestion des bénéficiaires (CRUD)
- Consultation des contrats proposés et acceptés
- Gestion du réseau de prestataires
- Statistiques et profil

## 🏗️ STRUCTURE DE L'APPLICATION

### 📁 Architecture des Composants

```
src/
├── components/
│   ├── auth/
│   │   ├── LoginForm.vue
│   │   └── AuthGuard.vue
│   ├── dashboard/
│   │   ├── ClientDashboard.vue
│   │   ├── DemandeStatusCard.vue
│   │   └── QuickActions.vue
│   ├── demande-adhesion/
│   │   ├── DemandeFlow.vue
│   │   ├── QuestionsStep.vue
│   │   ├── BeneficiairesStep.vue
│   │   ├── RecapStep.vue
│   │   ├── EntrepriseFlow.vue
│   │   └── InvitationLinkGenerator.vue
│   ├── beneficiaires/
│   │   ├── BeneficiairesList.vue
│   │   ├── BeneficiaireCard.vue
│   │   ├── AddBeneficiaireForm.vue
│   │   └── EditBeneficiaireForm.vue
│   ├── contrats/
│   │   ├── ContratsList.vue
│   │   ├── ContratCard.vue
│   │   ├── PropositionsList.vue
│   │   └── ContratDetails.vue
│   ├── prestataires/
│   │   ├── PrestatairesList.vue
│   │   └── PrestataireCard.vue
│   ├── entreprise/
│   │   ├── EmployesList.vue
│   │   ├── InvitationManager.vue
│   │   └── EntrepriseStats.vue
│   └── common/
│       ├── LoadingSpinner.vue
│       ├── ErrorMessage.vue
│       ├── SuccessMessage.vue
│       └── ConfirmDialog.vue
├── views/
│   ├── LoginView.vue
│   ├── DashboardView.vue
│   ├── DemandeAdhesionView.vue
│   ├── BeneficiairesView.vue
│   ├── ContratsView.vue
│   ├── PrestatairesView.vue
│   ├── ProfilView.vue
│   └── EntrepriseView.vue
├── stores/
│   ├── auth.js
│   ├── demande.js
│   ├── beneficiaires.js
│   ├── contrats.js
│   └── entreprise.js
├── services/
│   ├── api.js
│   ├── authService.js
│   ├── demandeService.js
│   ├── beneficiairesService.js
│   └── contratsService.js
└── utils/
    ├── constants.js
    ├── validators.js
    └── formatters.js
```

## 🎨 INTERFACE UTILISATEUR

### 🏠 Dashboard Principal

```vue
<template>
  <div class="dashboard">
    <!-- Header avec profil utilisateur -->
    <header class="dashboard-header">
      <div class="user-info">
        <img :src="user.photo_url" :alt="user.nom" class="avatar">
        <div class="user-details">
          <h2>{{ user.nom }} {{ user.prenoms }}</h2>
          <p class="user-role">{{ user.role }}</p>
        </div>
      </div>
      <button @click="logout" class="logout-btn">Déconnexion</button>
    </header>

    <!-- Contenu conditionnel selon l'état de la demande -->
    <main class="dashboard-content">
      <!-- Si aucune demande d'adhésion -->
      <div v-if="demandeStatus === 'none'" class="no-demand">
        <div class="cta-card">
          <h3>🎯 Commencez votre assurance</h3>
          <p>Protégez-vous et vos proches avec nos solutions d'assurance adaptées</p>
          <button @click="startDemande" class="btn-primary btn-large">
            Faire ma demande maintenant
          </button>
        </div>
      </div>

      <!-- Si demande en cours -->
      <div v-else-if="demandeStatus === 'pending'" class="demande-pending">
        <DemandeStatusCard :demande="demande" />
      </div>

      <!-- Si contrat conclu -->
      <div v-else class="dashboard-grid">
        <div class="stats-cards">
          <StatCard title="Bénéficiaires" :value="stats.total_beneficiaires" icon="👥" />
          <StatCard title="Contrats Actifs" :value="stats.contrats_actifs" icon="📄" />
          <StatCard title="Prestataires" :value="stats.prestataires" icon="🏥" />
        </div>
        
        <div class="quick-actions">
          <QuickActions :user-type="user.type_client" />
        </div>

        <div class="recent-activity">
          <RecentActivity />
        </div>
      </div>
    </main>
  </div>
</template>
```

### 📋 Flux de Demande d'Adhésion

```vue
<template>
  <div class="demande-flow">
    <!-- Progress Bar -->
    <div class="progress-bar">
      <div 
        v-for="(step, index) in steps" 
        :key="index"
        :class="['step', { active: currentStep === index, completed: currentStep > index }]"
      >
        <div class="step-number">{{ index + 1 }}</div>
        <div class="step-label">{{ step.label }}</div>
      </div>
    </div>

    <!-- Contenu des étapes -->
    <div class="step-content">
      <!-- Étape 1: Questions -->
      <QuestionsStep 
        v-if="currentStep === 0"
        :questions="questions"
        @next="handleQuestionsNext"
        @previous="handlePrevious"
      />

      <!-- Étape 2: Bénéficiaires (optionnel) -->
      <BeneficiairesStep 
        v-if="currentStep === 1"
        :beneficiaires="beneficiaires"
        @add="addBeneficiaire"
        @remove="removeBeneficiaire"
        @next="handleBeneficiairesNext"
        @previous="handlePrevious"
      />

      <!-- Étape 3: Récapitulatif -->
      <RecapStep 
        v-if="currentStep === 2"
        :data="demandeData"
        @submit="submitDemande"
        @previous="handlePrevious"
      />
    </div>
  </div>
</template>
```

### 👥 Gestion des Bénéficiaires

```vue
<template>
  <div class="beneficiaires-management">
    <div class="header">
      <h2>Gestion des Bénéficiaires</h2>
      <button @click="showAddForm = true" class="btn-primary">
        + Ajouter un bénéficiaire
      </button>
    </div>

    <div class="beneficiaires-grid">
      <BeneficiaireCard 
        v-for="beneficiaire in beneficiaires" 
        :key="beneficiaire.id"
        :beneficiaire="beneficiaire"
        @edit="editBeneficiaire"
        @delete="deleteBeneficiaire"
      />
    </div>

    <!-- Formulaire d'ajout/modification -->
    <AddBeneficiaireForm 
      v-if="showAddForm"
      :beneficiaire="editingBeneficiaire"
      @save="saveBeneficiaire"
      @cancel="cancelEdit"
    />
  </div>
</template>
```

### 🏢 Module Entreprise

```vue
<template>
  <div class="entreprise-module">
    <!-- Gestion des invitations -->
    <div class="invitation-section">
      <h3>Inviter des Employés</h3>
      <div class="invitation-link">
        <input 
          :value="invitationLink" 
          readonly 
          class="link-input"
        >
        <button @click="copyLink" class="btn-secondary">Copier</button>
        <button @click="generateNewLink" class="btn-primary">Nouveau lien</button>
      </div>
    </div>

    <!-- Liste des employés -->
    <div class="employes-section">
      <h3>Employés Inscrits</h3>
      <div class="employes-grid">
        <EmployeCard 
          v-for="employe in employes" 
          :key="employe.id"
          :employe="employe"
        />
      </div>
    </div>

    <!-- Actions -->
    <div class="actions-section">
      <button 
        v-if="canSubmitDemande"
        @click="submitEntrepriseDemande"
        class="btn-primary btn-large"
      >
        Soumettre la demande d'adhésion
      </button>
    </div>
  </div>
</template>
```

## 🔧 LOGIQUE MÉTIER

### 🎯 États de Demande d'Adhésion

```javascript
const DEMANDE_STATES = {
  NONE: 'none',           // Aucune demande
  EN_ATTENTE: 'en_attente', // En attente de traitement
  VALIDEE: 'validee',     // Validée par le technicien
  PROPOSEE: 'proposee',   // Contrat proposé
  ACCEPTEE: 'acceptee',   // Contrat accepté
  REJETEE: 'rejetee',     // Demande rejetée
  CONTRAT_CONCLU: 'contrat_conclu' // Contrat finalisé
};
```

### 📊 Gestion des Données

```javascript
// Store Pinia pour la demande d'adhésion
export const useDemandeStore = defineStore('demande', {
  state: () => ({
    status: null,
    demande: null,
    questions: [],
    reponses: [],
    beneficiaires: [],
    loading: false,
    error: null
  }),

  actions: {
    async checkDemandeStatus() {
      try {
        this.loading = true;
        const response = await demandeService.hasDemande();
        this.status = response.data.status;
        this.demande = response.data.demande;
      } catch (error) {
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async loadQuestions(destinataire) {
      const response = await demandeService.getQuestions(destinataire);
      this.questions = response.data;
    },

    async submitDemande(data) {
      try {
        this.loading = true;
        await demandeService.submitDemande(data);
        await this.checkDemandeStatus(); // Recharger l'état
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
```

### 🔄 Services API

```javascript
// Service pour les demandes d'adhésion
export class DemandeService {
  async hasDemande() {
    return await api.get('/demandes-adhesions/has-demande');
  }

  async getQuestions(destinataire) {
    return await api.get(`/questions?destinataire=${destinataire}`);
  }

  async submitDemande(data) {
    const formData = new FormData();
    
    // Ajouter les données de base
    formData.append('type_demandeur', data.type_demandeur);
    
    // Ajouter les réponses
    data.reponses.forEach((reponse, index) => {
      formData.append(`reponses[${index}][question_id]`, reponse.question_id);
      formData.append(`reponses[${index}][reponse]`, reponse.reponse);
    });
    
    // Ajouter les bénéficiaires si présents
    if (data.beneficiaires) {
      data.beneficiaires.forEach((beneficiaire, index) => {
        Object.keys(beneficiaire).forEach(key => {
          formData.append(`beneficiaires[${index}][${key}]`, beneficiaire[key]);
        });
      });
    }
    
    return await api.post('/demandes-adhesions/client', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  }
}
```

## 🎨 STYLES ET DESIGN

### 🎯 Palette de Couleurs

```css
:root {
  /* Couleurs principales */
  --primary-color: #2563eb;
  --primary-dark: #1d4ed8;
  --secondary-color: #64748b;
  
  /* États */
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --error-color: #ef4444;
  --info-color: #3b82f6;
  
  /* Neutres */
  --gray-50: #f8fafc;
  --gray-100: #f1f5f9;
  --gray-200: #e2e8f0;
  --gray-300: #cbd5e1;
  --gray-400: #94a3b8;
  --gray-500: #64748b;
  --gray-600: #475569;
  --gray-700: #334155;
  --gray-800: #1e293b;
  --gray-900: #0f172a;
  
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
  --spacing-2xl: 3rem;
  
  /* Border radius */
  --radius-sm: 0.25rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --radius-xl: 1rem;
}
```

### 🎨 Composants de Base

```css
/* Boutons */
.btn {
  padding: var(--spacing-sm) var(--spacing-md);
  border-radius: var(--radius-md);
  border: none;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
}

.btn-large {
  padding: var(--spacing-md) var(--spacing-xl);
  font-size: 1.125rem;
}

/* Cards */
.card {
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: var(--spacing-lg);
}

/* Status badges */
.status-badge {
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 500;
}

.status-en-attente {
  background-color: #fef3c7;
  color: #92400e;
}

.status-validee {
  background-color: #d1fae5;
  color: #065f46;
}

.status-rejetee {
  background-color: #fee2e2;
  color: #991b1b;
}
```

## 📱 RESPONSIVE DESIGN

```css
/* Mobile First */
.dashboard-grid {
  display: grid;
  gap: var(--spacing-md);
  grid-template-columns: 1fr;
}

/* Tablet */
@media (min-width: 768px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Desktop */
@media (min-width: 1024px) {
  .dashboard-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Large Desktop */
@media (min-width: 1280px) {
  .dashboard-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}
```

## 🔐 GESTION DES ERREURS

```javascript
// Intercepteur global pour la gestion des erreurs
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Rediriger vers la page de connexion
      router.push('/login');
    } else if (error.response?.status === 403) {
      // Afficher un message d'erreur d'autorisation
      showNotification('Accès non autorisé', 'error');
    } else if (error.response?.status >= 500) {
      // Erreur serveur
      showNotification('Erreur serveur. Veuillez réessayer plus tard.', 'error');
    }
    
    return Promise.reject(error);
  }
);
```

## 📊 VALIDATION ET FORMATAGE

```javascript
// Validateurs
export const validators = {
  email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
  phone: (value) => /^\+225[0-9]{9}$/.test(value),
  required: (value) => value && value.trim().length > 0,
  date: (value) => !isNaN(Date.parse(value)),
  file: (file, maxSize = 5 * 1024 * 1024) => file && file.size <= maxSize
};

// Formatters
export const formatters = {
  currency: (amount) => new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF'
  }).format(amount),
  
  date: (date) => new Intl.DateTimeFormat('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(new Date(date)),
  
  phone: (phone) => phone?.replace(/(\+225)(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5')
};
```

## 🚀 FONCTIONNALITÉS AVANCÉES

### 🔄 Synchronisation en Temps Réel

```javascript
// WebSocket pour les notifications en temps réel
export class NotificationService {
  constructor() {
    this.socket = null;
    this.listeners = new Map();
  }

  connect(token) {
    this.socket = new WebSocket(`ws://localhost:6001?token=${token}`);
    
    this.socket.onmessage = (event) => {
      const data = JSON.parse(event.data);
      this.notifyListeners(data.type, data.payload);
    };
  }

  on(type, callback) {
    if (!this.listeners.has(type)) {
      this.listeners.set(type, []);
    }
    this.listeners.get(type).push(callback);
  }

  notifyListeners(type, payload) {
    const callbacks = this.listeners.get(type) || [];
    callbacks.forEach(callback => callback(payload));
  }
}
```

### 📱 Mode Hors Ligne

```javascript
// Service Worker pour le cache
export class OfflineService {
  async cacheRequest(url, response) {
    const cache = await caches.open('api-cache');
    await cache.put(url, response);
  }

  async getCachedRequest(url) {
    const cache = await caches.open('api-cache');
    return await cache.match(url);
  }

  async syncWhenOnline() {
    if (navigator.onLine) {
      // Synchroniser les données en attente
      await this.syncPendingRequests();
    }
  }
}
```

## 📋 CHECKLIST DE DÉVELOPPEMENT

### ✅ Phase 1 : Authentification et Dashboard
- [ ] Page de connexion avec validation
- [ ] Vérification automatique de l'état de la demande
- [ ] Dashboard conditionnel selon l'état
- [ ] Gestion des tokens JWT

### ✅ Phase 2 : Flux de Demande d'Adhésion
- [ ] Chargement dynamique des questions
- [ ] Formulaire multi-étapes avec validation
- [ ] Gestion des bénéficiaires (ajout/modification)
- [ ] Upload de fichiers (photos, documents)
- [ ] Récapitulatif avant soumission

### ✅ Phase 3 : Module Entreprise
- [ ] Générateur de liens d'invitation
- [ ] Interface publique pour les employés
- [ ] Gestion des employés inscrits
- [ ] Soumission de demande groupe

### ✅ Phase 4 : Gestion Post-Adhésion
- [ ] CRUD des bénéficiaires
- [ ] Consultation des contrats
- [ ] Gestion des propositions
- [ ] Réseau de prestataires

### ✅ Phase 5 : Optimisations
- [ ] Design responsive
- [ ] Gestion des erreurs
- [ ] Loading states
- [ ] Notifications
- [ ] Performance et cache

## 🎯 POINTS D'ATTENTION

1. **UX/UI** : Interface intuitive avec feedback visuel constant
2. **Performance** : Chargement rapide et navigation fluide
3. **Sécurité** : Validation côté client ET serveur
4. **Accessibilité** : Support des lecteurs d'écran et navigation clavier
5. **Internationalisation** : Support multilingue (français/anglais)
6. **Tests** : Tests unitaires et d'intégration

Cette documentation fournit une base solide pour développer une interface client complète et professionnelle pour le système d'assurance.
