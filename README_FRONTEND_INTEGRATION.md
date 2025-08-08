# Guide d'Intégration Frontend - API Backend SUNU Santé

## 📋 Table des matières
1. [Configuration de base](#configuration-de-base)
2. [Authentification](#authentification)
3. [Gestion des demandes d'adhésion](#gestion-des-demandes-dadhésion)
4. [Proposition et acceptation de contrats](#proposition-et-acceptation-de-contrats)
5. [Gestion des catégories de garanties](#gestion-des-catégories-de-garanties)
6. [Gestion des contrats](#gestion-des-contrats)
7. [Notifications](#notifications)
8. [Gestion des fichiers](#gestion-des-fichiers)
9. [Codes d'erreur](#codes-derreur)
10. [Exemples d'utilisation](#exemples-dutilisation)

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

### Configuration Axios
```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: 'https://api.sunusante.com/v1',
    headers: {
        'Content-Type': 'application/json',
        'X-API-Key': 'votre_api_key'
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
```

---

## 🔐 Authentification

### Inscription
```javascript
POST /auth/register
Content-Type: application/json

{
    "nom": "Dupont",
    "prenoms": "Jean",
    "email": "jean.dupont@email.com",
    "telephone": "+221701234567",
    "password": "motdepasse123",
    "password_confirmation": "motdepasse123",
    "type_demandeur": "physique"
}
```

### Connexion
```javascript
POST /auth/login
Content-Type: application/json

{
    "email": "jean.dupont@email.com",
    "password": "motdepasse123"
}
```

### Réponse de connexion
```json
{
    "success": true,
    "message": "Connexion réussie",
    "data": {
        "user": {
            "id": 1,
            "nom": "Dupont",
            "prenoms": "Jean",
            "email": "jean.dupont@email.com",
            "telephone": "+221701234567",
            "roles": ["physique"],
            "profile_complete": true
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

### Vérification OTP
```javascript
POST /auth/verify-otp
Content-Type: application/json

{
    "email": "jean.dupont@email.com",
    "otp": "123456"
}
```

---

## 📝 Gestion des demandes d'adhésion

### Créer une demande d'adhésion
```javascript
POST /demandes-adhesions
Content-Type: application/json

{
    "type_demandeur": "physique",
    "nom": "Dupont",
    "prenoms": "Jean",
    "date_naissance": "1990-01-15",
    "sexe": "M",
    "telephone": "+221701234567",
    "adresse": "123 Rue de la Paix, Dakar",
    "profession": "Ingénieur",
    "salaire_mensuel": 500000,
    "beneficiaires": [
        {
            "nom": "Dupont",
            "prenoms": "Marie",
            "date_naissance": "1992-05-20",
            "sexe": "F",
            "lien_parente": "conjoint",
            "profession": "Médecin"
        }
    ],
    "reponses_questionnaire": [
        {
            "question_id": 1,
            "reponse_text": "Oui"
        },
        {
            "question_id": 2,
            "reponse_number": 70
        }
    ]
}
```

### Consulter ses demandes
```javascript
GET /demandes-adhesions/mes-demandes?page=1&per_page=10
```

### Détails d'une demande
```javascript
GET /demandes-adhesions/{id}
```

### Télécharger le PDF
```javascript
GET /demandes-adhesions/{id}/download
```

---

## 📋 Proposition et acceptation de contrats

### Proposer un contrat (Technicien)
```javascript
PUT /demandes-adhesions/{demande_id}/proposer-contrat
Content-Type: application/json

{
    "type_contrat": "standard",
    "commentaires": "Contrat standard recommandé pour votre profil"
}
```

**Types de contrat disponibles :**
- `"basic"` - Contrat basique
- `"standard"` - Contrat standard  
- `"premium"` - Contrat premium
- `"team"` - Contrat équipe

### Réponse de proposition
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
        "token": "abc123...",
        "expires_at": "2024-01-15T10:30:00Z"
    }
}
```

### Consulter les propositions
```javascript
GET /demandes-adhesions/{demande_id}/propositions-contrat
```

### Détails d'une proposition
```javascript
GET /demandes-adhesions/{demande_id}/propositions-contrat/{proposition_id}
```

### Accepter un contrat (Client)
```javascript
POST /client/contrats-proposes/{proposition_id}/accepter
Content-Type: application/json

{
    "accepte": true,
    "commentaires": "J'accepte cette proposition"
}
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

---

## 🏥 Gestion des catégories de garanties

### Lister les catégories (Lecture)
```javascript
GET /categories-garanties?search=consultation&per_page=10
```

### Détails d'une catégorie
```javascript
GET /categories-garanties/{id}
```

### Créer une catégorie (Médecin contrôleur)
```javascript
POST /categories-garanties
Content-Type: application/json

{
    "libelle": "consultation",
    "description": "Consultations médicales"
}
```

### Modifier une catégorie
```javascript
PUT /categories-garanties/{id}
Content-Type: application/json

{
    "libelle": "consultation_medicale",
    "description": "Consultations médicales spécialisées"
}
```

### Supprimer une catégorie
```javascript
DELETE /categories-garanties/{id}
```

---

## 📄 Gestion des contrats

### Lister les contrats
```javascript
GET /contrats?type=standard&min=25000&max=75000&per_page=15
```

### Détails d'un contrat
```javascript
GET /contrats/{id}
```

### Créer un contrat (Technicien)
```javascript
POST /contrats
Content-Type: application/json

{
    "type_contrat": "standard",
    "prime_standard": 50000,
    "categories_garanties": [
        {
            "categorie_garantie_id": 1,
            "couverture": 80
        },
        {
            "categorie_garantie_id": 2,
            "couverture": 90
        }
    ]
}
```

### Modifier un contrat
```javascript
PUT /contrats/{id}
Content-Type: application/json

{
    "type_contrat": "premium",
    "prime_standard": 75000
}
```

### Supprimer un contrat
```javascript
DELETE /contrats/{id}
```

### Statistiques des contrats
```javascript
GET /contrats/stats
```

---

## 🔔 Notifications

### Lister les notifications
```javascript
GET /notifications?page=1&per_page=20
```

### Marquer comme lue
```javascript
PATCH /notifications/{id}/mark-as-read
```

### Marquer comme non lue
```javascript
PATCH /notifications/{id}/mark-as-unread
```

### Marquer toutes comme lues
```javascript
PATCH /notifications/mark-all-as-read
```

### Supprimer une notification
```javascript
DELETE /notifications/{id}
```

### Supprimer les notifications lues
```javascript
DELETE /notifications/destroy-read
```

### Statistiques des notifications
```javascript
GET /notifications/stats
```

---

## 📁 Gestion des fichiers

### Upload de fichier
```javascript
POST /upload
Content-Type: multipart/form-data

FormData:
- file: [fichier]
- type: "photo" | "document" | "questionnaire"
```

### Télécharger un fichier
```javascript
GET /download/file/{filename}
```

### Accès public aux fichiers
```javascript
GET /v1/files/{filename}
```

---

## 🚨 Codes d'erreur

### Codes HTTP
- `200` - Succès
- `201` - Créé avec succès
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Non autorisé
- `404` - Ressource non trouvée
- `422` - Erreur de validation
- `500` - Erreur serveur

### Format des erreurs
```json
{
    "success": false,
    "message": "Message d'erreur",
    "errors": {
        "field": ["Message d'erreur spécifique"]
    },
    "code": 422
}
```

---

## 📊 Exemples d'utilisation

### Exemple complet : Créer une demande d'adhésion
```javascript
// 1. Authentification
const loginResponse = await api.post('/auth/login', {
    email: 'jean.dupont@email.com',
    password: 'motdepasse123'
});

const token = loginResponse.data.data.token;
api.defaults.headers.Authorization = `Bearer ${token}`;

// 2. Créer la demande
const demandeResponse = await api.post('/demandes-adhesions', {
    type_demandeur: 'physique',
    nom: 'Dupont',
    prenoms: 'Jean',
    date_naissance: '1990-01-15',
    sexe: 'M',
    telephone: '+221701234567',
    adresse: '123 Rue de la Paix, Dakar',
    profession: 'Ingénieur',
    salaire_mensuel: 500000,
    beneficiaires: [
        {
            nom: 'Dupont',
            prenoms: 'Marie',
            date_naissance: '1992-05-20',
            sexe: 'F',
            lien_parente: 'conjoint',
            profession: 'Médecin'
        }
    ],
    reponses_questionnaire: [
        {
            question_id: 1,
            reponse_text: 'Oui'
        }
    ]
});

console.log('Demande créée:', demandeResponse.data);
```

### Exemple : Proposer un contrat
```javascript
// Technicien propose un contrat
const propositionResponse = await api.put(`/demandes-adhesions/${demandeId}/proposer-contrat`, {
    type_contrat: 'standard',
    commentaires: 'Contrat standard recommandé pour votre profil'
});

const { token, expires_at } = propositionResponse.data.data;

// Envoyer l'email avec le lien d'acceptation
const acceptationUrl = `${frontendUrl}/contrat/accepter/${token}`;
```

### Exemple : Accepter un contrat
```javascript
// Client accepte le contrat
const acceptationResponse = await api.post(`/client/contrats-proposes/${propositionId}/accepter`, {
    accepte: true,
    commentaires: 'J\'accepte cette proposition'
});

console.log('Contrat accepté:', acceptationResponse.data);
```

---

## 🔧 Configuration Frontend

### Variables d'environnement
```env
VITE_API_BASE_URL=https://api.sunusante.com/v1
VITE_API_KEY=votre_api_key
VITE_FRONTEND_URL=https://app.sunusante.com
```

### Store Vuex/Pinia
```javascript
// store/auth.js
export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token'),
        isAuthenticated: false
    }),
    
    actions: {
        async login(credentials) {
            const response = await api.post('/auth/login', credentials);
            this.token = response.data.data.token;
            this.user = response.data.data.user;
            this.isAuthenticated = true;
            localStorage.setItem('token', this.token);
        },
        
        logout() {
            this.user = null;
            this.token = null;
            this.isAuthenticated = false;
            localStorage.removeItem('token');
        }
    }
});
```

### Composant de notification
```javascript
// composants/NotificationToast.vue
<template>
    <div v-if="notification" class="notification-toast">
        <div class="notification-content">
            <h4>{{ notification.title }}</h4>
            <p>{{ notification.message }}</p>
            <button @click="markAsRead">Marquer comme lu</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const notification = ref(null);

onMounted(async () => {
    // Écouter les nouvelles notifications
    // Implémenter WebSocket ou polling
});
</script>
```

---

## 📱 Interfaces utilisateur recommandées

### Dashboard Technicien
- Liste des demandes d'adhésion en attente
- Formulaire de proposition de contrat
- Statistiques des contrats proposés
- Gestion des clients

### Dashboard Client
- Statut de sa demande d'adhésion
- Propositions de contrat reçues
- Historique des contrats
- Notifications

### Dashboard Médecin Contrôleur
- Gestion des catégories de garanties
- Validation des demandes
- Statistiques des demandes

---

## 🚀 Déploiement

### Production
```bash
# Variables d'environnement
VITE_API_BASE_URL=https://api.sunusante.com/v1
VITE_API_KEY=production_api_key
VITE_FRONTEND_URL=https://app.sunusante.com
```

### Développement
```bash
# Variables d'environnement
VITE_API_BASE_URL=http://localhost:8000/v1
VITE_API_KEY=dev_api_key
VITE_FRONTEND_URL=http://localhost:3000
```

---

## 📞 Support

Pour toute question ou problème d'intégration :
- Email : dev@sunusante.com
- Documentation API : https://api.sunusante.com/docs
- Issues : https://github.com/sunusante/frontend/issues

---

*Dernière mise à jour : Janvier 2024* 