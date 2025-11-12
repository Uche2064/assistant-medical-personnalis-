# Documentation API - Demande d'Adhésion Personne Physique

## Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Authentification](#authentification)
3. [Endpoints](#endpoints)
4. [Modèles de données](#modèles-de-données)
5. [Règles de validation](#règles-de-validation)
6. [Flux de traitement](#flux-de-traitement)
7. [Exemples de requêtes](#exemples-de-requêtes)
8. [Codes d'erreur](#codes-derreur)

---

## Vue d'ensemble

L'API de demande d'adhésion pour les personnes physiques permet aux clients de soumettre une demande d'adhésion à l'assurance, d'ajouter des bénéficiaires, de répondre à un questionnaire dynamique et de gérer leurs propositions de contrat.

**Base URL**: `/api/v1`

**Format de réponse**: JSON

**Type de contenu**: `application/json` ou `multipart/form-data` (pour les fichiers)

---

## Authentification

Toutes les routes requièrent:
- **Header**: `Api-Key` - Clé API valide (middleware `verifyApiKey`)
- **Header**: `Authorization: Bearer {token}` - Token JWT (sauf mention contraire)

Les endpoints sont protégés par des rôles spécifiques via le middleware `checkRole`.

---

## Endpoints

### 1. Vérifier l'existence d'une demande

Permet à un client de vérifier s'il a déjà soumis une demande d'adhésion.

```http
GET /demandes-adhesions/has-demande
```

**Rôle requis**: `client`

#### Réponse (200 OK)

**Cas 1 : Aucune demande existante**
```json
{
  "success": true,
  "message": "Aucune demande d'adhésion trouvée",
  "data": {
    "existing": false,
    "demande": null,
    "can_submit": true,
    "status": "none"
  }
}
```

**Cas 2 : Demande existante**
```json
{
  "success": true,
  "message": "Demande d'adhésion récupérée avec succès",
  "data": {
    "existing": true,
    "demande": {
      "id": 1,
      "type_demandeur": "client",
      "statut": "en_attente",
      "created_at": "2025-10-12T10:00:00.000000Z",
      "updated_at": "2025-10-12T10:00:00.000000Z"
    },
    "can_submit": false,
    "status": "en_attente",
    "motif_rejet": null,
    "valider_a": null
  }
}
```

---

### 2. Soumettre une demande d'adhésion (Personne Physique)

Permet à un client de soumettre une demande d'adhésion complète avec questionnaire et bénéficiaires.

```http
POST /demandes-adhesions/client
```

**Rôle requis**: `client`

**Content-Type**: `multipart/form-data`

#### Corps de la requête

```json
{
  "type_demandeur": "client",
  "reponses": [
    {
      "question_id": 1,
      "reponse": "Valeur de la réponse"
    },
    {
      "question_id": 2,
      "reponse": 175.5
    },
    {
      "question_id": 3,
      "reponse": true
    },
    {
      "question_id": 4,
      "reponse": "2025-10-12"
    },
    {
      "question_id": 5,
      "reponse": "[fichier uploadé]"
    }
  ],
  "beneficiaires": [
    {
      "nom": "Doe",
      "prenoms": "Jane",
      "date_naissance": "1995-05-15",
      "sexe": "F",
      "email": "jane.doe@example.com",
      "contact": "+237690000000",
      "profession": "Enseignante",
      "photo_url": "[fichier image]",
      "lien_parente": "conjoint",
      "reponses": [
        {
          "question_id": 1,
          "reponse": "Valeur de la réponse"
        }
      ]
    }
  ]
}
```

#### Paramètres

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `type_demandeur` | string | ✅ | Type de demandeur: `"client"` |
| `reponses` | array | ✅ | Tableau de réponses au questionnaire |
| `reponses.*.question_id` | integer | ✅ | ID de la question |
| `reponses.*.reponse` | mixed | Conditionnel | Réponse (type dépend de la question) |
| `beneficiaires` | array | ❌ | Tableau des bénéficiaires (optionnel) |
| `beneficiaires.*.nom` | string | ✅ | Nom du bénéficiaire |
| `beneficiaires.*.prenoms` | string | ❌ | Prénoms du bénéficiaire |
| `beneficiaires.*.date_naissance` | date | ✅ | Date de naissance (format: YYYY-MM-DD) |
| `beneficiaires.*.sexe` | string | ✅ | Sexe: `"M"` ou `"F"` |
| `beneficiaires.*.email` | email | ❌ | Email du bénéficiaire |
| `beneficiaires.*.contact` | string | ❌ | Numéro de téléphone |
| `beneficiaires.*.profession` | string | ❌ | Profession |
| `beneficiaires.*.photo_url` | file | ✅ | Photo (JPEG, PNG, JPG, max: 5 Mo) |
| `beneficiaires.*.lien_parente` | string | ✅ | Lien de parenté (voir [Enums](#enums)) |
| `beneficiaires.*.reponses` | array | ✅ | Réponses au questionnaire du bénéficiaire |

#### Types de réponses selon le type de question

| Type de question | Type de réponse | Format | Exemple |
|-----------------|-----------------|--------|---------|
| `text` | string | Texte libre | `"Ma réponse"` |
| `number` | numeric | Nombre | `175.5` |
| `boolean` | boolean | Vrai/Faux | `true` ou `false` |
| `date` | date | YYYY-MM-DD | `"2025-10-12"` |
| `radio` | string | Option sélectionnée | `"option1"` |
| `file` | file | Fichier | Image/PDF (max: 2 Mo) |

#### Réponse (201 Created)

```json
{
  "success": true,
  "message": "Demande d'adhésion soumise avec succès.",
  "data": null
}
```

#### Erreurs possibles

**400 - Demande en cours**
```json
{
  "success": false,
  "message": "Vous avez déjà une demande d'adhésion en cours de traitement. Veuillez attendre la réponse.",
  "data": null
}
```

**400 - Demande déjà validée**
```json
{
  "success": false,
  "message": "Vous avez déjà une demande d'adhésion validée. Vous ne pouvez plus soumettre une nouvelle demande.",
  "data": null
}
```

**422 - Erreur de validation**
```json
{
  "success": false,
  "message": "Erreur de validation",
  "data": {
    "reponses.0.reponse": [
      "Cette réponse est obligatoire."
    ],
    "beneficiaires.0.photo_url": [
      "La photo du bénéficiaire est requise."
    ]
  }
}
```

---

### 3. Lister toutes les demandes d'adhésion

Récupère la liste de toutes les demandes d'adhésion (filtrées selon le rôle).

```http
GET /demandes-adhesions
```

**Rôles requis**: `medecin_controleur`, `technicien`, `admin_global`, `gestionnaire`, `commercial`

#### Réponse (200 OK)

```json
{
  "success": true,
  "message": "Liste des demandes d'adhésion récupérée avec succès",
  "data": [
    {
      "id": 1,
      "type_demandeur": "client",
      "statut": "en_attente",
      "created_at": "2025-10-12T10:00:00.000000Z",
      "updated_at": "2025-10-12T10:00:00.000000Z",
      "user": {
        "id": 5,
        "email": "client@example.com",
        "contact": "+237690000000"
      },
      "propositions_contrat": []
    }
  ]
}
```

---

### 4. Détails d'une demande d'adhésion

Récupère les détails complets d'une demande d'adhésion spécifique.

```http
GET /demandes-adhesions/{id}
```

**Rôles requis**: `medecin_controleur`, `technicien`

**Note**: Les techniciens ne peuvent voir que les demandes de type `client`, tandis que les médecins contrôleurs ne peuvent voir que les demandes de type `prestataire`.

#### Paramètres URL

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID de la demande d'adhésion |

#### Réponse (200 OK)

```json
{
  "success": true,
  "message": "Détails de la demande d'adhésion",
  "data": {
    "id": 1,
    "type_demandeur": "client",
    "statut": "en_attente",
    "created_at": "2025-10-12T10:00:00.000000Z",
    "updated_at": "2025-10-12T10:00:00.000000Z",
    "motif_rejet": null,
    "valide_par": null,
    "valider_a": null,
    "demandeur": {
      "nom": "Dupont",
      "prenoms": "Jean",
      "date_naissance": "1990-01-15",
      "sexe": "M",
      "profession": "Ingénieur",
      "contact": "+237690000000",
      "email": "jean.dupont@example.com",
      "photo": "https://example.com/storage/users/photo.jpg",
      "adresse": "Yaoundé, Cameroun"
    },
    "contrat_propose": null,
    "reponses_questionnaire": [
      {
        "question_id": 1,
        "question": "Avez-vous des antécédents médicaux ?",
        "type_donnee": "boolean",
        "reponse": true
      },
      {
        "question_id": 2,
        "question": "Quelle est votre taille (en cm) ?",
        "type_donnee": "number",
        "reponse": 175
      }
    ],
    "statistiques": {
      "nombre_beneficiaires": 2,
      "repartition_par_sexe": {
        "M": 1,
        "F": 1
      },
      "repartition_par_age": {
        "0-18": 1,
        "19-35": 0,
        "36-60": 1,
        "60+": 0
      }
    }
  }
}
```

---

### 5. Accepter une proposition de contrat

Permet à un client d'accepter une proposition de contrat qui lui a été faite par un technicien.

```http
POST /client/contrats-proposes/{proposition_id}/accepter
```

**Rôles requis**: `client`, `entreprise`

#### Paramètres URL

| Paramètre | Type | Description |
|-----------|------|-------------|
| `proposition_id` | integer | ID de la proposition de contrat |

#### Réponse (200 OK)

```json
{
  "success": true,
  "message": "Contrat accepté avec succès",
  "data": {
    "contrat_id": 15,
    "message": "Contrat accepté avec succès"
  }
}
```

#### Erreurs possibles

**403 - Accès non autorisé**
```json
{
  "success": false,
  "message": "Accès non autorisé",
  "data": null
}
```

**400 - Proposition déjà traitée**
```json
{
  "success": false,
  "message": "Cette proposition a déjà été traitée",
  "data": null
}
```

---

### 6. Rejeter une demande d'adhésion (Personnel uniquement)

Permet au personnel (technicien, médecin contrôleur) de rejeter une demande d'adhésion.

```http
PUT /demandes-adhesions/{id}/rejeter
```

**Rôles requis**: `technicien`, `medecin_controleur`

#### Corps de la requête

```json
{
  "motif_rejet": "Documents incomplets ou informations non conformes."
}
```

#### Paramètres

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `motif_rejet` | string | ✅ | Motif du rejet (min: 10 caractères) |

#### Réponse (200 OK)

```json
{
  "success": true,
  "message": "Demande d'adhésion rejetée avec succès",
  "data": {
    "demande_id": 1,
    "statut": "rejetee",
    "rejetee_par": "Dr. Martin Dupont"
  }
}
```

---

### 7. Télécharger les documents d'une demande

Permet de télécharger un PDF récapitulatif d'une demande d'adhésion.

```http
GET /download/demande-adhesion/{id}
```

**Authentification**: Requise

#### Réponse

Fichier PDF téléchargeable

---

## Modèles de données

### DemandeAdhesion

```php
{
  "id": integer,
  "user_id": integer,
  "type_demandeur": "client" | "prestataire",
  "statut": "en_attente" | "proposee" | "acceptee" | "validee" | "rejetee",
  "motif_rejet": string | null,
  "valide_par_id": integer | null,
  "valider_a": datetime | null,
  "created_at": datetime,
  "updated_at": datetime
}
```

### Assure (Assuré Principal)

```php
{
  "id": integer,
  "user_id": integer,
  "client_id": integer,
  "lien_parente": "principal",
  "est_principal": true,
  "assure_principal_id": null
}
```

### Assure (Bénéficiaire)

```php
{
  "id": integer,
  "user_id": integer,
  "client_id": integer,
  "lien_parente": "conjoint" | "enfant" | "parent" | "autre",
  "est_principal": false,
  "assure_principal_id": integer
}
```

### ReponseQuestion

```php
{
  "id": integer,
  "question_id": integer,
  "demande_adhesion_id": integer,
  "assure_id": integer,
  "reponse": string,  // Contenu sérialisé selon le type
  "date_reponse": date,
  "created_at": datetime,
  "updated_at": datetime
}
```

### Question

```php
{
  "id": integer,
  "libelle": string,
  "type_de_donnee": "text" | "number" | "boolean" | "date" | "radio" | "file",
  "options": array | null,
  "destinataire": string,
  "est_obligatoire": boolean,
  "est_active": boolean,
  "cree_par_id": integer,
  "created_at": datetime,
  "updated_at": datetime
}
```

---

## Règles de validation

### Demande d'adhésion

| Champ | Règles | Description |
|-------|--------|-------------|
| `type_demandeur` | `required`, `in:client,prestataire` | Type de demandeur |
| `reponses` | `required`, `array` | Tableau de réponses |
| `reponses.*.question_id` | `required`, `exists:questions,id` | ID de question valide |
| `reponses.*.reponse` | Dépend du type | Validation dynamique selon le type de question |

### Réponses selon le type de question

| Type | Règles de validation |
|------|---------------------|
| `text`, `radio` | `string` |
| `number` | `numeric` |
| `boolean` | `boolean` |
| `date` | `date` |
| `file` | `file`, `mimes:jpeg,png,pdf,jpg`, `max:2048` (Ko) |

### Bénéficiaires

| Champ | Règles | Description |
|-------|--------|-------------|
| `beneficiaires` | `nullable`, `array` | Tableau optionnel |
| `beneficiaires.*.nom` | `required`, `string` | Nom requis |
| `beneficiaires.*.prenoms` | `nullable`, `string` | Prénoms optionnels |
| `beneficiaires.*.date_naissance` | `required`, `date` | Date de naissance requise |
| `beneficiaires.*.sexe` | `required`, `in:M,F` | Sexe (M ou F) |
| `beneficiaires.*.email` | `nullable`, `email` | Email valide |
| `beneficiaires.*.contact` | `nullable`, `string` | Numéro de téléphone |
| `beneficiaires.*.profession` | `nullable`, `string` | Profession |
| `beneficiaires.*.photo_url` | `required`, `file`, `mimes:jpeg,png,jpg`, `max:5120` | Photo (max 5 Mo) |
| `beneficiaires.*.lien_parente` | `required`, `in:conjoint,enfant,parent,autre` | Lien de parenté |
| `beneficiaires.*.reponses` | `required`, `array` | Réponses au questionnaire |

---

## Enums

### TypeDemandeurEnum

```php
- CLIENT = 'client'          // Personne physique
- PRESTATAIRE = 'prestataire' // Prestataire de soins
```

### StatutDemandeAdhesionEnum

```php
- EN_ATTENTE = 'en_attente'  // En attente de traitement
- PROPOSEE = 'proposee'      // Contrat proposé
- ACCEPTEE = 'acceptee'      // Contrat accepté
- VALIDEE = 'validee'        // Demande validée
- REJETEE = 'rejetee'        // Demande rejetée
```

### LienParenteEnum

```php
- PRINCIPAL = 'principal'    // Assuré principal
- CONJOINT = 'conjoint'      // Conjoint(e)
- ENFANT = 'enfant'          // Enfant
- PARENT = 'parent'          // Parent
- AUTRE = 'autre'            // Autre lien
```

### TypeDonneeEnum (Types de questions)

```php
- TEXT = 'text'              // Texte libre
- NUMBER = 'number'          // Nombre
- BOOLEAN = 'boolean'        // Oui/Non
- DATE = 'date'              // Date
- SELECT = 'select'          // Liste déroulante
- CHECKBOX = 'checkbox'      // Cases à cocher
- RADIO = 'radio'            // Boutons radio
- FILE = 'file'              // Fichier
```

---

## Flux de traitement

### 1. Flux client (Personne physique)

```mermaid
graph TD
    A[Client s'inscrit] --> B[Client se connecte]
    B --> C[Vérifie s'il a une demande: GET /demandes-adhesions/has-demande]
    C --> D{Demande existante?}
    D -->|Non| E[Soumet demande: POST /demandes-adhesions/client]
    D -->|Oui - Rejetée| E
    D -->|Oui - En cours| F[Attend traitement]
    E --> G[Demande créée - Statut: en_attente]
    G --> H[Email de confirmation envoyé]
    H --> I[Personnel traite la demande]
    I --> J{Décision}
    J -->|Acceptée| K[Technicien propose contrat]
    J -->|Rejetée| L[Demande rejetée - Email envoyé]
    K --> M[Client consulte proposition]
    M --> N{Client accepte?}
    N -->|Oui| O[POST /client/contrats-proposes/{id}/accepter]
    N -->|Non| P[Client refuse]
    O --> Q[Contrat activé - Statut: acceptee]
    Q --> R[Notifications envoyées]
    L --> S[Client peut resoumettre]
```

### 2. Flux technicien

```mermaid
graph TD
    A[Technicien se connecte] --> B[Consulte demandes: GET /demandes-adhesions]
    B --> C[Filtre demandes type: client]
    C --> D[Sélectionne une demande]
    D --> E[GET /demandes-adhesions/{id}]
    E --> F[Examine questionnaire et bénéficiaires]
    F --> G{Décision}
    G -->|Valider| H[Propose un contrat]
    G -->|Rejeter| I[PUT /demandes-adhesions/{id}/rejeter]
    H --> J[Statut: proposee]
    I --> K[Statut: rejetee]
```

---

## Exemples de requêtes

### Exemple 1 : Soumission simple (sans bénéficiaires)

```bash
curl -X POST "https://api.example.com/api/v1/demandes-adhesions/client" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "type_demandeur": "client",
    "reponses": [
      {
        "question_id": 1,
        "reponse": "Non"
      },
      {
        "question_id": 2,
        "reponse": 175
      },
      {
        "question_id": 3,
        "reponse": true
      }
    ]
  }'
```

### Exemple 2 : Soumission avec bénéficiaires et fichiers

Utilisation de `multipart/form-data` :

```bash
curl -X POST "https://api.example.com/api/v1/demandes-adhesions/client" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Api-Key: YOUR_API_KEY" \
  -F "type_demandeur=client" \
  -F "reponses[0][question_id]=1" \
  -F "reponses[0][reponse]=Non" \
  -F "reponses[1][question_id]=5" \
  -F "reponses[1][reponse]=@/path/to/document.pdf" \
  -F "beneficiaires[0][nom]=Doe" \
  -F "beneficiaires[0][prenoms]=Jane" \
  -F "beneficiaires[0][date_naissance]=1995-05-15" \
  -F "beneficiaires[0][sexe]=F" \
  -F "beneficiaires[0][lien_parente]=conjoint" \
  -F "beneficiaires[0][photo_url]=@/path/to/photo.jpg" \
  -F "beneficiaires[0][reponses][0][question_id]=1" \
  -F "beneficiaires[0][reponses][0][reponse]=Non"
```

### Exemple 3 : Vérifier l'existence d'une demande

```bash
curl -X GET "https://api.example.com/api/v1/demandes-adhesions/has-demande" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Api-Key: YOUR_API_KEY"
```

### Exemple 4 : Accepter une proposition de contrat

```bash
curl -X POST "https://api.example.com/api/v1/client/contrats-proposes/5/accepter" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Api-Key: YOUR_API_KEY"
```

### Exemple 5 : Rejeter une demande (Technicien)

```bash
curl -X PUT "https://api.example.com/api/v1/demandes-adhesions/1/rejeter" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "motif_rejet": "Documents médicaux incomplets. Veuillez fournir les résultats d'analyses manquants."
  }'
```

---

## Codes d'erreur

| Code | Description | Solution |
|------|-------------|----------|
| 400 | Demande en cours ou déjà validée | Attendre la réponse ou ne pas resoumettre |
| 401 | Non authentifié | Fournir un token JWT valide |
| 403 | Accès interdit | Vérifier le rôle de l'utilisateur |
| 404 | Demande non trouvée | Vérifier l'ID de la demande |
| 422 | Erreur de validation | Corriger les champs invalides |
| 500 | Erreur serveur | Contacter le support technique |

---

## Notes importantes

### 1. Questions dynamiques

Les questions affichées dépendent du type de demandeur (`client` ou `prestataire`). Pour récupérer les questions applicables :

```bash
GET /questions?destinataire=client
```

### 2. Gestion des fichiers

- Les fichiers doivent être envoyés en `multipart/form-data`
- Taille maximale : 
  - Photos de bénéficiaires : **5 Mo**
  - Documents (réponses type `file`) : **2 Mo**
- Formats acceptés :
  - Images : JPEG, PNG, JPG
  - Documents : PDF, JPEG, PNG, JPG

### 3. Obligations

- Un client ne peut avoir qu'**une seule demande active** à la fois
- Une fois validée, une demande **ne peut pas être modifiée**
- En cas de rejet, le client peut **resoumettre** une nouvelle demande

### 4. Notifications

Le système envoie automatiquement des notifications par email :
- **À la soumission** : Confirmation de réception
- **En cas de rejet** : Notification avec motif
- **Proposition de contrat** : Lien pour consulter et accepter
- **Acceptation du contrat** : Confirmation d'activation

### 5. Bénéficiaires

- Le nombre de bénéficiaires est **illimité**
- Chaque bénéficiaire doit avoir sa propre photo
- Les bénéficiaires doivent répondre au même questionnaire que l'assuré principal

---

## Support

Pour toute question ou problème technique, contactez :
- **Email** : support@amp-assurance.com
- **Téléphone** : +237 6XX XX XX XX

---

## Changelog

### Version 1.0 (Octobre 2025)
- Documentation initiale
- Endpoints de base pour les demandes d'adhésion
- Gestion des bénéficiaires
- Système de questionnaire dynamique

---

**Dernière mise à jour** : 12 octobre 2025

