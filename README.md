# AMP Backend - API Documentation

## Table des matières

1. [Authentification](#authentification)
2. [Demandes d'Adhésion](#demandes-dadhésion)
   - [Personne Physique](#personne-physique)
   - [Entreprise](#entreprise)
   - [Prestataire](#prestataire)
3. [Contrats](#contrats)
4. [Statistiques](#statistiques)
5. [Gestion des Rôles](#gestion-des-rôles)

---

## Authentification

### Création de compte
**POST** `/api/v1/auth/register`

**Payload :**
```json
{
  "type_demandeur": "physique|entreprise|centre_de_soins|laboratoire_centre_diagnostic|pharmacie|optique",
  "nom": "Dupont",
  "prenoms": "Jean",
  "email": "jean.dupont@example.com",
  "contact": "22501234567",
  "adresse": "123 Rue de la Paix, Abidjan",
  "password": "motdepasse123",
  "date_naissance": "1985-03-15",
  "sexe": "M",
  "profession": "Développeur"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Inscription réussie. Vérifiez votre email pour valider votre compte.",
  "data": {
    "user": {
      "id": 15,
      "email": "jean.dupont@example.com",
      "est_actif": false
    }
  }
}
```

### Validation OTP
**POST** `/api/v1/auth/verify-otp`

**Payload :**
```json
{
  "email": "jean.dupont@example.com",
  "otp": "123456",
  "type": "register"
}
```

### Connexion
**POST** `/api/v1/auth/login`

**Payload :**
```json
{
  "email": "jean.dupont@example.com",
  "password": "motdepasse123"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": 15,
      "email": "jean.dupont@example.com",
      "client": {
        "nom": "Dupont",
        "prenoms": "Jean"
      }
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

---

## Demandes d'Adhésion

### Personne Physique

#### Soumission de demande
**POST** `/api/v1/demandes-adhesion`

**Headers :**
```
Content-Type: application/json
Authorization: Bearer {token}
```

**Payload :**
```json
{
  "type_demandeur": "physique",
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Non"
    },
    {
      "question_id": 2,
      "reponse_decimal": 175
    }
  ],
  "beneficiaires": [
    {
      "nom": "Martin",
      "prenoms": "Marie",
      "date_naissance": "1990-07-22",
      "sexe": "F",
      "contact": "22501234568",
      "email": "marie.martin@example.com",
      "lien_parente": "epouse",
      "reponses": [
        {
          "question_id": 1,
          "reponse_text": "Non"
        }
      ]
    }
  ]
}
```

#### Validation par technicien
**PUT** `/api/v1/demandes-adhesion/{id}/valider-prospect`

**Payload :**
```json
{
  "commentaires": "Demande validée après vérification"
}
```

### Entreprise

#### 1. Création du compte entreprise
**POST** `/api/v1/auth/register`

**Payload :**
```json
{
  "type_demandeur": "entreprise",
  "raison social": "Sunu Tech SARL",
  "email": "contact@sunutech.ci",
  "contact": "22501234567",
  "adresse": "123 Rue de la Paix, Abidjan",
  "password": "motdepasse123",
  "secteur_activite": "Technologie",
  "nombre_employes": 50
}
```

#### 2. Soumission de la demande
**POST** `/api/v1/entreprise/soumettre-demande-adhesion`

**Headers :**
```
Content-Type: application/json
Authorization: Bearer {token_entreprise}
```

**Payload :**
```json
{
  "employes": [
    {
      "nom": "Dupont",
      "prenoms": "Jean",
      "date_naissance": "1985-03-15",
      "sexe": "M",
      "contact": "22501234568",
      "email": "jean.dupont@sunutech.ci",
      "profession": "Développeur",
      "lien_parente": "employe"
    },
    {
      "nom": "Martin",
      "prenoms": "Marie",
      "date_naissance": "1990-07-22",
      "sexe": "F",
      "contact": "22501234569",
      "email": "marie.martin@sunutech.ci",
      "profession": "Designer",
      "lien_parente": "employe"
    }
  ]
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Demande d'adhésion entreprise soumise avec succès",
  "data": {
    "demande_id": 25,
    "statut": "en_attente",
    "nombre_employes": 2,
    "liens_invitation": [
      {
        "employe_id": 1,
        "nom": "Dupont Jean",
        "email": "jean.dupont@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/abc123def456",
        "token": "abc123def456"
      },
      {
        "employe_id": 2,
        "nom": "Martin Marie",
        "email": "marie.martin@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/xyz789uvw012",
        "token": "xyz789uvw012"
      }
    ]
  }
}
```

#### 3. Inviter des employés supplémentaires
**POST** `/api/v1/entreprise/inviter-employe`

**Payload :**
```json
{
  "demande_adhesion_id": 25,
  "employes": [
    {
      "nom": "Nouveau",
      "prenoms": "Employé",
      "date_naissance": "1988-11-10",
      "sexe": "M",
      "contact": "22501234570",
      "email": "nouveau.employe@sunutech.ci",
      "profession": "Marketing",
      "lien_parente": "employe"
    }
  ]
}
```

#### 4. Formulaire employé (publique)
**GET** `/api/v1/employes/formulaire/{token}`

**Réponse :**
```json
{
  "success": true,
  "message": "Formulaire d'invitation trouvé",
  "data": {
    "prospect_id": 25,
    "employe": {
      "id": 1,
      "nom": "Dupont",
      "prenoms": "Jean",
      "email": "jean.dupont@sunutech.ci"
    },
    "questions": [
      {
        "id": 1,
        "libelle": "Avez-vous des antécédents médicaux ?",
        "type_donnee": "radio",
        "options": ["Oui", "Non"],
        "est_obligatoire": true
      },
      {
        "id": 2,
        "libelle": "Quelle est votre taille en cm ?",
        "type_donnee": "number",
        "est_obligatoire": true
      }
    ]
  }
}
```

#### 5. Soumission du formulaire employé
**POST** `/api/v1/employes/formulaire/{token}`

**Payload :**
```json
{
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Non"
    },
    {
      "question_id": 2,
      "reponse_decimal": 175
    }
  ]
}
```

### Prestataire

#### Soumission de demande prestataire
**POST** `/api/v1/demandes-adhesion`

**Payload :**
```json
{
  "type_demandeur": "centre_de_soins",
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Centre médical privé"
    },
    {
      "question_id": 2,
      "reponse_decimal": 10
    }
  ]
}
```

#### Validation par médecin contrôleur
**PUT** `/api/v1/demandes-adhesion/{id}/valider-prestataire`

**Payload :**
```json
{
  "commentaires": "Prestataire validé après vérification des documents"
}
```

---

## Contrats

### Création de contrat
**POST** `/api/v1/contrats`

**Headers :**
```
Content-Type: application/json
Authorization: Bearer {token_technicien}
```

**Payload :**
```json
{
  "libelle": "BASIC",
  "prime_standard": 50000,
  "categories_garanties": [
    {
      "categorie_garantie_id": 1,
      "couverture": 80
    },
    {
      "categorie_garantie_id": 2,
      "couverture": 70
    }
  ]
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Contrat créé avec succès",
  "data": {
    "id": 15,
    "libelle": "BASIC",
    "prime_standard": "50000.00",
    "est_actif": true,
    "categories_garanties": [
      {
        "id": 1,
        "libelle": "Consultations médicales",
        "description": "Remboursement des consultations",
        "couverture": 80,
        "garanties": [
          {
            "id": 1,
            "libelle": "Consultation généraliste",
            "plafond": "5000.00",
            "prix_standard": "2000.00",
            "taux_couverture": "80.00"
          }
        ]
      }
    ]
  }
}
```

### Récupérer les catégories disponibles
**GET** `/api/v1/contrats/categories-garanties`

**Réponse :**
```json
{
  "success": true,
  "message": "Catégories de garanties récupérées avec succès",
  "data": [
    {
      "id": 1,
      "libelle": "Consultations médicales",
      "description": "Remboursement des consultations",
      "garanties": [
        {
          "id": 1,
          "libelle": "Consultation généraliste",
          "plafond": "5000.00",
          "prix_standard": "2000.00",
          "taux_couverture": "80.00"
        }
      ]
    }
  ]
}
```

### Proposer un contrat à un prospect
**PUT** `/api/v1/demandes-adhesion/{id}/proposer-contrat`

**Payload :**
```json
{
  "contrat_id": 5,
  "prime_proposee": 750000,
  "taux_couverture": 85,
  "frais_gestion": 15,
  "commentaires": "Contrat adapté pour une entreprise de 50 employés"
}
```

### Accepter un contrat
**POST** `/api/v1/contrats/accepter/{token}`

**Payload :**
```json
{
  "decision": "accepter",
  "commentaires": "Contrat accepté"
}
```

---

## Statistiques

### Statistiques des demandes d'adhésion
**GET** `/api/v1/demandes-adhesion/stats`

**Réponse selon le rôle :**

**Pour un technicien :**
```json
{
  "success": true,
  "message": "Statistiques des demandes d'adhésion récupérées avec succès",
  "data": {
    "total": 25,
    "en_attente": 8,
    "validees": 12,
    "rejetees": 5,
    "repartition_par_type_demandeur": {
      "physique": 18,
      "entreprise": 7
    },
    "repartition_par_statut": {
      "en_attente": 8,
      "validee": 12,
      "rejetee": 5
    },
    "demandes_par_mois": {
      "Janvier": 5,
      "Février": 8,
      "Mars": 12
    }
  }
}
```

**Pour un médecin contrôleur :**
```json
{
  "success": true,
  "message": "Statistiques des demandes d'adhésion récupérées avec succès",
  "data": {
    "total": 15,
    "en_attente": 6,
    "validees": 7,
    "rejetees": 2,
    "repartition_par_type_demandeur": {
      "centre_de_soins": 8,
      "pharmacie": 4,
      "laboratoire_centre_diagnostic": 2,
      "optique": 1
    }
  }
}
```

---

## Gestion des Rôles

### Rôles disponibles :
- **admin_global** : Accès complet à toutes les fonctionnalités
- **gestionnaire** : Gestion des personnels et des demandes
- **technicien** : Validation des demandes physique/entreprise et proposition de contrats
- **medecin_controleur** : Validation des demandes prestataires
- **physique** : Client personne physique
- **entreprise** : Client entreprise
- **prestataire** : Prestataire de soins

### Filtrage des statistiques :
- **Techniciens** : Voient uniquement les demandes `physique` et `entreprise`
- **Médecins contrôleurs** : Voient uniquement les demandes prestataires (`centre_de_soins`, `pharmacie`, `laboratoire_centre_diagnostic`, `optique`)
- **Admin global** : Voient toutes les demandes

---

## Codes d'erreur

### Erreur de validation (422)
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "email": ["L'email doit être valide"],
    "contact": ["Le contact est obligatoire"]
  }
}
```

### Erreur d'autorisation (401)
```json
{
  "success": false,
  "message": "Accès non autorisé",
  "error": "Token invalide ou expiré"
}
```

### Erreur de permission (403)
```json
{
  "success": false,
  "message": "Accès interdit",
  "error": "Vous n'avez pas les permissions nécessaires"
}
```

### Ressource non trouvée (404)
```json
{
  "success": false,
  "message": "Demande d'adhésion non trouvée",
  "error": "La ressource demandée n'existe pas"
}
```

---

## Configuration Postman

### Variables d'environnement :
- `base_url` : `http://localhost:8000/api/v1`
- `token` : Votre token JWT après authentification

### Headers par défaut :
- `Content-Type` : `application/json`
- `Authorization` : `Bearer {{token}}`

### Collection AMP Backend :
1. **Auth** : Endpoints d'authentification
2. **Demandes** : Gestion des demandes d'adhésion
3. **Contrats** : Gestion des contrats
4. **Statistiques** : Statistiques et rapports
5. **Admin** : Fonctionnalités d'administration

---

## Notes importantes

### Sécurité :
- Tous les endpoints (sauf formulaire employé) nécessitent une authentification
- Les tokens JWT expirent après 24h
- Les liens d'invitation expirent après 7 jours

### Validation :
- Les emails doivent être uniques
- Les contacts doivent être au format ivoirien (225XXXXXXXX)
- Les dates de naissance doivent être dans le passé
- Les fichiers uploadés doivent être au format PDF, JPG, PNG

### Performance :
- Les requêtes sont paginées (10 éléments par page par défaut)
- Les relations sont chargées avec `with()` pour optimiser les requêtes
- Les statistiques utilisent des requêtes optimisées avec `selectRaw()`

---

## Support

Pour toute question ou problème, contactez l'équipe de développement. 