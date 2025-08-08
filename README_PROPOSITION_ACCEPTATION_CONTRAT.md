# Guide d'Intégration - Proposition et Acceptation de Contrats

## 📋 Vue d'ensemble

Ce guide décrit l'intégration du workflow simplifié de proposition et acceptation de contrats dans SUNU Santé.

### Workflow
1. **Technicien** propose un contrat en choisissant le type
2. **Client** reçoit une notification et peut accepter/refuser
3. **Système** utilise automatiquement la prime standard du contrat

---

## 🔧 Configuration de base

### URL de base
```
https://api.sunusante.com/v1
```

### Headers requis
```javascript
const headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer {token}',
    'X-API-Key': 'votre_api_key'
};
```

---

## 📋 Proposition de contrat (Technicien)

### Endpoint
```javascript
PUT /demandes-adhesions/{demande_id}/proposer-contrat
```

### Body de la requête
```json
{
    "type_contrat": "standard",
    "commentaires": "Contrat standard recommandé pour votre profil"
}
```

### Types de contrat disponibles
- `"basic"` - Contrat basique
- `"standard"` - Contrat standard  
- `"premium"` - Contrat premium
- `"team"` - Contrat équipe

### Exemple JavaScript
```javascript
// Configuration Axios
const api = axios.create({
    baseURL: 'https://api.sunusante.com/v1',
    headers: {
        'Content-Type': 'application/json',
        'X-API-Key': 'votre_api_key',
        'Authorization': `Bearer ${token}`
    }
});

// Proposer un contrat
const proposerContrat = async (demandeId, typeContrat, commentaires) => {
    try {
        const response = await api.put(`/demandes-adhesions/${demandeId}/proposer-contrat`, {
            type_contrat: typeContrat,
            commentaires: commentaires
        });
        
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la proposition:', error.response?.data);
        throw error;
    }
};

// Utilisation
const resultat = await proposerContrat(123, 'standard', 'Contrat recommandé');
```

### Réponse de succès
```json
{
    "success": true,
    "message": "Proposition de contrat créée avec succès",
    "data": {
        "proposition_id": 123,
        "contrat": {
            "id": 456,
            "type_contrat": "standard",
            "prime_standard": 50000,
            "prime_standard_formatted": "50 000 FCFA"
        },
        "token": "abc123def456ghi789...",
        "expires_at": "2024-01-15T10:30:00Z"
    }
}
```

---

## ✅ Acceptation de contrat (Client)

### Méthode 1 : Avec authentification

#### Endpoint
```javascript
POST /client/contrats-proposes/{proposition_id}/accepter
```

#### Body de la requête
```json
{
    "accepte": true,
    "commentaires": "J'accepte cette proposition"
}
```

#### Exemple JavaScript
```javascript
const accepterContrat = async (propositionId, accepte, commentaires) => {
    try {
        const response = await api.post(`/client/contrats-proposes/${propositionId}/accepter`, {
            accepte: accepte,
            commentaires: commentaires
        });
        
        return response.data;
    } catch (error) {
        console.error('Erreur lors de l\'acceptation:', error.response?.data);
        throw error;
    }
};

// Utilisation
const resultat = await accepterContrat(123, true, 'J\'accepte cette proposition');
```

### Méthode 2 : Via token (sans authentification)

#### Endpoint
```javascript
POST /contrats/accepter/{token}
```

#### Body de la requête
```json
{
    "accepte": true,
    "commentaires": "J'accepte cette proposition"
}
```

#### Exemple JavaScript
```javascript
const accepterContratViaToken = async (token, accepte, commentaires) => {
    try {
        const response = await api.post(`/contrats/accepter/${token}`, {
            accepte: accepte,
            commentaires: commentaires
        });
        
        return response.data;
    } catch (error) {
        console.error('Erreur lors de l\'acceptation:', error.response?.data);
        throw error;
    }
};

// Utilisation
const resultat = await accepterContratViaToken('abc123def456ghi789...', true, 'J\'accepte');
```

### Accepter via token (sans authentification)
```javascript
POST /contrats/accepter/{token}
Content-Type: application/json

{
    "accepte": true,
    "commentaires": "J'accepte cette proposition"
}
```

### Refuser un contrat (Client)
```javascript
POST /client/contrats-proposes/{proposition_id}/refuser
Content-Type: application/json

{
    "commentaires": "Je refuse cette proposition"
}
```

### Réponse de refus
```json
{
    "success": true,
    "message": "Proposition refusée avec succès",
    "data": {
        "proposition_id": 123,
        "message": "Proposition refusée avec succès"
    }
}
```

### Réponse de succès
```json
{
    "success": true,
    "message": "Contrat accepté avec succès",
    "data": {
        "proposition_id": 123,
        "statut": "acceptee",
        "date_acceptation": "2024-01-15T10:30:00Z",
        "contrat_actif": {
            "id": 789,
            "numero_police": "POL2024001",
            "type_contrat": "standard",
            "prime_standard": 50000
        }
    }
}
```

---

## 📊 Consultation des propositions

### Lister les propositions d'une demande
```javascript
GET /demandes-adhesions/{demande_id}/propositions-contrat
```

### Détails d'une proposition spécifique
```javascript
GET /demandes-adhesions/{demande_id}/propositions-contrat/{proposition_id}
```

### Consulter les propositions
```javascript
GET /demandes-adhesions/{demande_id}/propositions-contrat
```

### Détails d'une proposition
```javascript
GET /demandes-adhesions/{demande_id}/propositions-contrat/{proposition_id}
```

### Consulter mes propositions (Client)
```javascript
GET /client/propositions-contrat
```

### Détails de ma proposition (Client)
```javascript
GET /client/propositions-contrat/{proposition_id}
```

### Exemple JavaScript
```javascript
const getPropositions = async (demandeId) => {
    try {
        const response = await api.get(`/demandes-adhesions/${demandeId}/propositions-contrat`);
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la récupération:', error.response?.data);
        throw error;
    }
};

const getPropositionDetails = async (demandeId, propositionId) => {
    try {
        const response = await api.get(`/demandes-adhesions/${demandeId}/propositions-contrat/${propositionId}`);
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la récupération:', error.response?.data);
        throw error;
    }
};
```

### Exemple : Récupérer mes propositions de contrat
```javascript
// Client récupère ses propositions
const getMesPropositions = async () => {
    try {
        const response = await api.get('/client/propositions-contrat');
        const { propositions, total, statistiques } = response.data.data;
        
        console.log('Mes propositions:', propositions);
        console.log('Statistiques:', statistiques);
        
        return propositions;
    } catch (error) {
        console.error('Erreur lors de la récupération:', error.response?.data);
        throw error;
    }
};

// Utilisation
const mesPropositions = await getMesPropositions();
```

### Exemple : Détails d'une proposition
```javascript
const getPropositionDetails = async (propositionId) => {
    try {
        const response = await api.get(`/client/propositions-contrat/${propositionId}`);
        const proposition = response.data.data;
        
        console.log('Détails de la proposition:', proposition);
        return proposition;
    } catch (error) {
        console.error('Erreur lors de la récupération:', error.response?.data);
        throw error;
    }
};
```

---

## 🎨 Interfaces utilisateur recommandées

### Interface Technicien

#### Formulaire de proposition
```vue
<template>
    <div class="proposition-form">
        <h3>Proposer un contrat</h3>
        
        <form @submit.prevent="proposerContrat">
            <div class="form-group">
                <label>Type de contrat</label>
                <select v-model="typeContrat" required>
                    <option value="">Sélectionner un type</option>
                    <option value="basic">Basique</option>
                    <option value="standard">Standard</option>
                    <option value="premium">Premium</option>
                    <option value="team">Équipe</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Commentaires (optionnel)</label>
                <textarea v-model="commentaires" rows="3" placeholder="Commentaires pour le client..."></textarea>
            </div>
            
            <button type="submit" :disabled="loading">
                {{ loading ? 'Envoi en cours...' : 'Proposer le contrat' }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const typeContrat = ref('');
const commentaires = ref('');
const loading = ref(false);

const proposerContrat = async () => {
    loading.value = true;
    try {
        const response = await api.put(`/demandes-adhesions/${demandeId}/proposer-contrat`, {
            type_contrat: typeContrat.value,
            commentaires: commentaires.value
        });
        
        // Afficher le succès
        showSuccess('Contrat proposé avec succès');
        
        // Réinitialiser le formulaire
        typeContrat.value = '';
        commentaires.value = '';
        
    } catch (error) {
        showError('Erreur lors de la proposition');
    } finally {
        loading.value = false;
    }
};
</script>
```

### Interface Client

#### Liste des propositions reçues
```vue
<template>
    <div class="propositions-list">
        <h3>Propositions de contrat reçues</h3>
        
        <div v-for="proposition in propositions" :key="proposition.id" class="proposition-card">
            <div class="proposition-header">
                <h4>{{ proposition.contrat.type_contrat_label }}</h4>
                <span class="prime">{{ proposition.contrat.prime_standard_formatted }}</span>
            </div>
            
            <div class="proposition-details">
                <p><strong>Proposé par:</strong> {{ proposition.technicien.nom_complet }}</p>
                <p><strong>Date:</strong> {{ formatDate(proposition.date_proposition) }}</p>
                <p v-if="proposition.commentaires_technicien">
                    <strong>Commentaires:</strong> {{ proposition.commentaires_technicien }}
                </p>
            </div>
            
            <div class="proposition-actions">
                <button @click="accepterContrat(proposition.id)" class="btn-accept">
                    Accepter
                </button>
                <button @click="refuserContrat(proposition.id)" class="btn-refuse">
                    Refuser
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const propositions = ref([]);
const loading = ref(false);

const getPropositions = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/demandes-adhesions/${demandeId}/propositions-contrat`);
        propositions.value = response.data.data;
    } catch (error) {
        showError('Erreur lors de la récupération');
    } finally {
        loading.value = false;
    }
};

const accepterContrat = async (propositionId) => {
    try {
        const response = await api.post(`/client/contrats-proposes/${propositionId}/accepter`, {
            accepte: true,
            commentaires: 'J\'accepte cette proposition'
        });
        
        showSuccess('Contrat accepté avec succès');
        // Rediriger vers le dashboard ou actualiser la liste
        await getPropositions();
        
    } catch (error) {
        showError('Erreur lors de l\'acceptation');
    }
};

const refuserContrat = async (propositionId) => {
    try {
        const response = await api.post(`/client/contrats-proposes/${propositionId}/accepter`, {
            accepte: false,
            commentaires: 'Je refuse cette proposition'
        });
        
        showSuccess('Contrat refusé');
        await getPropositions();
        
    } catch (error) {
        showError('Erreur lors du refus');
    }
};

onMounted(() => {
    getPropositions();
});
</script>
```

---

## 🚨 Gestion des erreurs

### Codes d'erreur courants

| Code | Message | Action recommandée |
|------|---------|-------------------|
| 400 | Demande déjà traitée | Vérifier le statut de la demande |
| 404 | Demande non trouvée | Vérifier l'ID de la demande |
| 403 | Non autorisé | Vérifier les permissions utilisateur |
| 422 | Type de contrat invalide | Vérifier la valeur du type_contrat |

### Exemple de gestion d'erreur
```javascript
const proposerContrat = async (demandeId, typeContrat, commentaires) => {
    try {
        const response = await api.put(`/demandes-adhesions/${demandeId}/proposer-contrat`, {
            type_contrat: typeContrat,
            commentaires: commentaires
        });
        
        return response.data;
        
    } catch (error) {
        const status = error.response?.status;
        const message = error.response?.data?.message;
        
        switch (status) {
            case 400:
                showError('Cette demande a déjà été traitée');
                break;
            case 404:
                showError('Demande d\'adhésion non trouvée');
                break;
            case 403:
                showError('Vous n\'êtes pas autorisé à proposer des contrats');
                break;
            case 422:
                showError('Type de contrat invalide');
                break;
            default:
                showError('Erreur lors de la proposition de contrat');
        }
        
        throw error;
    }
};
```

---

## 📱 Notifications

### Notification de nouvelle proposition
```javascript
// Écouter les nouvelles notifications
const checkNotifications = async () => {
    try {
        const response = await api.get('/notifications?type=contrat_propose');
        const notifications = response.data.data;
        
        notifications.forEach(notification => {
            if (notification.type === 'contrat_propose') {
                showNotification({
                    title: 'Nouvelle proposition de contrat',
                    message: notification.message,
                    type: 'info',
                    action: () => navigateToPropositions()
                });
            }
        });
    } catch (error) {
        console.error('Erreur lors de la vérification des notifications');
    }
};

// Vérifier toutes les 30 secondes
setInterval(checkNotifications, 30000);
```

---

## 🔧 Configuration

### Variables d'environnement
```env
VITE_API_BASE_URL=https://api.sunusante.com/v1
VITE_API_KEY=votre_api_key
VITE_FRONTEND_URL=https://app.sunusante.com
```

### Configuration Axios
```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'X-API-Key': import.meta.env.VITE_API_KEY
    }
});

// Intercepteur pour ajouter le token
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Intercepteur pour gérer les erreurs
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Token expiré, rediriger vers la connexion
            localStorage.removeItem('token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
```

---

## 📊 Exemple complet

### Workflow complet
```javascript
// 1. Technicien propose un contrat
const proposerContrat = async () => {
    const response = await api.put('/demandes-adhesions/123/proposer-contrat', {
        type_contrat: 'standard',
        commentaires: 'Contrat standard recommandé'
    });
    
    const { token, expires_at } = response.data.data;
    
    // 2. Envoyer l'email avec le lien d'acceptation
    const acceptationUrl = `${frontendUrl}/contrat/accepter/${token}`;
    console.log('Lien d\'acceptation:', acceptationUrl);
    
    return response.data;
};

// 3. Client accepte via le lien
const accepterViaToken = async (token) => {
    const response = await api.post(`/contrats/accepter/${token}`, {
        accepte: true,
        commentaires: 'J\'accepte cette proposition'
    });
    
    console.log('Contrat accepté:', response.data);
    return response.data;
};

// 4. Vérifier le statut
const verifierStatut = async (demandeId) => {
    const response = await api.get(`/demandes-adhesions/${demandeId}/propositions-contrat`);
    const propositions = response.data.data;
    
    const propositionAcceptee = propositions.find(p => p.statut === 'acceptee');
    if (propositionAcceptee) {
        console.log('Contrat actif:', propositionAcceptee.contrat_actif);
    }
};
```

---

## 🎯 Points clés

### ✅ Simplification
- Plus besoin de calculer les primes manuellement
- Le technicien choisit juste le type de contrat
- Le système utilise automatiquement la `prime_standard` du contrat

### ✅ Sécurité
- Tokens d'acceptation avec expiration (7 jours)
- Validation des permissions utilisateur
- Gestion des erreurs robuste

### ✅ Expérience utilisateur
- Notifications en temps réel
- Interface intuitive pour techniciens et clients
- Feedback immédiat sur les actions

---

*Dernière mise à jour : Janvier 2024* 