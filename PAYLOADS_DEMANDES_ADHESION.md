# Payloads Détaillés - Demandes d'Adhésion

Ce document détaille tous les payloads requis pour chaque type de demande d'adhésion dans le système SUNU Santé.

---

## 📋 Table des Matières

1. [Soumission de Demande d'Adhésion](#soumission-de-demande-dadhésion)
2. [Gestion des Entreprises](#gestion-des-entreprises)
3. [Proposition de Contrat](#proposition-de-contrat)
4. [Validation de Prestataire](#validation-de-prestataire)
5. [Rejet de Demande](#rejet-de-demande)
6. [Soumission Employé (Entreprise)](#soumission-employé-entreprise)
7. [Acceptation de Contrat](#acceptation-de-contrat)

---

## 🚀 Soumission de Demande d'Adhésion

### **Route**
```http
POST /api/v1/demandes-adhesions/
```

### **Headers**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

### **Payload selon le type de demandeur**

#### **1. Client Physique** (`type_demandeur: "physique"`)

```json
{
  "type_demandeur": "physique",
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Jean Dupont"
    },
    {
      "question_id": 2,
      "reponse_number": 35
    },
    {
      "question_id": 3,
      "reponse_bool": true
    },
    {
      "question_id": 4,
      "reponse_date": "1988-05-15"
    }
  ],
  "beneficiaires": [
    {
      "nom": "Dupont",
      "prenoms": "Marie",
      "date_naissance": "1990-03-20",
      "sexe": "F",
      "lien_parente": "conjoint",
      "reponses": [
        {
          "question_id": 5,
          "reponse_text": "Marie Dupont"
        },
        {
          "question_id": 6,
          "reponse_number": 32
        }
      ]
    },
    {
      "nom": "Dupont",
      "prenoms": "Lucas",
      "date_naissance": "2015-08-10",
      "sexe": "M",
      "lien_parente": "enfant",
      "reponses": [
        {
          "question_id": 7,
          "reponse_text": "Lucas Dupont"
        },
        {
          "question_id": 8,
          "reponse_number": 8
        }
      ]
    }
  ]
}
```

#### **2. Entreprise** (`type_demandeur: "autre"`)

```json
{
  "type_demandeur": "autre",
  "reponses": [
    {
      "question_id": 10,
      "reponse_text": "Entreprise ABC SARL"
    },
    {
      "question_id": 11,
      "reponse_text": "123456789"
    },
    {
      "question_id": 12,
      "reponse_text": "50"
    },
    {
      "question_id": 13,
      "reponse_text": "Abidjan, Côte d'Ivoire"
    }
  ]
}
```

#### **3. Centre de Soins** (`type_demandeur: "centre_de_soins"`)

```json
{
  "type_demandeur": "centre_de_soins",
  "reponses": [
    {
      "question_id": 20,
      "reponse_text": "Centre Médical Excellence"
    },
    {
      "question_id": 21,
      "reponse_text": "987654321"
    },
    {
      "question_id": 22,
      "reponse_text": "Dr. Konan Kouassi"
    },
    {
      "question_id": 23,
      "reponse_fichier": "file" // Upload de fichier (PDF, JPG, PNG)
    },
    {
      "question_id": 24,
      "reponse_text": "Médecine générale, Cardiologie, Pédiatrie"
    }
  ]
}
```

#### **4. Laboratoire/Centre de Diagnostic** (`type_demandeur: "laboratoire_centre_diagnostic"`)

```json
{
  "type_demandeur": "laboratoire_centre_diagnostic",
  "reponses": [
    {
      "question_id": 30,
      "reponse_text": "Laboratoire BioLab Plus"
    },
    {
      "question_id": 31,
      "reponse_text": "456789123"
    },
    {
      "question_id": 32,
      "reponse_text": "Dr. Traoré Fatou"
    },
    {
      "question_id": 33,
      "reponse_fichier": "file" // Certificat d'agrément
    },
    {
      "question_id": 34,
      "reponse_text": "Analyses sanguines, Tests COVID, Biologie moléculaire"
    }
  ]
}
```

#### **5. Pharmacie** (`type_demandeur: "pharmacie"`)

```json
{
  "type_demandeur": "pharmacie",
  "reponses": [
    {
      "question_id": 40,
      "reponse_text": "Pharmacie Santé Plus"
    },
    {
      "question_id": 41,
      "reponse_text": "789123456"
    },
    {
      "question_id": 42,
      "reponse_text": "M. Koffi Yao"
    },
    {
      "question_id": 43,
      "reponse_fichier": "file" // Licence pharmaceutique
    },
    {
      "question_id": 44,
      "reponse_text": "Médicaments génériques, Parapharmacie, Vaccins"
    }
  ]
}
```

#### **6. Optique** (`type_demandeur: "optique"`)

```json
{
  "type_demandeur": "optique",
  "reponses": [
    {
      "question_id": 50,
      "reponse_text": "Optique Vision Claire"
    },
    {
      "question_id": 51,
      "reponse_text": "321654987"
    },
    {
      "question_id": 52,
      "reponse_text": "Mme. Bamba Aminata"
    },
    {
      "question_id": 53,
      "reponse_fichier": "file" // Certificat d'optométrie
    },
    {
      "question_id": 54,
      "reponse_text": "Lunettes, Lentilles, Examens de vue"
    }
  ]
}
```

### **Types de réponses supportés**

| Type | Champ | Description | Validation |
|------|-------|-------------|------------|
| `text` | `reponse_text` | Texte libre | `string` |
| `radio` | `reponse_text` | Choix unique | `string` |
| `number` | `reponse_number` | Nombre | `numeric` |
| `boolean` | `reponse_bool` | Oui/Non | `boolean` |
| `date` | `reponse_date` | Date | `date` |
| `file` | `reponse_fichier` | Fichier | `file|mimes:jpeg,png,pdf,jpg|max:2048` |

---

## 🏢 Gestion des Entreprises

### **1. Génération du lien d'invitation**

#### **Route**
```http
POST /api/v1/entreprise/inviter-employe
```

#### **Headers**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

#### **Payload**
```json
{}
```

#### **Réponse de succès**
```json
{
  "success": true,
  "message": "Nouveau lien d'invitation généré avec succès.",
  "data": {
    "invitation_id": 123,
    "url": "https://sunusante.com/employes/formulaire/abc123def456",
    "expire_at": "2024-01-22T10:30:00Z"
  }
}
```

---

### **2. Récupération du formulaire employé**

#### **Route**
```http
GET /api/v1/employes/formulaire/{token}
```

#### **Headers**
```http
Content-Type: application/json
```

#### **Réponse**
```json
{
  "success": true,
  "message": "Formulaire employé prêt à être rempli.",
  "data": {
    "entreprise": {
      "id": 1,
      "nom": "Entreprise ABC SARL",
      "adresse": "Abidjan, Côte d'Ivoire"
    },
    "token": "abc123def456",
    "questions": [
      {
        "id": 1,
        "libelle": "Nom complet",
        "type_donnee": "text",
        "obligatoire": true,
        "options": null
      },
      {
        "id": 2,
        "libelle": "Âge",
        "type_donnee": "number",
        "obligatoire": true,
        "options": null
      },
      {
        "id": 3,
        "libelle": "Avez-vous des antécédents médicaux ?",
        "type_donnee": "boolean",
        "obligatoire": true,
        "options": null
      }
    ],
    "fields": [
      "nom",
      "prenoms", 
      "email",
      "date_naissance",
      "sexe",
      "contact",
      "profession",
      "adresse",
      "photo_url"
    ]
  }
}
```

---

### **3. Soumission de la fiche employé**

#### **Route**
```http
POST /api/v1/employes/formulaire/{token}
```

#### **Headers**
```http
Content-Type: multipart/form-data
```

#### **Payload**
```json
{
  "nom": "Traoré",
  "prenoms": "Moussa",
  "email": "moussa.traore@entreprise.com",
  "date_naissance": "1985-12-03",
  "sexe": "M",
  "contact": "+2250701234567",
  "profession": "Ingénieur Informatique",
  "adresse": "Cocody, Abidjan",
  "photo_url": "file", // Upload optionnel
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Moussa Traoré"
    },
    {
      "question_id": 2,
      "reponse_number": 38
    },
    {
      "question_id": 3,
      "reponse_bool": false
    }
  ]
}
```

#### **Réponse de succès**
```json
{
  "success": true,
  "message": "Fiche employé soumise avec succès.",
  "data": {
    "employe_id": 456,
    "user_id": 789,
    "email": "moussa.traore@entreprise.com"
  }
}
```

---

### **4. Soumission de la demande d'adhésion entreprise**

#### **Route**
```http
POST /api/v1/entreprise/soumettre-demande-adhesion
```

#### **Headers**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

#### **Payload**
```json
{}
```

#### **Réponse de succès**
```json
{
  "success": true,
  "message": "Demande d'adhésion entreprise soumise avec succès.",
  "data": {
    "demande_id": 123,
    "type_demandeur": "autre",
    "statut": "en_attente",
    "nombre_employes": 15,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

## 💼 Proposition de Contrat

### **Route**
```http
PUT /api/v1/demandes-adhesions/{demande_id}/proposer-contrat
```

### **Headers**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

### **Payload**

```json
{
  "contrat_id": 1,
  "prime_proposee": 75000,
  "taux_couverture": 80,
  "frais_gestion": 15,
  "commentaires": "Contrat adapté au profil du client avec couverture complète",
  "garanties_incluses": [1, 3, 5, 7]
}
```

### **Champs détaillés**

| Champ | Type | Requis | Description | Validation |
|-------|------|--------|-------------|------------|
| `contrat_id` | integer | ✅ | ID du contrat proposé | `exists:contrats,id` |
| `prime_proposee` | numeric | ✅ | Montant de la prime en FCFA | `min:0` |
| `taux_couverture` | numeric | ❌ | Pourcentage de couverture | `min:0|max:100` |
| `frais_gestion` | numeric | ❌ | Pourcentage des frais | `min:0|max:100` |
| `commentaires` | string | ❌ | Commentaires du technicien | `max:1000` |
| `garanties_incluses` | array | ❌ | IDs des garanties incluses | `exists:garanties,id` |

---

## ✅ Validation de Prestataire

### **Route**
```http
PUT /api/v1/demandes-adhesions/{demande_id}/valider-prestataire
```

### **Headers**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

### **Payload**

```json
{}
```

**Note :** Aucun payload requis - juste un bouton de validation.

---

## ❌ Rejet de Demande

### **Route**
```http
PUT /api/v1/demandes-adhesions/{demande_id}/rejeter
```

### **Headers**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

### **Payload**

```json
{
  "motif_rejet": "Documents incomplets. Veuillez fournir tous les certificats requis."
}
```

### **Champs détaillés**

| Champ | Type | Requis | Description | Validation |
|-------|------|--------|-------------|------------|
| `motif_rejet` | string | ✅ | Raison du rejet | `required|string` |

---

## 👥 Soumission Employé (Entreprise)

### **Route**
```http
POST /api/v1/employes/formulaire/{token}
```

### **Headers**
```http
Content-Type: multipart/form-data
```

### **Payload**

```json
{
  "nom": "Traoré",
  "prenoms": "Moussa",
  "email": "moussa.traore@entreprise.com",
  "date_naissance": "1985-12-03",
  "sexe": "M",
  "contact": "+2250701234567",
  "profession": "Ingénieur Informatique",
  "adresse": "Cocody, Abidjan",
  "photo_url": "file", // Upload photo (optionnel)
  "reponses": [
    {
      "question_id": 100,
      "reponse_text": "Moussa Traoré"
    },
    {
      "question_id": 101,
      "reponse_number": 38
    },
    {
      "question_id": 102,
      "reponse_bool": false
    },
    {
      "question_id": 103,
      "reponse_date": "2020-01-15"
    }
  ]
}
```

### **Champs détaillés**

| Champ | Type | Requis | Description | Validation |
|-------|------|--------|-------------|------------|
| `nom` | string | ✅ | Nom de famille | `max:255` |
| `prenoms` | string | ✅ | Prénoms | `max:255` |
| `email` | string | ✅ | Adresse email | `email|max:255` |
| `date_naissance` | date | ✅ | Date de naissance | `before:today` |
| `sexe` | string | ✅ | Sexe | `in:M,F` |
| `contact` | string | ❌ | Numéro de téléphone | `max:30` |
| `profession` | string | ❌ | Profession | `max:255` |
| `adresse` | string | ❌ | Adresse | `max:255` |
| `photo_url` | file | ❌ | Photo de profil | `mimes:jpg,jpeg,png,gif,webp` |
| `reponses` | array | ✅ | Réponses au questionnaire | `min:1` |

---

## 🤝 Acceptation de Contrat

### **Route**
```http
POST /api/v1/contrats/accepter/{token}
```

### **Headers**
```http
Content-Type: application/json
```

### **Payload**

```json
{
  "confirmation": true
}
```

### **Champs détaillés**

| Champ | Type | Requis | Description | Validation |
|-------|------|--------|-------------|------------|
| `confirmation` | boolean | ✅ | Confirmation d'acceptation | `required|boolean` |

---

## 📊 Exemples de Réponses

### **Réponse de succès (Soumission)**
```json
{
  "success": true,
  "message": "Demande d'adhésion soumise avec succès.",
  "data": {
    "id": 123,
    "type_demandeur": "physique",
    "statut": "en_attente",
    "created_at": "2024-01-15T10:30:00Z",
    "user": {
      "id": 456,
      "email": "jean.dupont@email.com"
    }
  }
}
```

### **Réponse de succès (Proposition Contrat)**
```json
{
  "success": true,
  "message": "Contrat proposé avec succès.",
  "data": {
    "proposition_id": 789,
    "contrat_id": 1,
    "prime_proposee": 75000,
    "token_acceptation": "abc123def456...",
    "expiration": "2024-01-22T10:30:00Z"
  }
}
```

### **Réponse d'erreur de validation**
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "type_demandeur": ["Le type de demandeur est obligatoire."],
    "reponses": ["Les réponses au questionnaire sont requises."],
    "reponses.0.question_id": ["L'ID de la question est requis."]
  }
}
```

---

## ⚠️ Points d'attention

### **Validation dynamique**
- Les questions et leurs types sont récupérés dynamiquement selon le `type_demandeur`
- Les champs obligatoires dépendent de la configuration des questions
- Les fichiers uploadés doivent respecter les types MIME autorisés

### **Sécurité**
- Tous les endpoints nécessitent une authentification (sauf acceptation contrat)
- Les permissions sont vérifiées selon le rôle de l'utilisateur
- Les tokens d'acceptation ont une expiration de 7 jours

### **Performance**
- Les fichiers sont uploadés et stockés sur le serveur
- Les validations sont effectuées côté serveur
- Les transactions DB garantissent l'intégrité des données

---

## 🔧 Tests avec cURL

### **Soumission demande physique**
```bash
curl -X POST "https://api.sunusante.com/api/v1/demandes-adhesions/" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "type_demandeur": "physique",
    "reponses": [
      {
        "question_id": 1,
        "reponse_text": "Jean Dupont"
      }
    ]
  }'
```

### **Proposition de contrat**
```bash
curl -X PUT "https://api.sunusante.com/api/v1/demandes-adhesions/123/proposer-contrat" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "contrat_id": 1,
    "prime_proposee": 75000,
    "commentaires": "Contrat adapté"
  }'
```

### **Upload fichier (Prestataire)**
```bash
curl -X POST "https://api.sunusante.com/api/v1/demandes-adhesions/" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "type_demandeur=centre_de_soins" \
  -F "reponses[0][question_id]=20" \
  -F "reponses[0][reponse_text]=Centre Médical" \
  -F "reponses[1][question_id]=23" \
  -F "reponses[1][reponse_fichier]=@document.pdf"
```

### **Génération lien invitation (Entreprise)**
```bash
curl -X POST "https://api.sunusante.com/api/v1/entreprise/inviter-employe" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{}'
```

### **Récupération formulaire employé**
```bash
curl -X GET "https://api.sunusante.com/api/v1/employes/formulaire/abc123def456" \
  -H "Content-Type: application/json"
```

### **Soumission fiche employé**
```bash
curl -X POST "https://api.sunusante.com/api/v1/employes/formulaire/abc123def456" \
  -H "Content-Type: multipart/form-data" \
  -F "nom=Traoré" \
  -F "prenoms=Moussa" \
  -F "email=moussa.traore@entreprise.com" \
  -F "date_naissance=1985-12-03" \
  -F "sexe=M" \
  -F "contact=+2250701234567" \
  -F "profession=Ingénieur Informatique" \
  -F "reponses[0][question_id]=1" \
  -F "reponses[0][reponse_text]=Moussa Traoré" \
  -F "photo_url=@photo.jpg"
```

### **Soumission demande entreprise**
```bash
curl -X POST "https://api.sunusante.com/api/v1/entreprise/soumettre-demande-adhesion" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{}'
``` 