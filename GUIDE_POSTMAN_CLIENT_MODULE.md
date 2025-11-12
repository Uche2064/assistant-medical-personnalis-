# 📚 GUIDE D'UTILISATION - COLLECTION POSTMAN CLIENT MODULE

## 🎯 Vue d'Ensemble

Cette collection Postman contient tous les endpoints nécessaires pour tester et intégrer le module Client du système d'assurance. Elle couvre l'authentification, les demandes d'adhésion, la gestion des bénéficiaires, les contrats et le module entreprise.

## 🚀 Configuration Initiale

### 1. Variables d'Environnement

Créez un environnement Postman avec les variables suivantes :

```json
{
  "base_url": "http://127.0.0.1:8000",
  "api_key": "votre_api_key_ici",
  "auth_token": ""
}
```

### 2. Import de la Collection

1. Importez le fichier `22_Client_Module.postman_collection.json`
2. Sélectionnez l'environnement créé
3. La collection est maintenant prête à être utilisée

## 🔐 Authentification

### Connexion Client

**Endpoint :** `POST /api/v1/auth/login`

**Payload :**
```json
{
  "email": "client@example.com",
  "password": "password123"
}
```

**Réponse :**
```json
{
  "status": true,
  "message": "Connexion réussie",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "email": "client@example.com",
      "nom": "Dupont",
      "prenoms": "Jean",
      "role": "client"
    }
  }
}
```

**⚠️ Important :** Le token est automatiquement sauvegardé dans la variable `auth_token` et utilisé pour les requêtes suivantes.

## 📋 Demandes d'Adhésion

### 1. Vérifier l'État de la Demande

**Endpoint :** `GET /api/v1/demandes-adhesions/has-demande`

**Réponse si aucune demande :**
```json
{
  "status": true,
  "data": {
    "existing": false,
    "demande": null,
    "can_submit": true,
    "status": "none"
  }
}
```

**Réponse si demande existante (avec toutes les informations) :**
```json
{
  "status": true,
  "data": {
    "existing": true,
    "can_submit": false,
    "status": "en_attente",
    "demande": {
      "id": 1,
      "type_demandeur": "client",
      "statut": "en_attente",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z",
      "motif_rejet": null,
      "valider_a": null,
      "valide_par": null,
      
      "assure_principal": {
        "id": 1,
        "nom": "Dupont",
        "prenoms": "Jean",
        "date_naissance": "1985-05-15",
        "sexe": "M",
        "profession": "Ingénieur",
        "email": "jean.dupont@example.com",
        "contact": "+225123456789",
        "adresse": "Abidjan, Côte d'Ivoire",
        "photo_url": "https://example.com/photo.jpg",
        "est_principal": true,
        "lien_parente": null,
        "created_at": "2024-01-15T10:30:00Z"
      },
      
      "beneficiaires": [
        {
          "id": 2,
          "nom": "Dupont",
          "prenoms": "Marie",
          "date_naissance": "1990-03-20",
          "sexe": "F",
          "profession": "Comptable",
          "email": "marie.dupont@example.com",
          "contact": "+225987654321",
          "adresse": "Abidjan, Côte d'Ivoire",
          "photo_url": "https://example.com/marie.jpg",
          "lien_parente": "conjoint",
          "est_principal": false,
          "created_at": "2024-01-15T10:35:00Z"
        }
      ],
      
      "reponses_questions": [
        {
          "id": 1,
          "question_id": 1,
          "question": {
            "id": 1,
            "libelle": "Quel est votre revenu mensuel ?",
            "type_de_donnee": "number",
            "obligatoire": true,
            "destinataire": "client"
          },
          "reponse": "500000",
          "date_reponse": "2024-01-15T10:30:00Z",
          "user_id": 1
        }
      ],
      
      "total_beneficiaires": 2
    },
    "motif_rejet": null,
    "valider_a": null
  }
}
```

**États possibles :**
- `none` : Aucune demande
- `en_attente` : Demande en cours de traitement
- `validee` : Demande validée
- `proposee` : Contrat proposé
- `acceptee` : Contrat accepté
- `rejetee` : Demande rejetée

**Nouvelles informations incluses :**
- **assure_principal** : Informations complètes de l'assuré principal
- **beneficiaires** : Liste de tous les bénéficiaires ajoutés
- **reponses_questions** : Réponses au questionnaire de l'utilisateur connecté uniquement (pas celles des bénéficiaires)
- **total_beneficiaires** : Nombre total de bénéficiaires (principal + secondaires)

**⚠️ Important :** Les `reponses_questions` retournées sont uniquement celles de l'utilisateur connecté pour cette demande. Les réponses des bénéficiaires ne sont pas incluses dans cette réponse. Chaque bénéficiaire a ses propres réponses liées à son `user_id`.

### 2. Récupérer les Questions

**Endpoint :** `GET /api/v1/questions?destinataire=client`

**Réponse :**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "libelle": "Quel est votre revenu mensuel ?",
      "type_de_donnee": "number",
      "obligatoire": true,
      "destinataire": "client"
    }
  ]
}
```

### 3. Soumettre une Demande Client Physique

**Endpoint :** `POST /api/v1/demandes-adhesions/client`

**Content-Type :** `multipart/form-data`

**Données du formulaire :**

| Clé | Valeur | Type |
|-----|--------|------|
| `type_demandeur` | `client` | text |
| `reponses[0][question_id]` | `1` | text |
| `reponses[0][reponse]` | `Réponse au questionnaire` | text |
| `beneficiaires[0][nom]` | `Dupont` | text |
| `beneficiaires[0][prenoms]` | `Marie` | text |
| `beneficiaires[0][date_naissance]` | `1990-05-15` | text |
| `beneficiaires[0][sexe]` | `F` | text |
| `beneficiaires[0][lien_parente]` | `conjoint` | text |
| `beneficiaires[0][email]` | `marie.dupont@example.com` | text |
| `beneficiaires[0][contact]` | `+225123456789` | text |
| `beneficiaires[0][profession]` | `Ingénieur` | text |
| `beneficiaires[0][photo_url]` | `[FICHIER]` | file |

**Réponse :**
```json
{
  "status": true,
  "message": "Demande d'adhésion soumise avec succès."
}
```

## 👥 Gestion des Bénéficiaires

### 1. Lister les Bénéficiaires

**Endpoint :** `GET /api/v1/client/beneficiaires`

**Réponse :**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "nom": "Dupont",
      "prenoms": "Marie",
      "date_naissance": "1990-05-15",
      "sexe": "F",
      "lien_parente": "conjoint",
      "profession": "Ingénieur",
      "email": "marie.dupont@example.com",
      "contact": "+225123456789",
      "est_principal": false
    }
  ]
}
```

### 2. Ajouter un Bénéficiaire

**Endpoint :** `POST /api/v1/client/beneficiaires`

**Payload :**
```json
{
  "nom": "Martin",
  "prenoms": "Pierre",
  "date_naissance": "1985-03-20",
  "sexe": "M",
  "lien_parente": "enfant",
  "email": "pierre.martin@example.com",
  "contact": "+225987654321",
  "profession": "Étudiant",
  "adresse": "Abidjan, Côte d'Ivoire"
}
```

### 3. Modifier un Bénéficiaire

**Endpoint :** `PUT /api/v1/client/beneficiaires/{id}`

**Payload :**
```json
{
  "nom": "Martin",
  "prenoms": "Pierre Jean",
  "profession": "Ingénieur",
  "contact": "+225987654321"
}
```

### 4. Supprimer un Bénéficiaire

**Endpoint :** `DELETE /api/v1/client/beneficiaires/{id}`

## 📄 Gestion des Contrats

### 1. Mes Contrats

**Endpoint :** `GET /api/v1/client/mes-contrats`

**Paramètres optionnels :**
- `per_page` : Nombre d'éléments par page (défaut: 15)
- `page` : Numéro de page (défaut: 1)
- `statut` : Filtrer par statut
- `date_debut` : Date de début
- `date_fin` : Date de fin

### 2. Contrats Proposés

**Endpoint :** `GET /api/v1/client/contrats-proposes`

**Réponse :**
```json
{
  "status": true,
  "data": [
    {
      "proposition_id": 1,
      "categories_garanties": [
        {
          "id": 1,
          "libelle": "Soins de santé",
          "couverture": 500000,
          "couverture_formatted": "500 000 FCFA",
          "garanties": [
            {
              "id": 1,
              "libelle": "Consultation médicale",
              "plafond": 10000,
              "prix_standard": 5000
            }
          ]
        }
      ],
      "details_proposition": {
        "prime_proposee": 25000,
        "prime_proposee_formatted": "25 000 FCFA",
        "taux_couverture": 80
      }
    }
  ]
}
```

### 3. Accepter un Contrat

**Endpoint :** `POST /api/v1/client/contrats-proposes/{proposition_id}/accepter`

**Payload :** `{}` (vide)

### 4. Refuser un Contrat

**Endpoint :** `POST /api/v1/client/contrats-proposes/{proposition_id}/refuser`

**Payload :**
```json
{
  "raison_refus": "Le contrat ne correspond pas à mes besoins actuels"
}
```

## 🏥 Prestataires et Réseau

### 1. Mes Prestataires

**Endpoint :** `GET /api/v1/client/prestataires`

**Réponse :**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "nom": "Clinique du Plateau",
      "type_prestataire": "clinique",
      "adresse": "Abidjan, Côte d'Ivoire",
      "contact": "+225123456789"
    }
  ]
}
```

### 2. Historique des Remboursements

**Endpoint :** `GET /api/v1/client/historique-remboursements`

## 📊 Statistiques et Profil

### 1. Statistiques Client

**Endpoint :** `GET /api/v1/client/stats`

**Réponse :**
```json
{
  "status": true,
  "data": {
    "resume": {
      "total_beneficiaires": 3,
      "assure_principal": {
        "nom": "Dupont Jean",
        "age": 35,
        "sexe": "M",
        "profession": "Ingénieur"
      },
      "nombre_beneficiaires_secondaires": 2
    },
    "repartition_sexe": {
      "hommes": { "nombre": 2, "pourcentage": 66.67 },
      "femmes": { "nombre": 1, "pourcentage": 33.33 }
    }
  }
}
```

### 2. Profil Client

**Endpoint :** `GET /api/v1/client/profil`

### 3. Modifier le Profil

**Endpoint :** `PUT /api/v1/client/profil`

**Payload :**
```json
{
  "contact": "+225123456789",
  "adresse": "Nouvelle adresse, Abidjan"
}
```

## 🏢 Module Entreprise

### 1. Générer un Lien d'Invitation

**Endpoint :** `GET /api/v1/client/entreprise/generer-lien-invitation`

**Réponse :**
```json
{
  "status": true,
  "data": {
    "token": "abc123def456",
    "lien": "http://127.0.0.1:8000/api/v1/employes/formulaire/abc123def456",
    "expires_at": "2024-12-31T23:59:59Z"
  }
}
```

### 2. Lien d'Invitation Actuel

**Endpoint :** `GET /api/v1/client/entreprise/lien-invitation`

### 3. Soumettre la Demande Entreprise

**Endpoint :** `POST /api/v1/client/entreprise/soumettre-demande-adhesion`

**Prérequis :** Les employés doivent avoir soumis leurs fiches

### 4. Mes Demandes Entreprise

**Endpoint :** `GET /api/v1/client/entreprise/mes-demandes`

### 5. Demandes des Employés

**Endpoint :** `GET /api/v1/client/entreprise/demandes-employes`

### 6. Statistiques des Employés

**Endpoint :** `GET /api/v1/client/entreprise/statistiques-employes`

## 👨‍💼 Formulaire Employé (Public)

### 1. Afficher le Formulaire

**Endpoint :** `GET /api/v1/employes/formulaire/{token}`

**⚠️ Important :** Cette requête ne nécessite PAS d'authentification

### 2. Soumettre la Fiche Employé

**Endpoint :** `POST /api/v1/employes/formulaire/{token}`

**Content-Type :** `multipart/form-data`

**Données du formulaire :**

| Clé | Valeur | Type |
|-----|--------|------|
| `nom` | `Kouassi` | text |
| `prenoms` | `Jean` | text |
| `date_naissance` | `1980-12-10` | text |
| `sexe` | `M` | text |
| `profession` | `Comptable` | text |
| `email` | `jean.kouassi@example.com` | text |
| `contact` | `+225555123456` | text |
| `adresse` | `Abidjan, Côte d'Ivoire` | text |
| `photo` | `[FICHIER]` | file |
| `reponses[0][question_id]` | `1` | text |
| `reponses[0][reponse]` | `Réponse employé` | text |

## 🧪 Tests Automatisés

### Tests Inclus dans la Collection

Chaque requête inclut des tests automatisés :

```javascript
// Test de statut HTTP
pm.test('Status code is 200', function () {
    pm.response.to.have.status(200);
});

// Test de structure de réponse
pm.test('Response has correct structure', function () {
    const response = pm.response.json();
    pm.expect(response).to.have.property('status');
    pm.expect(response).to.have.property('data');
});

// Test de données spécifiques
pm.test('User data is present', function () {
    const response = pm.response.json();
    pm.expect(response.data).to.have.property('user');
    pm.expect(response.data.user).to.have.property('email');
});
```

### Exécution des Tests

1. **Test individuel :** Cliquez sur "Send" puis consultez l'onglet "Test Results"
2. **Test de collection :** Utilisez le "Collection Runner" pour exécuter tous les tests
3. **Test automatisé :** Configurez Newman pour l'intégration CI/CD

## 🔧 Gestion des Erreurs

### Codes d'Erreur Courants

| Code | Signification | Action |
|------|---------------|--------|
| 400 | Requête invalide | Vérifier les données envoyées |
| 401 | Non authentifié | Se reconnecter |
| 403 | Accès refusé | Vérifier les permissions |
| 422 | Erreur de validation | Corriger les données |
| 500 | Erreur serveur | Contacter l'administrateur |

### Exemple de Gestion d'Erreur

```javascript
pm.test('Handle validation errors', function () {
    if (pm.response.code === 422) {
        const response = pm.response.json();
        pm.expect(response).to.have.property('errors');
        console.log('Validation errors:', response.errors);
    }
});
```

## 📝 Bonnes Pratiques

### 1. Gestion des Tokens
- Le token est automatiquement géré par la collection
- Il expire après 24h, nécessitant une reconnexion
- Utilisez l'endpoint `/auth/me` pour vérifier la validité

### 2. Upload de Fichiers
- Utilisez `multipart/form-data` pour les fichiers
- Limite de taille : 5MB pour les images
- Formats acceptés : JPEG, PNG, JPG, PDF

### 3. Pagination
- Utilisez les paramètres `per_page` et `page`
- Limite par défaut : 15 éléments
- Limite maximale : 100 éléments

### 4. Filtres et Recherche
- Utilisez les paramètres de requête pour filtrer
- Les dates doivent être au format ISO 8601
- Les statuts sont sensibles à la casse

## 🚀 Intégration

### Frontend (Vue.js/React)

```javascript
// Exemple d'intégration avec Axios
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api/v1',
  headers: {
    'X-API-Key': 'votre_api_key',
    'Authorization': `Bearer ${token}`
  }
});

// Utilisation
const response = await api.get('/demandes-adhesions/has-demande');
```

### Mobile (Flutter/React Native)

```dart
// Exemple Flutter
final response = await http.get(
  Uri.parse('$baseUrl/demandes-adhesions/has-demande'),
  headers: {
    'X-API-Key': apiKey,
    'Authorization': 'Bearer $token',
  },
);
```

Cette collection Postman fournit une base complète pour tester et intégrer toutes les fonctionnalités du module Client.
