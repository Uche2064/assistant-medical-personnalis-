# 📋 LOGIQUE DES DEMANDES D'ADHÉSION - SUNU SANTÉ

## 🎯 **Vue d'ensemble**

Les demandes d'adhésion varient selon le type de demandeur. Chaque type a sa propre logique de soumission et de traitement.

---

## 📊 **TYPES DE DEMANDEURS**

### **1. ENTREPRISE** 
- **Rôle** : Client moral qui soumet ses employés
- **Questionnaire demandeur** : ❌ **AUCUN** - L'entreprise ne répond pas à de questionnaire
- **Action** : Soumet la liste de ses employés avec leurs questionnaires respectifs
- **Structure** :
  ```
  DemandeAdhesion (entreprise)
  ├── User (compte entreprise)
  ├── Entreprise (données entreprise)
  └── Employés (liste des employés)
      ├── Employé 1 + Questionnaire médical
      ├── Employé 2 + Questionnaire médical
      └── ...
  ```

### **2. PHYSIQUE**
- **Rôle** : Client personne physique
- **Questionnaire demandeur** : ✅ **OUI** - Répond à un questionnaire médical
- **Action** : Soumet ses propres réponses + peut ajouter des bénéficiaires
- **Structure** :
  ```
  DemandeAdhesion (physique)
  ├── User (compte personne physique)
  ├── Client (données client)
  ├── Réponses questionnaire (demandeur principal)
  └── Bénéficiaires (optionnel)
      ├── Bénéficiaire 1 + Questionnaire médical
      ├── Bénéficiaire 2 + Questionnaire médical
      └── ...
  ```

### **3. PRESTATAIRE**
- **Rôle** : Centre de soins, laboratoire, pharmacie, etc.
- **Questionnaire demandeur** : ✅ **OUI** - Répond à un questionnaire spécifique prestataire
- **Action** : Soumet ses propres réponses (pas de bénéficiaires)
- **Structure** :
  ```
  DemandeAdhesion (prestataire)
  ├── User (compte prestataire)
  ├── Prestataire (données établissement)
  └── Réponses questionnaire (prestataire)
  ```

---

## 🔍 **DONNÉES RETOURNÉES PAR L'API**

### **Pour une demande ENTREPRISE**
```json
{
  "demande": {
    "type_demandeur": "entreprise",
    "statut": "en_attente"
  },
  "demandeur": {
    "user": { /* compte entreprise */ },
    "entreprise": {
      "raison_sociale": "Entreprise ABC",
      "siret": "12345678901234",
      "adresse_siege": "123 Rue de la Paix"
    },
    "reponses_questionnaire": [] // ❌ VIDE - entreprise ne répond pas
  },
  "personnes_associees": {
    "employes": [
      {
        "nom": "Dupont",
        "prenoms": "Jean",
        "reponses_questionnaire": [ /* réponses médicales */ ]
      }
    ],
    "beneficiaires": [] // ❌ VIDE - pas de bénéficiaires pour entreprise
  }
}
```

### **Pour une demande PHYSIQUE**
```json
{
  "demande": {
    "type_demandeur": "physique",
    "statut": "en_attente"
  },
  "demandeur": {
    "user": { /* compte personne physique */ },
    "client": { /* données client */ },
    "reponses_questionnaire": [ /* réponses du demandeur principal */ ]
  },
  "personnes_associees": {
    "employes": [], // ❌ VIDE - pas d'employés pour personne physique
    "beneficiaires": [
      {
        "nom": "Martin",
        "prenoms": "Marie",
        "reponses_questionnaire": [ /* réponses médicales */ ]
      }
    ]
  }
}
```

### **Pour une demande PRESTATAIRE**
```json
{
  "demande": {
    "type_demandeur": "prestataire",
    "statut": "en_attente"
  },
  "demandeur": {
    "user": { /* compte prestataire */ },
    "prestataire": {
      "nom_etablissement": "Centre Médical ABC",
      "type_prestataire": "centre_soins"
    },
    "reponses_questionnaire": [ /* réponses prestataire */ ]
  },
  "personnes_associees": {
    "employes": [], // ❌ VIDE - pas d'employés pour prestataire
    "beneficiaires": [] // ❌ VIDE - pas de bénéficiaires pour prestataire
  }
}
```

---

## 🛠️ **IMPLÉMENTATION TECHNIQUE**

### **Dans DemandeAdhesionDataTrait**
```php
protected function getDemandeurReponses(DemandeAdhesion $demande)
{
    // Pour une entreprise, pas de réponses questionnaire du demandeur principal
    // L'entreprise soumet juste la liste de ses employés
    if ($demande->type_demandeur === 'entreprise') {
        return [];
    }

    // Pour les autres types (physique, prestataire), retourner les réponses du demandeur
    return $demande->reponsesQuestionnaire;
}
```

### **Relations dans le modèle DemandeAdhesion**
```php
// Relations selon le type de demandeur
public function employes()
{
    return $this->hasManyThrough(Assure::class, User::class, ...)
        ->where('est_principal', true); // Employés = assurés principaux
}

public function beneficiaires()
{
    return $this->hasManyThrough(Assure::class, User::class, ...)
        ->where('est_principal', false); // Bénéficiaires = assurés non principaux
}
```

---

## ✅ **VALIDATION**

Cette logique garantit que :

1. **Entreprises** : Pas de questionnaire demandeur, seulement les employés
2. **Personnes physiques** : Questionnaire demandeur + bénéficiaires optionnels
3. **Prestataires** : Questionnaire demandeur spécifique, pas de bénéficiaires
4. **Données cohérentes** : Chaque type retourne les bonnes informations
5. **API claire** : Structure de réponse adaptée au type de demandeur

---

## 🎯 **AVANTAGES**

- ✅ **Logique métier respectée** : Chaque type a sa propre logique
- ✅ **Données pertinentes** : Seules les informations utiles sont retournées
- ✅ **Performance optimisée** : Pas de chargement inutile de données
- ✅ **Maintenance facilitée** : Code clair et documenté
- ✅ **Évolutivité** : Facile d'ajouter de nouveaux types de demandeurs 