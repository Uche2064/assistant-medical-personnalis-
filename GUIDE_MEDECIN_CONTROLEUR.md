# Guide d'Utilisation - Module Médecin Contrôleur 🩺

## 🎯 Rôle et Responsabilités

Le médecin contrôleur est responsable de :
1. **Créer et gérer les questions** pour les prestataires
2. **Gérer les garanties** et catégories de garanties
3. **Valider les prestataires** de soins
4. **Valider les factures** d'un point de vue médical

---

## 📚 1. Gestion des Questions

### Pourquoi des questions ?
Les questions permettent de collecter des informations spécifiques auprès des prestataires lors de leur inscription (spécialité, équipements, expérience, etc.).

### Types de questions disponibles

| Type | Description | Options requises |
|------|-------------|------------------|
| **text** | Texte court | Non |
| **textarea** | Texte long | Non |
| **number** | Nombre | Non |
| **date** | Date | Non |
| **email** | Email | Non |
| **tel** | Téléphone | Non |
| **select** | Liste déroulante | Oui |
| **checkbox** | Cases à cocher | Oui |
| **radio** | Boutons radio | Oui |
| **file** | Fichier | Non |

### Créer des questions

#### Méthode 1 : Création en masse (Recommandé)

```json
POST /v1/questions

[
    {
        "libelle": "Quelle est votre spécialité médicale ?",
        "type_de_donnee": "select",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true,
        "options": ["Médecine générale", "Pédiatrie", "Cardiologie", "Dermatologie"]
    },
    {
        "libelle": "Nombre d'années d'expérience",
        "type_de_donnee": "number",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true
    },
    {
        "libelle": "Disposez-vous d'un équipement de radiologie ?",
        "type_de_donnee": "radio",
        "destinataire": "prestataire",
        "est_obligatoire": false,
        "est_active": true,
        "options": ["Oui", "Non"]
    }
]
```

**Avantages** :
- ✅ Plus rapide (une seule requête)
- ✅ Optimisé pour les performances
- ✅ Idéal pour l'initialisation

### Consulter les questions

```http
GET /v1/questions
GET /v1/questions?destinataire=prestataire
```

### Modifier une question

```json
PUT /v1/questions/{id}

{
    "libelle": "Quelle est votre spécialité médicale principale ?",
    "options": ["Médecine générale", "Pédiatrie", "Cardiologie", "Dermatologie", "Autre"]
}
```

### Supprimer des questions

```http
DELETE /v1/questions/{id}
```

Ou en masse :
```json
DELETE /v1/questions/bulk-delete

{
    "ids": [1, 2, 3, 4, 5]
}
```

---

## 🛡️ 2. Gestion des Garanties

### Qu'est-ce qu'une garantie ?
Une garantie définit un type de soin couvert par l'assurance avec un montant maximum de remboursement.

### Créer une garantie

```json
POST /v1/garanties

{
    "libelle": "Consultation générale",
    "description": "Consultation médicale générale",
    "montant_max": 50000,
    "est_active": true
}
```

### Exemples de garanties courantes

- **Consultation générale** : 50 000 FCFA
- **Hospitalisation** : 500 000 FCFA
- **Radiologie** : 100 000 FCFA
- **Analyses biologiques** : 75 000 FCFA
- **Soins dentaires** : 150 000 FCFA
- **Optique** : 200 000 FCFA

---

## 📦 3. Gestion des Catégories de Garanties

### Qu'est-ce qu'une catégorie ?
Une catégorie regroupe plusieurs garanties liées (ex: "Soins dentaires" regroupe détartrage, extraction, prothèse, etc.).

### Créer une catégorie

```json
POST /v1/categories-garanties

{
    "nom": "Soins dentaires",
    "description": "Catégorie regroupant tous les soins dentaires",
    "garanties": [1, 2, 3, 4]
}
```

### Exemples de catégories

- **Soins dentaires** : Détartrage, extraction, prothèse, orthodontie
- **Soins optiques** : Lunettes, lentilles, examens de vue
- **Hospitalisation** : Chambre, soins infirmiers, médicaments
- **Maternité** : Consultation prénatale, accouchement, césarienne
- **Analyses** : Analyses sanguines, radiologie, échographie

---

## ✅ 4. Validation des Prestataires

### Workflow

1. **Consulter les demandes**
```http
GET /v1/demandes-adhesions?type_demandeur=prestataire
```

2. **Voir les détails et réponses**
```http
GET /v1/demandes-adhesions/{id}
```

3. **Valider le prestataire**
```json
PUT /v1/demandes-adhesions/{id}/valider-prestataire

{
    "commentaire": "Prestataire validé après vérification des documents et qualifications médicales"
}
```

**Résultat** :
- ✅ Compte prestataire créé
- ✅ Email envoyé avec identifiants
- ✅ Prestataire peut se connecter
- ✅ Notifications envoyées

4. **Ou rejeter si non conforme**
```json
PUT /v1/demandes-adhesions/{id}/rejeter

{
    "motif_rejet": "Documents incomplets ou qualifications non conformes"
}
```

---

## 💰 5. Validation des Factures

### Workflow de validation (3 étapes)

```
Prestataire soumet facture
    ↓
1️⃣ Technicien valide (vérification technique)
    ↓
2️⃣ Médecin contrôleur valide (vérification médicale) ← VOUS ÊTES ICI
    ↓
3️⃣ Comptable autorise (remboursement)
```

### Valider une facture

**Prérequis** : La facture doit être validée par un technicien

```json
POST /v1/factures/{id}/validate-medecin

{
    "commentaire": "Actes médicaux conformes et justifiés"
}
```

### Rejeter une facture

```json
POST /v1/factures/{id}/reject-medecin

{
    "motif_rejet": "Actes médicaux non conformes ou non justifiés"
}
```

---

## 📊 6. Statistiques des Questions

```http
GET /v1/questions/stats
```

**Réponse** :
```json
{
    "success": true,
    "data": {
        "total": 25,
        "actives": 20,
        "inactives": 5,
        "obligatoires": 15,
        "optionnelles": 10,
        "repartition_par_destinataire": {
            "prestataire": 18,
            "client": 5,
            "autre": 2
        }
    }
}
```

---

## 🎯 Cas d'Usage Pratiques

### Cas 1 : Initialiser le système

1. Créer les garanties de base
2. Créer les catégories de garanties
3. Créer les questions pour prestataires
4. Activer toutes les garanties

### Cas 2 : Valider un nouveau prestataire

1. Consulter les demandes en attente
2. Vérifier les réponses aux questions
3. Vérifier les documents
4. Valider ou rejeter avec commentaire

### Cas 3 : Valider une facture

1. Consulter les factures validées par technicien
2. Vérifier les actes médicaux
3. Vérifier les montants
4. Valider ou rejeter avec commentaire

---

## ⚠️ Erreurs Courantes

### 1. Question sans options pour select/checkbox/radio
```json
{
    "success": false,
    "message": "Erreur de validation",
    "data": {
        "options": ["Le champ options est obligatoire pour les types select, checkbox ou radio"]
    }
}
```

**Solution** : Ajouter le champ `options` avec un tableau de valeurs

### 2. Facture non validée par technicien
```json
{
    "success": false,
    "message": "Cette facture doit d'abord être validée par un technicien"
}
```

**Solution** : Attendre la validation du technicien

### 3. Accès non autorisé
```json
{
    "success": false,
    "message": "Accès non autorisé. Seuls les médecins contrôleurs peuvent..."
}
```

**Solution** : Vérifier que vous êtes connecté avec le bon rôle

---

## 🔒 Permissions Partagées

Certaines fonctionnalités sont partagées avec d'autres rôles :

### Garanties et Catégories
- ✅ **Médecin Contrôleur** : CRUD complet
- ✅ **Technicien** : CRUD complet

### Demandes d'Adhésion
- ✅ **Médecin Contrôleur** : Validation prestataires uniquement
- ✅ **Technicien** : Validation clients et autres

### Consultation
- ✅ **Médecin Contrôleur** : Accès aux assurés
- ✅ **Technicien** : Accès aux assurés
- ✅ **Comptable** : Accès aux assurés
- ✅ **Admin** : Accès complet

---

## 🚀 Bonnes Pratiques

### Pour les Questions
1. **Utilisez l'insertion en masse** pour créer plusieurs questions
2. **Définissez clairement** les questions obligatoires
3. **Testez les options** pour select/checkbox/radio
4. **Désactivez** au lieu de supprimer (historique)

### Pour les Garanties
1. **Définissez des montants réalistes**
2. **Organisez par catégories** logiques
3. **Activez progressivement** les garanties
4. **Documentez** les descriptions

### Pour la Validation
1. **Vérifiez tous les documents** avant validation
2. **Donnez des commentaires clairs** en cas de rejet
3. **Validez rapidement** pour ne pas bloquer le processus
4. **Communiquez** avec les techniciens si doute

---

## 📱 Intégration Frontend

### Composants suggérés

1. **Questions Manager**
   - Liste des questions avec filtres
   - Formulaire de création en masse
   - Édition inline
   - Suppression avec confirmation

2. **Garanties Manager**
   - Liste des garanties par catégorie
   - Formulaire de création
   - Toggle actif/inactif
   - Recherche et filtres

3. **Validation Prestataires**
   - Liste des demandes en attente
   - Vue détaillée avec réponses
   - Boutons Valider/Rejeter
   - Formulaire de commentaire

4. **Validation Factures**
   - Liste des factures à valider
   - Détails des actes médicaux
   - Boutons Valider/Rejeter
   - Historique des validations

---

## 📊 Dashboard Médecin Contrôleur (Suggéré)

```
┌─────────────────────────────────────────────────────────┐
│            DASHBOARD MÉDECIN CONTRÔLEUR                  │
├─────────────────────────────────────────────────────────┤
│  [KPI]        [KPI]         [KPI]        [KPI]         │
│  Questions   Garanties   Prestataires  Factures        │
│   Active     Actives     En attente    À valider       │
├──────────────────────────┬──────────────────────────────┤
│                          │                              │
│  Demandes en Attente     │  Factures à Valider          │
│  [Liste avec actions]    │  [Liste avec actions]        │
│                          │                              │
├──────────────────────────┴──────────────────────────────┤
│                                                          │
│  Statistiques des Questions                              │
│  [Graphiques répartition]                                │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

---

## ✅ Checklist de Démarrage

### Configuration Initiale
- [ ] Créer les garanties de base
- [ ] Créer les catégories de garanties
- [ ] Créer les questions pour prestataires
- [ ] Activer toutes les garanties
- [ ] Tester le workflow de validation

### Utilisation Quotidienne
- [ ] Consulter les demandes prestataires en attente
- [ ] Valider ou rejeter les demandes
- [ ] Consulter les factures à valider
- [ ] Valider ou rejeter les factures
- [ ] Gérer les questions si besoin

---

## 🎓 Exemples Pratiques

### Exemple 1 : Créer un questionnaire complet pour prestataires

```json
POST /v1/questions

[
    {
        "libelle": "Quelle est votre spécialité médicale ?",
        "type_de_donnee": "select",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true,
        "options": [
            "Médecine générale",
            "Pédiatrie",
            "Cardiologie",
            "Dermatologie",
            "Gynécologie",
            "Ophtalmologie",
            "ORL",
            "Autre"
        ]
    },
    {
        "libelle": "Nombre d'années d'expérience",
        "type_de_donnee": "number",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true
    },
    {
        "libelle": "Numéro d'ordre des médecins",
        "type_de_donnee": "text",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true
    },
    {
        "libelle": "Équipements disponibles",
        "type_de_donnee": "checkbox",
        "destinataire": "prestataire",
        "est_obligatoire": false,
        "est_active": true,
        "options": [
            "Radiologie",
            "Échographie",
            "ECG",
            "Laboratoire",
            "Bloc opératoire",
            "Ambulance"
        ]
    },
    {
        "libelle": "Horaires d'ouverture",
        "type_de_donnee": "textarea",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true
    }
]
```

### Exemple 2 : Créer un système de garanties complet

**Étape 1 : Créer les garanties**
```json
POST /v1/garanties

{
    "libelle": "Consultation générale",
    "description": "Consultation médicale générale",
    "montant_max": 50000,
    "est_active": true
}
```

**Étape 2 : Créer les catégories**
```json
POST /v1/categories-garanties

{
    "nom": "Consultations",
    "description": "Toutes les consultations médicales",
    "garanties": [1, 2, 3]
}
```

---

## 🎯 Conseils d'Expert

### Pour les Questions
1. **Commencez simple** : Créez d'abord les questions essentielles
2. **Testez les options** : Vérifiez que les options sont claires
3. **Évitez les doublons** : Vérifiez avant de créer
4. **Utilisez des libellés clairs** : Questions compréhensibles

### Pour les Garanties
1. **Montants réalistes** : Basés sur les coûts moyens
2. **Descriptions précises** : Éviter les ambiguïtés
3. **Organisation logique** : Catégories cohérentes
4. **Mise à jour régulière** : Ajuster selon les besoins

### Pour la Validation
1. **Vérification complète** : Tous les documents
2. **Commentaires constructifs** : Aider à améliorer
3. **Rapidité** : Ne pas bloquer le processus
4. **Communication** : Contacter si doute

---

## 📞 Support

Pour toute question :
- Consulter `MEDECIN_CONTROLEUR_DOCUMENTATION.md`
- Utiliser la collection Postman pour tester
- Contacter l'équipe technique si problème

---

## ✨ Résumé

Le module Médecin Contrôleur est essentiel pour :
- ✅ Garantir la qualité des prestataires
- ✅ Vérifier la conformité médicale des factures
- ✅ Gérer les garanties et catégories
- ✅ Collecter les informations nécessaires via questions

**Collection Postman** : `20_Medecin_Controleur_Module.postman_collection.json`

Bonne utilisation ! 🚀
