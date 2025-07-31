# Flow Détaillé - Demande d'Adhésion jusqu'à la Validation

Ce document détaille le processus complet de demande d'adhésion dans le système SUNU Santé, de la soumission jusqu'à la validation finale.

## 📋 Vue d'ensemble du processus

```
Soumission → Validation → Proposition Contrat → Acceptation → Finalisation
```

## 🎯 Types de demandeurs

### 1. **Clients Physiques** (`physique`)
- Personnes physiques souhaitant s'assurer
- Géré par les **Techniciens**
- Flow : Soumission → Proposition Contrat → Acceptation

### 2. **Entreprises** (`autre`)
- Entreprises souhaitant assurer leurs employés
- Géré par les **Techniciens**
- Flow : Invitation Employés → Soumission Fiches → Soumission Demande → Proposition Contrat → Acceptation

### 3. **Prestataires de Soins**
- **Centre de Soins** (`centre_de_soins`)
- **Laboratoire/Centre de Diagnostic** (`laboratoire_centre_diagnostic`)
- **Pharmacie** (`pharmacie`)
- **Optique** (`optique`)
- Géré par les **Médecins Contrôleurs**
- Flow : Soumission → Validation → Finalisation

---

## 🔄 Flow Détaillé par Type

### **A. Flow pour Clients Physiques**

#### **Étape 1 : Soumission de la demande**
```http
POST /api/v1/demandes-adhesions/
```

**Données requises :**
```json
{
  "type_demandeur": "physique" | "autre",
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Réponse à la question",
      "reponse_number": 25,
      "reponse_bool": true,
      "reponse_date": "2024-01-15"
    }
  ],
  "beneficiaires": [
    {
      "nom": "Dupont",
      "prenoms": "Marie",
      "date_naissance": "1990-05-15",
      "sexe": "F",
      "lien_parente": "conjoint",
      "reponses": [
        {
          "question_id": 2,
          "reponse_text": "Réponse bénéficiaire"
        }
      ]
    }
  ]
}
```

**Actions système :**
1. ✅ Vérification si l'utilisateur a déjà une demande en cours
2. ✅ Vérification si l'utilisateur a déjà une demande validée
3. ✅ Création de la demande avec statut `en_attente`
4. ✅ Enregistrement des réponses au questionnaire principal
5. ✅ Enregistrement des bénéficiaires (si fournis)
6. ✅ Envoi d'email de confirmation

**Statut après soumission :** `en_attente`

---

#### **Étape 2 : Traitement par le Technicien**

**Le technicien peut :**
- 📋 Consulter la liste des demandes en attente
- 📄 Télécharger le PDF de la demande
- ✅ Proposer un contrat
- ❌ Rejeter la demande

**Route pour proposer un contrat :**
```http
PUT /api/v1/demandes-adhesions/{demande_id}/proposer-contrat
```

**Données requises :**
```json
{
  "contrat_id": 1,
  "prime_proposee": 50000,
  "taux_couverture": 80,
  "frais_gestion": 20,
  "commentaires": "Contrat adapté au profil",
  "garanties_incluses": [1, 2, 3]
}
```

**Actions système :**
1. ✅ Vérification que la demande est en attente
2. ✅ Vérification que le contrat est valide et actif
3. ✅ Création de la proposition de contrat
4. ✅ Association des garanties
5. ✅ Génération d'un token d'acceptation (valable 7 jours)
6. ✅ Envoi d'email avec lien d'acceptation

**Statut après proposition :** `en_attente` (demande) + `proposee` (proposition)

---

#### **Étape 3 : Acceptation par le Client**

**Route d'acceptation :**
```http
POST /api/v1/contrats/accepter/{token}
```

**Actions système :**
1. ✅ Validation du token
2. ✅ Vérification de l'expiration du token
3. ✅ Création du client (si pas encore créé)
4. ✅ Création des assurés (principal + bénéficiaires)
5. ✅ Association au contrat
6. ✅ Mise à jour du statut de la demande
7. ✅ Envoi d'email de confirmation

**Statut après acceptation :** `validee`

---

### **C. Flow pour Entreprises**

#### **Étape 1 : Génération du lien d'invitation**
```http
POST /api/v1/entreprise/inviter-employe
```

**Actions système :**
1. ✅ Vérification que l'utilisateur est une entreprise
2. ✅ Génération d'un token d'invitation unique
3. ✅ Création d'un lien valable 7 jours
4. ✅ Retour de l'URL d'invitation

**Réponse :**
```json
{
  "invitation_id": 123,
  "url": "https://sunusante.com/employes/formulaire/abc123def456",
  "expire_at": "2024-01-22T10:30:00Z"
}
```

---

#### **Étape 2 : Invitation des employés**

**L'entreprise partage le lien avec ses employés :**
- 📧 Email avec le lien d'invitation
- 📱 WhatsApp, SMS, etc.
- 🖥️ Interface interne de l'entreprise

**Lien d'invitation :** `https://sunusante.com/employes/formulaire/{token}`

---

#### **Étape 3 : Soumission des fiches employés**

**Route pour récupérer le formulaire :**
```http
GET /api/v1/employes/formulaire/{token}
```

**Réponse :**
```json
{
  "entreprise": {
    "id": 1,
    "nom": "Entreprise ABC SARL"
  },
  "token": "abc123def456",
  "questions": [
    {
      "id": 1,
      "libelle": "Nom complet",
      "type_donnee": "text",
      "obligatoire": true
    }
  ],
  "fields": ["nom", "prenoms", "email", "date_naissance", "sexe", "contact", "profession", "adresse", "photo_url"]
}
```

**Route pour soumettre la fiche employé :**
```http
POST /api/v1/employes/formulaire/{token}
```

**Payload :**
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
    }
  ]
}
```

**Actions système :**
1. ✅ Validation du token d'invitation
2. ✅ Création du compte utilisateur employé
3. ✅ Création de l'assuré (employé)
4. ✅ Enregistrement des réponses au questionnaire
5. ✅ Upload de la photo (si fournie)
6. ✅ Notification à l'entreprise
7. ✅ Email de confirmation à l'employé

---

#### **Étape 4 : Soumission de la demande d'adhésion entreprise**

**Route :**
```http
POST /api/v1/entreprise/soumettre-demande-adhesion
```

**Payload :**
```json
{}
```

**Actions système :**
1. ✅ Vérification que l'utilisateur est une entreprise
2. ✅ Vérification qu'au moins un employé a soumis sa fiche
3. ✅ Création de la demande d'adhésion entreprise
4. ✅ Association de tous les employés à la demande
5. ✅ Notification aux techniciens
6. ✅ Envoi d'emails de notification

**Statut après soumission :** `en_attente`

---

#### **Étape 5 : Traitement par le Technicien**

**Le technicien peut :**
- 📋 Consulter la liste des demandes entreprises en attente
- 📄 Télécharger le PDF de la demande (avec tous les employés)
- ✅ Proposer un contrat groupé
- ❌ Rejeter la demande

**Route pour proposer un contrat :**
```http
PUT /api/v1/demandes-adhesions/{demande_id}/proposer-contrat
```

**Payload :**
```json
{
  "contrat_id": 1,
  "prime_proposee": 250000, // Prime totale pour tous les employés
  "taux_couverture": 80,
  "frais_gestion": 15,
  "commentaires": "Contrat groupé adapté à l'entreprise",
  "garanties_incluses": [1, 3, 5, 7]
}
```

**Actions système :**
1. ✅ Vérification que la demande est en attente
2. ✅ Vérification que le contrat est valide
3. ✅ Création de la proposition de contrat groupé
4. ✅ Association des garanties
5. ✅ Génération d'un token d'acceptation (7 jours)
6. ✅ Envoi d'email avec lien d'acceptation à l'entreprise

---

#### **Étape 6 : Acceptation par l'Entreprise**

**Route d'acceptation :**
```http
POST /api/v1/contrats/accepter/{token}
```

**Payload :**
```json
{
  "confirmation": true
}
```

**Actions système :**
1. ✅ Validation du token
2. ✅ Vérification de l'expiration
3. ✅ Création du client entreprise (si pas encore créé)
4. ✅ Association de tous les employés au contrat
5. ✅ Mise à jour du statut de la demande
6. ✅ Envoi d'email de confirmation
7. ✅ Notification à tous les employés

**Statut après acceptation :** `validee`

---

### **B. Flow pour Prestataires de Soins**

#### **Étape 1 : Soumission de la demande**
```http
POST /api/v1/demandes-adhesions/prestataire
```

**Données requises :**
```json
{
  "type_demandeur": "centre_de_soins" | "laboratoire_centre_diagnostic" | "pharmacie" | "optique",
  "reponses": [
    {
      "question_id": 1,
      "reponse_text": "Réponse prestataire",
      "reponse_fichier": "document.pdf"
    }
  ]
}
```

**Actions système :**
1. ✅ Vérification si l'utilisateur a déjà une demande en cours
2. ✅ Création de la demande avec statut `en_attente`
3. ✅ Enregistrement des réponses au questionnaire
4. ✅ Upload des documents requis
5. ✅ Envoi d'email de confirmation

**Statut après soumission :** `en_attente`

---

#### **Étape 2 : Traitement par le Médecin Contrôleur**

**Le médecin contrôleur peut :**
- 📋 Consulter la liste des demandes prestataires en attente
- 📄 Télécharger le PDF de la demande
- ✅ Valider la demande
- ❌ Rejeter la demande

**Route de validation :**
```http
PUT /api/v1/demandes-adhesions/{demande_id}/valider-prestataire
```

**Actions système :**
1. ✅ Vérification que la demande est en attente
2. ✅ Validation de la demande (statut → `validee`)
3. ✅ Enregistrement du médecin contrôleur qui a validé
4. ✅ Création du prestataire dans le système
5. ✅ Envoi d'email de validation
6. ✅ Création d'une notification

**Statut après validation :** `validee`

---

#### **Étape 3 : Rejet possible**

**Route de rejet :**
```http
PUT /api/v1/demandes-adhesions/{demande_id}/rejeter
```

**Données requises :**
```json
{
  "motif_rejet": "Documents incomplets ou non conformes"
}
```

**Actions système :**
1. ✅ Vérification que la demande est en attente
2. ✅ Rejet de la demande (statut → `rejetee`)
3. ✅ Enregistrement du motif de rejet
4. ✅ Envoi d'email de rejet
5. ✅ Création d'une notification

**Statut après rejet :** `rejetee`

---

## 📊 Statuts des Demandes

### **Enum StatutDemandeAdhesionEnum**

| Statut | Description | Couleur | Actions possibles |
|--------|-------------|---------|-------------------|
| `en_attente` | Demande soumise, en attente de traitement | ⚠️ Warning | Proposer contrat / Valider / Rejeter |
| `validee` | Demande acceptée et validée | ✅ Success | Aucune (finalisé) |
| `rejetee` | Demande refusée | ❌ Error | Aucune (finalisé) |

---

## 🔐 Permissions par Rôle

### **Technicien**
- ✅ Voir les demandes `physique` et `autre`
- ✅ Proposer des contrats
- ✅ Rejeter des demandes
- ❌ Voir les demandes prestataires
- ❌ Valider les prestataires

### **Médecin Contrôleur**
- ✅ Voir les demandes prestataires
- ✅ Valider les prestataires
- ✅ Rejeter les prestataires
- ❌ Voir les demandes `physique` et `autre`
- ❌ Proposer des contrats

### **Admin Global**
- ✅ Voir toutes les demandes
- ✅ Toutes les actions

---

## 📧 Notifications et Emails

### **Emails envoyés automatiquement :**

1. **Confirmation de soumission**
   - Template : `emails.demande_adhesion_physique`
   - Destinataire : Demandeur
   - Contenu : Confirmation de réception

2. **Proposition de contrat**
   - Template : `emails.contract_credentials`
   - Destinataire : Demandeur
   - Contenu : Lien d'acceptation + détails du contrat

3. **Validation prestataire**
   - Template : `emails.acceptee`
   - Destinataire : Prestataire
   - Contenu : Confirmation de validation

4. **Rejet de demande**
   - Template : `emails.rejetee`
   - Destinataire : Demandeur
   - Contenu : Motif du rejet

---

## 📄 Documents et PDF

### **Génération de PDF**
```http
GET /api/v1/demandes-adhesions/{id}/download
```

**Contenu du PDF :**
- Informations du demandeur
- Réponses au questionnaire
- Bénéficiaires (si applicable)
- Documents uploadés (prestataires)
- Horodatage de soumission

---

## 🔄 Workflow Complet

### **Pour Clients Physiques :**
```
Soumission → [En Attente] → Proposition Contrat → Acceptation → [Validée]
     ↓              ↓              ↓              ↓
  Email         Technicien    Email + Token   Création
Confirmation   Traite        Acceptation     Client/Assurés
```

### **Pour Entreprises :**
```
Invitation → Soumission Fiches → Demande → [En Attente] → Proposition → Acceptation → [Validée]
     ↓              ↓              ↓              ↓              ↓              ↓
  Lien         Employés      Entreprise    Technicien    Email + Token   Création
Invitation    Remplissent   Soumet        Traite        Acceptation     Contrat Groupé
```

### **Pour Prestataires :**
```
Soumission → [En Attente] → Validation → [Validée]
     ↓              ↓              ↓
  Email         Médecin        Email
Confirmation   Contrôleur    Validation
```

---

## ⚠️ Points d'attention

### **Validation des données :**
- ✅ Vérification de l'unicité des demandes
- ✅ Validation des réponses obligatoires
- ✅ Vérification des documents requis (prestataires)
- ✅ Contrôle des permissions par rôle

### **Sécurité :**
- 🔐 Tokens d'acceptation avec expiration
- 🔐 Validation des permissions par middleware
- 🔐 Logs de toutes les actions importantes

### **Performance :**
- ⚡ Transactions DB pour l'intégrité
- ⚡ Cache pour les tokens
- ⚡ Jobs en arrière-plan pour les emails

---

## 📈 Métriques et Statistiques

### **Statistiques disponibles :**
- Nombre total de demandes
- Répartition par statut
- Répartition par type de demandeur
- Évolution mensuelle
- Taux de validation/rejet

### **Route des statistiques :**
```http
GET /api/v1/demandes-adhesions/stats
```

---

## 🛠️ Routes API Principales

| Méthode | Route | Description | Rôle requis |
|---------|-------|-------------|-------------|
| `POST` | `/demandes-adhesions/` | Soumettre demande | `user` |
| `POST` | `/demandes-adhesions/prestataire` | Soumettre demande prestataire | `prestataire` |
| `POST` | `/entreprise/inviter-employe` | Générer lien invitation employé | `user` |
| `GET` | `/employes/formulaire/{token}` | Récupérer formulaire employé | Public |
| `POST` | `/employes/formulaire/{token}` | Soumettre fiche employé | Public |
| `POST` | `/entreprise/soumettre-demande-adhesion` | Soumettre demande entreprise | `user` |
| `GET` | `/demandes-adhesions/` | Lister les demandes | `medecin_controleur,technicien,admin_global` |
| `GET` | `/demandes-adhesions/{id}` | Voir une demande | `medecin_controleur,technicien,admin_global` |
| `GET` | `/demandes-adhesions/{id}/download` | Télécharger PDF | `medecin_controleur,technicien,admin_global` |
| `PUT` | `/demandes-adhesions/{id}/proposer-contrat` | Proposer contrat | `technicien` |
| `PUT` | `/demandes-adhesions/{id}/valider-prestataire` | Valider prestataire | `medecin_controleur` |
| `PUT` | `/demandes-adhesions/{id}/rejeter` | Rejeter demande | `technicien,medecin_controleur` |
| `POST` | `/contrats/accepter/{token}` | Accepter contrat | Public (avec token) |
| `GET` | `/demandes-adhesions/stats` | Statistiques | `admin_global,medecin_controleur,technicien` | 