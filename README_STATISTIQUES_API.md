# API Statistiques - Documentation Frontend

Ce document détaille toutes les routes de statistiques disponibles dans l'API pour permettre au frontend de concevoir les interfaces appropriées.

## Base URL
```
https://api.sunusante.com/api/v1
```

## Authentification
Toutes les routes nécessitent une authentification JWT. Incluez le token dans le header :
```
Authorization: Bearer {token}
```

---

## 📊 Statistiques des Gestionnaires

### Route
```
GET /admin/gestionnaires/stats
```

### Accès
- **Rôle requis** : `admin_global`
- **Middleware** : `auth:api`, `checkRole:admin_global`

### Réponse
```json
{
  "success": true,
  "message": "Statistiques des gestionnaires récupérées avec succès",
  "data": {
    "total": 15,
    "actifs": 12,
    "inactifs": 3,
    "repartition_par_sexe": {
      "M": 8,
      "F": 6,
      "Non spécifié": 1
    }
  }
}
```

### Structure des données
| Champ | Type | Description |
|-------|------|-------------|
| `total` | integer | Nombre total de gestionnaires |
| `actifs` | integer | Nombre de gestionnaires actifs |
| `inactifs` | integer | Nombre de gestionnaires inactifs |
| `repartition_par_sexe` | object | Répartition par sexe (M/F/Non spécifié) |

---

## 👥 Statistiques des Personnels

### Route
```
GET /gestionnaire/personnels/stats
```

### Accès
- **Rôle requis** : `gestionnaire`
- **Middleware** : `auth:api`, `checkRole:gestionnaire`

### Réponse
```json
{
  "success": true,
  "message": "Statistiques des personnels récupérées avec succès",
  "data": {
    "total": 45,
    "actifs": 38,
    "inactifs": 7,
    "repartition_par_role": {
      "technicien": 20,
      "medecin_controleur": 10,
      "comptable": 8,
      "commercial": 7
    },
    "repartition_par_sexe": {
      "M": 25,
      "F": 18,
      "Non spécifié": 2
    }
  }
}
```

### Structure des données
| Champ | Type | Description |
|-------|------|-------------|
| `total` | integer | Nombre total de personnels |
| `actifs` | integer | Nombre de personnels actifs |
| `inactifs` | integer | Nombre de personnels inactifs |
| `repartition_par_role` | object | Répartition par rôle (technicien, medecin_controleur, comptable, commercial) |
| `repartition_par_sexe` | object | Répartition par sexe (M/F/Non spécifié) |

---

## ❓ Statistiques des Questions

### Route
```
GET /questions/stats
```

### Accès
- **Rôle requis** : `medecin_controleur`
- **Middleware** : `auth:api`, `checkRole:medecin_controleur`

### Réponse
```json
{
  "success": true,
  "message": "Statistiques des questions récupérées avec succès",
  "data": {
    "total": 120,
    "actives": 110,
    "inactives": 10,
    "obligatoires": 85,
    "optionnelles": 35,
    "repartition_par_destinataire": {
      "prospect_physique": 50,
      "prestataire_medical": 40,
      "entreprise": 30
    },
    "repartition_par_type_donnee": {
      "text": 60,
      "select": 30,
      "number": 20,
      "date": 10
    },
    "repartition_obligatoire_par_destinataire": {
      "prospect_physique": {
        "obligatoires": 35,
        "optionnelles": 15
      },
      "prestataire_medical": {
        "obligatoires": 30,
        "optionnelles": 10
      },
      "entreprise": {
        "obligatoires": 20,
        "optionnelles": 10
      }
    }
  }
}
```

### Structure des données
| Champ | Type | Description |
|-------|------|-------------|
| `total` | integer | Nombre total de questions |
| `actives` | integer | Nombre de questions actives |
| `inactives` | integer | Nombre de questions inactives |
| `obligatoires` | integer | Nombre de questions obligatoires |
| `optionnelles` | integer | Nombre de questions optionnelles |
| `repartition_par_destinataire` | object | Répartition par type de destinataire |
| `repartition_par_type_donnee` | object | Répartition par type de donnée (text, select, number, date, etc.) |
| `repartition_obligatoire_par_destinataire` | object | Répartition obligatoire/optionnelle par destinataire |

---

## 👥 Statistiques des Clients

### Route
```
GET /clients/stats
```

### Accès
- **Rôle requis** : Tous les utilisateurs authentifiés
- **Middleware** : `auth:api`

### Réponse
```json
{
  "success": true,
  "message": "Statistiques des clients récupérées avec succès",
  "data": {
    "total": 150,
    "prospects": 45,
    "clients": 80,
    "assures": 25,
    "physiques": 120,
    "moraux": 30,
    "repartition_par_sexe": {
      "M": 85,
      "F": 60,
      "Non spécifié": 5
    },
    "repartition_par_profession": {
      "Médecin": 25,
      "Ingénieur": 20,
      "Avocat": 15,
      "Comptable": 12,
      "Enseignant": 10
    },
    "repartition_statut_par_type": {
      "physique": {
        "prospect": 35,
        "client": 70,
        "assure": 15
      },
      "moral": {
        "prospect": 10,
        "client": 10,
        "assure": 10
      }
    }
  }
}
```

### Structure des données
| Champ | Type | Description |
|-------|------|-------------|
| `total` | integer | Nombre total de clients |
| `prospects` | integer | Nombre de prospects |
| `clients` | integer | Nombre de clients actifs |
| `assures` | integer | Nombre d'assurés |
| `physiques` | integer | Nombre de clients physiques |
| `moraux` | integer | Nombre de clients moraux (entreprises) |
| `repartition_par_sexe` | object | Répartition par sexe |
| `repartition_par_profession` | object | Top 10 des professions |
| `repartition_statut_par_type` | object | Répartition statut par type de client |

---

## 🏥 Statistiques des Assurés

### Route
```
GET /assures/stats
```

### Accès
- **Rôle requis** : `admin_global`, `medecin_controleur`, `technicien`
- **Middleware** : `auth:api`, `checkRole:admin_global,medecin_controleur,technicien`

### Réponse
```json
{
  "success": true,
  "message": "Statistiques des assurés récupérées avec succès",
  "data": {
    "total": 200,
    "principaux": 80,
    "beneficiaires": 120,
    "actifs": 180,
    "inactifs": 15,
    "suspendus": 5,
    "repartition_par_sexe": {
      "M": 110,
      "F": 85,
      "Non spécifié": 5
    },
    "repartition_par_lien_parente": {
      "principal": 80,
      "conjoint": 40,
      "enfant": 60,
      "parent": 20
    },
    "repartition_par_statut": {
      "actif": 180,
      "inactif": 15,
      "suspendu": 5
    },
    "repartition_principaux_beneficiaires": {
      "principaux": 80,
      "beneficiaires": 120
    },
    "repartition_par_contrat": {
      "Contrat 1": 25,
      "Contrat 2": 30,
      "Contrat 3": 20
    }
  }
}
```

### Structure des données
| Champ | Type | Description |
|-------|------|-------------|
| `total` | integer | Nombre total d'assurés |
| `principaux` | integer | Nombre d'assurés principaux |
| `beneficiaires` | integer | Nombre de bénéficiaires |
| `actifs` | integer | Nombre d'assurés actifs |
| `inactifs` | integer | Nombre d'assurés inactifs |
| `suspendus` | integer | Nombre d'assurés suspendus |
| `repartition_par_sexe` | object | Répartition par sexe |
| `repartition_par_lien_parente` | object | Répartition par lien de parenté |
| `repartition_par_statut` | object | Répartition par statut |
| `repartition_principaux_beneficiaires` | object | Répartition principaux vs bénéficiaires |
| `repartition_par_contrat` | object | Répartition par contrat |

---

## 📋 Statistiques des Demandes d'Adhésion

### Route
```
GET /demandes-adhesions/stats
```

### Accès
- **Rôle requis** : `admin_global`, `medecin_controleur`, `technicien`
- **Middleware** : `auth:api`, `checkRole:admin_global,medecin_controleur,technicien`

### Réponse
```json
{
  "success": true,
  "message": "Statistiques des demandes d'adhésion récupérées avec succès",
  "data": {
    "total": 300,
    "en_attente": 50,
    "validees": 200,
    "rejetees": 50,
    "repartition_par_type_demandeur": {
      "physique": 150,
      "autre": 100,
      "centre_soins": 30,
      "laboratoire": 20
    },
    "repartition_par_statut": {
      "en_attente": 50,
      "validee": 200,
      "rejetee": 50
    },
    "repartition_statut_par_type": {
      "physique": {
        "en_attente": 30,
        "validee": 100,
        "rejetee": 20
      },
      "autre": {
        "en_attente": 20,
        "validee": 70,
        "rejetee": 10
      }
    },
    "demandes_par_mois": {
      "Janvier": 25,
      "Février": 30,
      "Mars": 35,
      "Avril": 40,
      "Mai": 45,
      "Juin": 50
    }
  }
}
```

### Structure des données
| Champ | Type | Description |
|-------|------|-------------|
| `total` | integer | Nombre total de demandes |
| `en_attente` | integer | Demandes en attente |
| `validees` | integer | Demandes validées |
| `rejetees` | integer | Demandes rejetées |
| `repartition_par_type_demandeur` | object | Répartition par type de demandeur |
| `repartition_par_statut` | object | Répartition par statut |
| `repartition_statut_par_type` | object | Répartition statut par type |
| `demandes_par_mois` | object | Demandes par mois (année en cours) |

---

## 🎨 Suggestions d'Interface Frontend

### 1. Dashboard Gestionnaire
- **Graphiques circulaires** pour la répartition par sexe et par rôle
- **Cartes de statistiques** pour les totaux, actifs, inactifs
- **Graphiques en barres** pour comparer les différents rôles

### 2. Dashboard Admin
- **Graphiques circulaires** pour la répartition par sexe des gestionnaires
- **Indicateurs clés** (KPIs) pour les totaux
- **Graphiques d'évolution** temporelle

### 3. Dashboard Médecin Contrôleur
- **Graphiques en barres** pour la répartition par destinataire
- **Graphiques circulaires** pour les types de données
- **Tableaux détaillés** pour la répartition obligatoire/optionnelle
- **Indicateurs de performance** pour les questions actives/inactives

### 4. Dashboard Clients
- **Graphiques circulaires** pour la répartition par sexe et par type
- **Graphiques en barres** pour les professions les plus populaires
- **Indicateurs clés** pour les prospects, clients, assurés
- **Tableaux détaillés** pour la répartition statut par type

### 5. Dashboard Assurés
- **Graphiques circulaires** pour la répartition par sexe et lien de parenté
- **Graphiques en barres** pour les statuts et contrats
- **Indicateurs clés** pour principaux vs bénéficiaires
- **Graphiques d'évolution** temporelle

### 6. Dashboard Demandes d'Adhésion
- **Graphiques en barres** pour la répartition par type et statut
- **Graphiques linéaires** pour l'évolution mensuelle
- **Indicateurs de performance** pour les taux de validation
- **Tableaux détaillés** pour la répartition croisée

---

## 📋 Codes d'erreur possibles

### 401 - Non autorisé
```json
{
  "success": false,
  "message": "Token d'authentification invalide ou manquant",
  "code": 401
}
```

### 403 - Accès interdit
```json
{
  "success": false,
  "message": "Vous n'avez pas les permissions nécessaires",
  "code": 403
}
```

### 500 - Erreur serveur
```json
{
  "success": false,
  "message": "Erreur lors de la récupération des statistiques",
  "code": 500
}
```

---

## 🔄 Mise à jour des données

Les statistiques sont calculées en temps réel à chaque requête. Aucun cache n'est implémenté pour garantir la fraîcheur des données.

---

## 📝 Notes techniques

- Toutes les routes utilisent le middleware `verifyApiKey` pour la sécurité
- Les données sont groupées et agrégées côté base de données pour de meilleures performances
- Les répartitions par sexe incluent une catégorie "Non spécifié" pour les valeurs nulles
- Les répartitions par rôle sont dynamiques selon les rôles existants dans le système

---

## 📊 Statistiques du Dashboard (Adaptées au Rôle)

### Route
```
GET /dashboard/stats
```

### Accès
- **Rôle requis** : Tous les utilisateurs authentifiés
- **Middleware** : `auth:api`
- **Données adaptées** : Selon le rôle de l'utilisateur connecté

### Réponse selon le rôle

#### Admin Global
```json
{
  "success": true,
  "message": "Statistiques du dashboard récupérées avec succès",
  "data": {
    "gestionnaires": { /* stats gestionnaires */ },
    "personnels": { /* stats personnels */ },
    "clients": { /* stats clients */ },
    "assures": { /* stats assurés */ },
    "demandes_adhesion": { /* stats toutes demandes */ },
    "questions": { /* stats questions */ },
    "garanties": { /* stats garanties */ },
    "categories_garanties": { /* stats catégories */ }
  }
}
```

#### Gestionnaire
```json
{
  "success": true,
  "message": "Statistiques du dashboard récupérées avec succès",
  "data": {
    "personnels": { /* stats personnels gérés */ },
    "clients": { /* stats clients */ }
  }
}
```

#### Technicien
```json
{
  "success": true,
  "message": "Statistiques du dashboard récupérées avec succès",
  "data": {
    "clients": { /* stats clients */ },
    "assures": { /* stats assurés */ },
    "demandes_adhesion": { /* stats demandes physique/moral */ },
    "garanties": { /* stats garanties */ },
    "categories_garanties": { /* stats catégories */ }
  }
}
```

#### Médecin Contrôleur
```json
{
  "success": true,
  "message": "Statistiques du dashboard récupérées avec succès",
  "data": {
    "clients": { /* stats clients */ },
    "assures": { /* stats assurés */ },
    "questions": { /* stats questions */ },
    "garanties": { /* stats garanties */ },
    "categories_garanties": { /* stats catégories */ },
    "demandes_adhesion": { /* stats demandes prestataires */ }
  }
}
```

### Accès par rôle

| Rôle | Gestionnaires | Personnels | Clients | Assurés | Demandes | Questions | Garanties | Catégories |
|------|---------------|------------|---------|---------|----------|-----------|-----------|------------|
| **Admin Global** | ✅ | ✅ | ✅ | ✅ | ✅ (Toutes) | ✅ | ✅ | ✅ |
| **Gestionnaire** | ❌ | ✅ (Gérés) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Technicien** | ❌ | ❌ | ✅ | ✅ | ✅ (Physique/Moral) | ❌ | ✅ | ✅ |
| **Médecin Contrôleur** | ❌ | ❌ | ✅ | ✅ | ✅ (Prestataires) | ✅ | ✅ | ✅ |

---

## 🚀 Exemples d'utilisation

### JavaScript/Fetch
```javascript
const getGestionnaireStats = async () => {
  const response = await fetch('/api/v1/admin/gestionnaires/stats', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};

const getClientStats = async () => {
  const response = await fetch('/api/v1/clients/stats', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};

const getAssureStats = async () => {
  const response = await fetch('/api/v1/assures/stats', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};

const getDemandeAdhesionStats = async () => {
  const response = await fetch('/api/v1/demandes-adhesions/stats', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};

const getDashboardStats = async () => {
  const response = await fetch('/api/v1/dashboard/stats', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};
```

### Axios
```javascript
const getPersonnelStats = async () => {
  const response = await axios.get('/api/v1/gestionnaire/personnels/stats', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
};
``` 