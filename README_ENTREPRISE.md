# Flow Demande d'Adhésion - Entreprise

## Vue d'ensemble

Ce document décrit le flow complet pour la demande d'adhésion d'une entreprise, depuis la création du compte jusqu'à la soumission de la demande.

## Flow complet

1. **Création du compte entreprise** → 2. **Validation OTP** → 3. **Connexion** → 4. **Soumission de la demande avec employés** → 5. **Génération des liens d'invitation** → 6. **Employés remplissent leurs questionnaires** → 7. **Validation par le technicien**

---

## 1. Création du compte entreprise

### Endpoint
**POST** `/api/v1/auth/register`

### Headers
```
Content-Type: application/json
```

### Payload requis
```json
{
  "type_demandeur": "entreprise",
  "raison_sociale": "Sunu Tech SARL",
  "email": "contact@sunutech.ci",
  "contact": "+22501234567",
  "adresse": "123 Rue de la Paix, Abidjan",
  "password": "motdepasse123",
  "nombre_employe": 50,
  "secteur_activite": "Technologie"
}
```

### Champs obligatoires
- `type_demandeur` : Doit être "entreprise"
- `raison_sociale` : Nom de l'entreprise (max 255 caractères)
- `email` : Email unique de l'entreprise
- `contact` : Numéro de téléphone au format international (+225XXXXXXXX)
- `adresse` : Adresse complète de l'entreprise
- `password` : Mot de passe (min 8 caractères)
- `nombre_employe` : Nombre d'employés (entier, min 1)
- `secteur_activite` : Secteur d'activité de l'entreprise

### Réponse
```json
{
  "success": true,
  "message": "Inscription réussie. Vérifiez votre email pour valider votre compte.",
  "data": {
    "user": {
      "id": 15,
      "email": "contact@sunutech.ci",
      "est_actif": false,
      "entreprise": {
        "raison_sociale": "Sunu Tech SARL",
        "nombre_employe": 50,
        "secteur_activite": "Technologie"
      }
    }
  }
}
```

---

## 2. Validation OTP

### Endpoint
**POST** `/api/v1/auth/verify-otp`

### Payload
```json
{
  "email": "contact@sunutech.ci",
  "otp": "123456",
  "type": "register"
}
```

### Réponse
```json
{
  "success": true,
  "message": "Votre compte a été validé avec succès.",
  "data": {
    "user": {
      "id": 15,
      "email": "contact@sunutech.ci",
      "est_actif": true
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

---

## 3. Connexion

### Endpoint
**POST** `/api/v1/auth/login`

### Payload
```json
{
  "email": "contact@sunutech.ci",
  "password": "motdepasse123"
}
```

### Réponse
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": 15,
      "email": "contact@sunutech.ci",
      "entreprise": {
        "raison_sociale": "Sunu Tech SARL",
        "nombre_employe": 50,
        "secteur_activite": "Technologie"
      }
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

---

## 4. Générer les liens d'invitation pour les employés

### Endpoint
**POST** `/api/v1/entreprise/generer-liens-invitation`

### Headers
```
Content-Type: application/json
Authorization: Bearer {token_entreprise}
```

### Payload
```json
{
  "employes": [
    {
      "nom": "Dupont",
      "prenoms": "Jean",
      "date_naissance": "1985-03-15",
      "sexe": "M",
      "contact": "+22501234568",
      "email": "jean.dupont@sunutech.ci",
      "profession": "Développeur",
      "lien_parente": "employe"
    },
    {
      "nom": "Martin",
      "prenoms": "Marie",
      "date_naissance": "1990-07-22",
      "sexe": "F",
      "contact": "+22501234569",
      "email": "marie.martin@sunutech.ci",
      "profession": "Designer",
      "lien_parente": "employe"
    }
  ]
}
```

### Champs obligatoires pour chaque employé
- `nom` : Nom de famille (max 255 caractères)
- `prenoms` : Prénoms (max 255 caractères)
- `date_naissance` : Date de naissance (format YYYY-MM-DD, avant aujourd'hui)
- `sexe` : M ou F
- `email` : Email valide (max 255 caractères)
- `lien_parente` : Doit être "employe"

### Champs optionnels
- `contact` : Numéro de téléphone (max 30 caractères)
- `profession` : Profession (max 255 caractères)

### Réponse
```json
{
  "success": true,
  "message": "Liens d'invitation générés avec succès pour 2 employé(s).",
  "data": {
    "entreprise_id": 15,
    "raison_sociale": "Sunu Tech SARL",
    "nombre_employes": 2,
    "liens_invitation": [
      {
        "employe_id": 1,
        "nom": "Dupont Jean",
        "email": "jean.dupont@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/abc123def456",
        "token": "abc123def456",
        "expire_at": "2025-01-22T10:30:00.000000Z"
      },
      {
        "employe_id": 2,
        "nom": "Martin Marie",
        "email": "marie.martin@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/xyz789uvw012",
        "token": "xyz789uvw012",
        "expire_at": "2025-01-22T10:30:00.000000Z"
      }
    ]
  }
}
```

---

## 5. Consulter les liens d'invitation existants

### Endpoint
**GET** `/api/v1/entreprise/liens-invitation`

### Headers
```
Authorization: Bearer {token_entreprise}
```

### Réponse
```json
{
  "success": true,
  "message": "Liens d'invitation récupérés avec succès",
  "data": {
    "entreprise_id": 15,
    "raison_sociale": "Sunu Tech SARL",
    "total_liens": 3,
    "liens_actifs": 2,
    "liens_expires": 1,
    "liens": [
      {
        "invitation_id": 1,
        "employe_id": 1,
        "nom": "Dupont Jean",
        "email": "jean.dupont@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/abc123def456",
        "token": "abc123def456",
        "statut": "actif",
        "envoye_le": "2025-01-15T10:30:00.000000Z",
        "expire_le": "2025-01-22T10:30:00.000000Z"
      },
      {
        "invitation_id": 2,
        "employe_id": 2,
        "nom": "Martin Marie",
        "email": "marie.martin@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/xyz789uvw012",
        "token": "xyz789uvw012",
        "statut": "actif",
        "envoye_le": "2025-01-15T10:30:00.000000Z",
        "expire_le": "2025-01-22T10:30:00.000000Z"
      },
      {
        "invitation_id": 3,
        "employe_id": 3,
        "nom": "Nouveau Employé",
        "email": "nouveau.employe@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/def456ghi789",
        "token": "def456ghi789",
        "statut": "expire",
        "envoye_le": "2025-01-10T10:30:00.000000Z",
        "expire_le": "2025-01-17T10:30:00.000000Z"
      }
    ]
  }
}
```

---

## 6. Soumission de la demande d'adhésion

### Endpoint
**POST** `/api/v1/entreprise/soumettre-demande-adhesion`

### Headers
```
Content-Type: application/json
Authorization: Bearer {token_entreprise}
```

### Payload
```json
{
  "employes": [
    {
      "nom": "Dupont",
      "prenoms": "Jean",
      "date_naissance": "1985-03-15",
      "sexe": "M",
      "contact": "+22501234568",
      "email": "jean.dupont@sunutech.ci",
      "profession": "Développeur",
      "lien_parente": "employe"
    },
    {
      "nom": "Martin",
      "prenoms": "Marie",
      "date_naissance": "1990-07-22",
      "sexe": "F",
      "contact": "+22501234569",
      "email": "marie.martin@sunutech.ci",
      "profession": "Designer",
      "lien_parente": "employe"
    }
  ]
}
```

### Champs obligatoires pour chaque employé
- `nom` : Nom de famille (max 255 caractères)
- `prenoms` : Prénoms (max 255 caractères)
- `date_naissance` : Date de naissance (format YYYY-MM-DD, avant aujourd'hui)
- `sexe` : M ou F
- `reponses` : Tableau des réponses au questionnaire (min 1 réponse)

### Types de réponses selon le type de question
- **radio/text** : `reponse_text`
- **number** : `reponse_number`
- **boolean** : `reponse_bool`
- **date** : `reponse_date`
- **file** : `reponse_fichier` (fichier uploadé)

### Réponse
```json
{
  "success": true,
  "message": "Fiche employé soumise avec succès",
  "data": {
    "employe_id": 1,
    "statut": "soumis",
    "nombre_reponses": 4
  }
}
```

---

## 7. Inviter des employés supplémentaires

### Endpoint
**POST** `/api/v1/entreprise/inviter-employe`

### Headers
```
Content-Type: application/json
Authorization: Bearer {token_entreprise}
```

### Payload
```json
{
  "demande_adhesion_id": 25,
  "employes": [
    {
      "nom": "Nouveau",
      "prenoms": "Employé",
      "date_naissance": "1988-11-10",
      "sexe": "M",
      "contact": "+22501234570",
      "email": "nouveau.employe@sunutech.ci",
      "profession": "Marketing",
      "lien_parente": "employe"
    }
  ]
}
```

### Réponse
```json
{
  "success": true,
  "message": "Employés invités avec succès",
  "data": {
    "liens_invitation": [
      {
        "employe_id": 3,
        "nom": "Nouveau Employé",
        "email": "nouveau.employe@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/def456ghi789",
        "token": "def456ghi789"
      }
    ]
  }
}
```

---

## 8. Formulaire employé (publique)

### Endpoint
**GET** `/api/v1/employes/formulaire/{token}`

### Réponse
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
      },
      {
        "id": 3,
        "libelle": "Quel est votre poids en kg ?",
        "type_donnee": "number",
        "est_obligatoire": true
      },
      {
        "id": 4,
        "libelle": "Avez-vous des documents médicaux à joindre ?",
        "type_donnee": "file",
        "est_obligatoire": false
      }
    ]
  }
}
```

---

## 9. Soumission du formulaire employé

### Endpoint
**POST** `/api/v1/employes/formulaire/{token}`

### Payload
```json
{
  "nom": "Dupont",
  "prenoms": "Jean",
  "email": "jean.dupont@sunutech.ci",
  "date_naissance": "1985-03-15",
  "sexe": "M",
  "contact": "+22501234568",
  "profession": "Développeur",
  "adresse": "123 Rue de la Paix, Abidjan",
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Non"
    },
    {
      "question_id": 2,
      "reponse_number": 175
    },
    {
      "question_id": 3,
      "reponse_number": 70
    },
    {
      "question_id": 4,
      "reponse_fichier": "document_medical.pdf"
    }
  ],
  "beneficiaires": [
    {
      "nom": "Dupont",
      "prenoms": "Marie",
      "date_naissance": "1990-05-20",
      "sexe": "F",
      "lien_parente": "épouse",
      "photo": "photo_marie.jpg",
      "reponses": [
        {
          "question_id": 1,
          "reponse_text": "Non"
        },
        {
          "question_id": 2,
          "reponse_number": 165
        }
      ]
    },
    {
      "nom": "Dupont",
      "prenoms": "Pierre",
      "date_naissance": "2015-08-10",
      "sexe": "M",
      "lien_parente": "enfant",
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

### Champs obligatoires
- `nom` : Nom de famille (max 255 caractères)
- `prenoms` : Prénoms (max 255 caractères)
- `email` : Email valide (max 255 caractères)
- `date_naissance` : Date de naissance (format YYYY-MM-DD, avant aujourd'hui)
- `sexe` : M ou F
- `reponses` : Tableau des réponses au questionnaire (min 1 réponse)

### Champs optionnels
- `contact` : Numéro de téléphone (max 30 caractères)
- `profession` : Profession (max 255 caractères)
- `adresse` : Adresse complète (max 255 caractères)
- `photo` : Photo de l'employé (fichier JPG, PNG, GIF, WEBP)
- `beneficiaires` : Tableau des bénéficiaires (optionnel)

### Champs obligatoires pour chaque bénéficiaire (si fourni)
- `nom` : Nom de famille (max 255 caractères)
- `prenoms` : Prénoms (max 255 caractères)
- `date_naissance` : Date de naissance (format YYYY-MM-DD, avant aujourd'hui)
- `sexe` : M ou F
- `lien_parente` : Lien de parenté (max 255 caractères)

### Champs optionnels pour chaque bénéficiaire
- `photo` : Photo du bénéficiaire (fichier JPG, PNG, GIF, WEBP)
- `reponses` : Tableau des réponses au questionnaire (optionnel)

### Types de réponses selon le type de question
- **radio/text** : `reponse_text`
- **number** : `reponse_number`
- **boolean** : `reponse_bool`
- **date** : `reponse_date`
- **file** : `reponse_fichier` (fichier uploadé)

### Réponse
```json
{
  "success": true,
  "message": "Fiche employé soumise avec succès",
  "data": {
    "employe_id": 1,
    "statut": "soumis",
    "nombre_reponses": 4
  }
}
```

---

## 10. Consulter les liens d'invitation

### Endpoint
**GET** `/api/v1/entreprise/liens-invitation`

### Headers
```
Authorization: Bearer {token_entreprise}
```

### Réponse
```json
{
  "success": true,
  "message": "Liens d'invitation récupérés avec succès",
  "data": {
    "entreprise_id": 15,
    "raison_sociale": "Sunu Tech SARL",
    "total_liens": 3,
    "liens_actifs": 2,
    "liens_expires": 1,
    "liens": [
      {
        "invitation_id": 1,
        "employe_id": 1,
        "nom": "Dupont Jean",
        "email": "jean.dupont@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/abc123def456",
        "token": "abc123def456",
        "statut": "actif",
        "envoye_le": "2025-01-15T10:30:00.000000Z",
        "expire_le": "2025-01-22T10:30:00.000000Z"
      },
      {
        "invitation_id": 2,
        "employe_id": 2,
        "nom": "Martin Marie",
        "email": "marie.martin@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/xyz789uvw012",
        "token": "xyz789uvw012",
        "statut": "actif",
        "envoye_le": "2025-01-15T10:30:00.000000Z",
        "expire_le": "2025-01-22T10:30:00.000000Z"
      },
      {
        "invitation_id": 3,
        "employe_id": 3,
        "nom": "Nouveau Employé",
        "email": "nouveau.employe@sunutech.ci",
        "lien": "https://app.sunusante.ci/employes/formulaire/def456ghi789",
        "token": "def456ghi789",
        "statut": "expire",
        "envoye_le": "2025-01-10T10:30:00.000000Z",
        "expire_le": "2025-01-17T10:30:00.000000Z"
      }
    ]
  }
}
```

---

## 11. Consulter toutes les demandes d'adhésion de l'entreprise

### Endpoint
**GET** `/api/v1/entreprise/mes-demandes`

### Headers
```
Authorization: Bearer {token_entreprise}
```

### Paramètres de requête (optionnels)
- `statut` : Filtrer par statut (en_attente, validee, rejetee)
- `per_page` : Nombre d'éléments par page (défaut: 10)
- `page` : Numéro de page

### Exemple de requête
```
GET /api/v1/entreprise/mes-demandes?statut=en_attente&per_page=5
```

### Réponse
```json
{
  "success": true,
  "message": "Demandes d'adhésion de l'entreprise récupérées avec succès",
  "data": {
    "entreprise": {
      "id": 15,
      "raison_sociale": "Sunu Tech SARL",
      "nombre_employe": 50,
      "secteur_activite": "Technologie"
    },
    "demandes": {
      "current_page": 1,
      "data": [
        {
          "id": 25,
          "statut": "en_attente",
          "type_demandeur": "entreprise",
          "created_at": "2025-01-15T10:30:00.000000Z",
          "updated_at": "2025-01-15T10:30:00.000000Z",
          "valide_par": null,
          "valider_a": null,
          "motif_rejet": null,
          "commentaires_technicien": null,
          "employes": [
            {
              "id": 1,
              "nom": "Dupont",
              "prenoms": "Jean",
              "email": "jean.dupont@sunutech.ci",
              "contact": "+22501234568",
              "profession": "Développeur",
              "date_naissance": "1985-03-15",
              "sexe": "M",
              "statut": "en_attente",
              "lien_parente": "employe",
              "photo_url": null,
              "reponses_questionnaire": [
                {
                  "question_id": 1,
                  "question_libelle": "Avez-vous des antécédents médicaux ?",
                  "reponse_text": "Non",
                  "reponse_number": null,
                  "reponse_bool": null,
                  "reponse_date": null,
                  "reponse_fichier": null
                }
              ]
            }
          ],
          "reponses_questionnaire": [
            {
              "question_id": 10,
              "question_libelle": "Nombre d'employés à assurer",
              "reponse_text": null,
              "reponse_number": 50,
              "reponse_bool": null,
              "reponse_date": null,
              "reponse_fichier": null
            }
          ],
          "statistiques": {
            "total_employes": 2,
            "employes_avec_reponses": 1,
            "employes_sans_reponses": 1
          }
        }
      ],
      "total": 1,
      "per_page": 10
    },
    "statistiques_globales": {
      "total_demandes": 1,
      "demandes_en_attente": 1,
      "demandes_validees": 0,
      "demandes_rejetees": 0
    }
  }
}
```

---

## 12. Consulter une demande d'adhésion spécifique

### Endpoint
**GET** `/api/v1/entreprise/mes-demandes/{id}`

### Headers
```
Authorization: Bearer {token_entreprise}
```

### Réponse
```json
{
  "success": true,
  "message": "Détails de la demande d'adhésion récupérés avec succès",
  "data": {
    "id": 25,
    "statut": "en_attente",
    "type_demandeur": "entreprise",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z",
    "valide_par": null,
    "valider_a": null,
    "motif_rejet": null,
    "commentaires_technicien": null,
    "employes": [
      {
        "id": 1,
        "nom": "Dupont",
        "prenoms": "Jean",
        "email": "jean.dupont@sunutech.ci",
        "contact": "+22501234568",
        "profession": "Développeur",
        "date_naissance": "1985-03-15",
        "sexe": "M",
        "statut": "en_attente",
        "lien_parente": "employe",
        "photo_url": null,
        "reponses_questionnaire": [
          {
            "question_id": 1,
            "question_libelle": "Avez-vous des antécédents médicaux ?",
            "type_donnee": "radio",
            "reponse_text": "Non",
            "reponse_number": null,
            "reponse_bool": null,
            "reponse_date": null,
            "reponse_fichier": null
          },
          {
            "question_id": 2,
            "question_libelle": "Quelle est votre taille en cm ?",
            "type_donnee": "number",
            "reponse_text": null,
            "reponse_number": 175,
            "reponse_bool": null,
            "reponse_date": null,
            "reponse_fichier": null
          }
        ]
      }
    ],
    "reponses_questionnaire": [
      {
        "question_id": 10,
        "question_libelle": "Nombre d'employés à assurer",
        "type_donnee": "number",
        "reponse_text": null,
        "reponse_number": 50,
        "reponse_bool": null,
        "reponse_date": null,
        "reponse_fichier": null
      }
    ],
    "statistiques": {
      "total_employes": 2,
      "employes_avec_reponses": 1,
      "employes_sans_reponses": 1,
      "pourcentage_completion": 50.0
    }
  }
}
```

---

## 13. Consulter les demandes d'adhésion soumises par les employés

### Endpoint
**GET** `/api/v1/entreprise/demandes-employes`

### Headers
```
Authorization: Bearer {token_entreprise}
```

### Paramètres de requête (optionnels)
- `statut` : Filtrer par statut (en_attente, validee, rejetee)
- `employe_id` : Filtrer par employé spécifique
- `per_page` : Nombre d'éléments par page (défaut: 10)
- `page` : Numéro de page

### Exemple de requête
```
GET /api/v1/entreprise/demandes-employes?statut=en_attente&employe_id=1&per_page=5
```

### Réponse
```json
{
  "success": true,
  "message": "Demandes d'adhésion des employés récupérées avec succès",
  "data": {
    "entreprise": {
      "id": 15,
      "raison_sociale": "Sunu Tech SARL",
      "nombre_employe": 50,
      "secteur_activite": "Technologie"
    },
    "demandes": {
      "current_page": 1,
      "data": [
        {
          "id": 30,
          "statut": "en_attente",
          "type_demandeur": "physique",
          "created_at": "2025-01-16T14:30:00.000000Z",
          "updated_at": "2025-01-16T14:30:00.000000Z",
          "valide_par": null,
          "valider_a": null,
          "motif_rejet": null,
          "commentaires_technicien": null,
          "employe": {
            "id": 1,
            "nom": "Dupont",
            "prenoms": "Jean",
            "email": "jean.dupont@sunutech.ci",
            "contact": "+22501234568",
            "profession": "Développeur",
            "date_naissance": "1985-03-15",
            "sexe": "M",
            "statut": "en_attente",
            "lien_parente": "employe",
            "photo_url": null
          },
          "reponses_questionnaire": [
            {
              "question_id": 1,
              "question_libelle": "Avez-vous des antécédents médicaux ?",
              "type_donnee": "radio",
              "reponse_text": "Non",
              "reponse_number": null,
              "reponse_bool": null,
              "reponse_date": null,
              "reponse_fichier": null
            },
            {
              "question_id": 2,
              "question_libelle": "Quelle est votre taille en cm ?",
              "type_donnee": "number",
              "reponse_text": null,
              "reponse_number": 175,
              "reponse_bool": null,
              "reponse_date": null,
              "reponse_fichier": null
            }
          ],
          "demandeur": {
            "id": 20,
            "email": "jean.dupont@sunutech.ci",
            "contact": "+22501234568",
            "adresse": "123 Rue de la Paix, Abidjan"
          }
        }
      ],
      "total": 1,
      "per_page": 10
    },
    "statistiques_globales": {
      "total_demandes": 1,
      "demandes_en_attente": 1,
      "demandes_validees": 0,
      "demandes_rejetees": 0
    }
  }
}
```

---

## 14. Consulter une demande d'adhésion spécifique d'un employé

### Endpoint
**GET** `/api/v1/entreprise/demandes-employes/{id}`

### Headers
```
Authorization: Bearer {token_entreprise}
```

### Réponse
```json
{
  "success": true,
  "message": "Détails de la demande d'adhésion de l'employé récupérés avec succès",
  "data": {
    "id": 30,
    "statut": "en_attente",
    "type_demandeur": "physique",
    "created_at": "2025-01-16T14:30:00.000000Z",
    "updated_at": "2025-01-16T14:30:00.000000Z",
    "valide_par": null,
    "valider_a": null,
    "motif_rejet": null,
    "commentaires_technicien": null,
    "employe": {
      "id": 1,
      "nom": "Dupont",
      "prenoms": "Jean",
      "email": "jean.dupont@sunutech.ci",
      "contact": "+22501234568",
      "profession": "Développeur",
      "date_naissance": "1985-03-15",
      "sexe": "M",
      "statut": "en_attente",
      "lien_parente": "employe",
      "photo_url": null,
      "reponses_questionnaire": [
        {
          "question_id": 1,
          "question_libelle": "Avez-vous des antécédents médicaux ?",
          "type_donnee": "radio",
          "reponse_text": "Non",
          "reponse_number": null,
          "reponse_bool": null,
          "reponse_date": null,
          "reponse_fichier": null
        },
        {
          "question_id": 2,
          "question_libelle": "Quelle est votre taille en cm ?",
          "type_donnee": "number",
          "reponse_text": null,
          "reponse_number": 175,
          "reponse_bool": null,
          "reponse_date": null,
          "reponse_fichier": null
        }
      ]
    },
    "reponses_questionnaire": [
      {
        "question_id": 1,
        "question_libelle": "Avez-vous des antécédents médicaux ?",
        "type_donnee": "radio",
        "reponse_text": "Non",
        "reponse_number": null,
        "reponse_bool": null,
        "reponse_date": null,
        "reponse_fichier": null
      },
      {
        "question_id": 2,
        "question_libelle": "Quelle est votre taille en cm ?",
        "type_donnee": "number",
        "reponse_text": null,
        "reponse_number": 175,
        "reponse_bool": null,
        "reponse_date": null,
        "reponse_fichier": null
      }
    ],
    "demandeur": {
      "id": 20,
      "email": "jean.dupont@sunutech.ci",
      "contact": "+22501234568",
      "adresse": "123 Rue de la Paix, Abidjan"
    },
    "statistiques": {
      "total_reponses": 2,
      "reponses_employe": 2,
      "reponses_demande": 2
    }
  }
}
```

---

## Codes d'erreur

### Erreur de validation (422)
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "raison_sociale": ["La raison sociale est obligatoire"],
    "email": ["Cet email est déjà utilisé"],
    "contact": ["Le contact doit être au format international (+225XXXXXXXX)"],
    "employes.0.email": ["L'email doit être valide"],
    "employes.0.date_naissance": ["La date de naissance doit être dans le passé"]
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
  "error": "Seules les entreprises peuvent soumettre une demande groupée"
}
```

### Token expiré
```json
{
  "success": false,
  "message": "Invitation invalide ou expirée",
  "error": "Le lien d'invitation a expiré"
}
```

---

## Notes importantes

### Configuration
- **URL du Frontend** : Configurez `FRONTEND_URL` dans votre fichier `.env` pour pointer vers votre application frontend
  ```
  FRONTEND_URL=https://app.sunusante.ci
  ```
- **URL de l'API** : Configurez `APP_URL` dans votre fichier `.env` pour pointer vers votre API backend
  ```
  APP_URL=https://api.sunusante.ci
  ```

### Sécurité
- Les tokens JWT expirent après 24h
- Les liens d'invitation expirent après 7 jours
- Seules les entreprises connectées peuvent soumettre des demandes

### Validation
- Les emails doivent être uniques
- Les contacts doivent être au format international (+225XXXXXXXX)
- Les dates de naissance doivent être dans le passé
- Les fichiers uploadés doivent être au format PDF, JPG, PNG (max 2MB)

### Workflow
1. L'entreprise crée son compte
2. Elle valide son compte via OTP
3. Elle se connecte et obtient un token JWT
4. Elle soumet sa demande avec la liste des employés
5. Le système génère automatiquement les liens d'invitation
6. Les employés remplissent leurs questionnaires via les liens
7. Le technicien peut consulter et valider la demande complète 