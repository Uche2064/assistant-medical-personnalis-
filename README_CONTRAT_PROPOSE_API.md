# 📋 API - Détails du Contrat Proposé

## 🎯 Modification Apportée

Lors de la récupération des détails d'une demande d'adhésion pour les clients **physiques** et **entreprises**, l'API retourne maintenant les détails complets du contrat proposé.

## 📊 Structure de la Réponse

### Pour un Client Physique

**Endpoint :** `GET /api/v1/demandes-adhesions/{id}`

**Réponse :**
```json
{
  "success": true,
  "message": "Détails de la demande d'adhésion",
  "data": {
    "id": 123,
    "type_demandeur": "physique",
    "statut": "VALIDEE",
    "created_at": "2025-01-08T10:30:00.000000Z",
    "updated_at": "2025-01-08T11:15:00.000000Z",
    "motif_rejet": null,
    "valide_par": {
      "id": 45,
      "nom": "Technicien",
      "prenoms": "Jean"
    },
    "valider_a": "2025-01-08T11:00:00.000000Z",
    
    "demandeur": {
      "nom": "Dupont",
      "prenoms": "Marie Claire",
      "date_naissance": "1985-03-15",
      "sexe": "femme",
      "profession": "Enseignante",
      "contact": "+225 07 12 34 56 78",
      "email": "marie.dupont@email.com",
      "photo": "uploads/users/marie_photo.jpg",
      "adresse": "Cocody, Abidjan"
    },
    
    "contrat_propose": {
      "proposition": {
        "id": 67,
        "statut": "ACCEPTE",
        "date_proposition": "2025-01-08T11:00:00.000000Z",
        "date_acceptation": "2025-01-08T14:30:00.000000Z",
        "date_refus": null,
        "motif_refus": null
      },
      "contrat": {
        "id": 12,
        "type_contrat": "INDIVIDUEL",
        "prime_standard": 50000.00,
        "frais_gestion": 5.00,
        "couverture_moyenne": 75.00,
        "couverture": 80.00,
        "est_actif": true,
        "technicien": {
          "id": 45,
          "nom": "Technicien",
          "prenoms": "Jean"
        },
        "categories_garanties": [
          {
            "id": 1,
            "libelle": "Soins ambulatoires",
            "description": "Consultations et soins externes",
            "couverture": 80.00,
            "garanties": [
              {
                "id": 1,
                "libelle": "Consultation médicale générale",
                "prix_standard": 15000.00,
                "taux_couverture": 80.00,
                "plafond": 50000.00
              },
              {
                "id": 2,
                "libelle": "Consultation spécialisée",
                "prix_standard": 25000.00,
                "taux_couverture": 70.00,
                "plafond": 100000.00
              }
            ]
          },
          {
            "id": 2,
            "libelle": "Hospitalisation",
            "description": "Soins avec hospitalisation",
            "couverture": 90.00,
            "garanties": [
              {
                "id": 5,
                "libelle": "Hospitalisation générale",
                "prix_standard": 100000.00,
                "taux_couverture": 90.00,
                "plafond": 500000.00
              }
            ]
          }
        ]
      }
    },
    
    "reponses_questionnaire": [
      {
        "question_id": 1,
        "question_libelle": "Avez-vous des antécédents médicaux ?",
        "type_donnee": "boolean",
        "reponse_bool": false
      }
    ],
    
    "statistiques": {
      "nombre_beneficiaires": 2,
      "repartition_par_sexe": {
        "homme": 1,
        "femme": 1
      },
      "repartition_par_age": {
        "0-18": 1,
        "19-30": 0,
        "31-50": 1,
        "51-65": 0,
        "65+": 0
      }
    }
  }
}
```

### Pour une Entreprise

**Endpoint :** `GET /api/v1/demandes-adhesions/{id}`

**Réponse :**
```json
{
  "success": true,
  "message": "Détails de la demande d'adhésion",
  "data": {
    "id": 456,
    "type_demandeur": "entreprise",
    "statut": "VALIDEE",
    "created_at": "2025-01-08T10:30:00.000000Z",
    "updated_at": "2025-01-08T11:15:00.000000Z",
    
    "demandeur": {
      "raison_sociale": "SUNU ASSURANCES SARL",
      "email": "contact@sunu-assurances.ci",
      "contact": "+225 27 20 12 34 56"
    },
    
    "contrat_propose": {
      "proposition": {
        "id": 89,
        "statut": "PROPOSE",
        "date_proposition": "2025-01-08T11:00:00.000000Z",
        "date_acceptation": null,
        "date_refus": null,
        "motif_refus": null
      },
      "contrat": {
        "id": 15,
        "type_contrat": "ENTREPRISE",
        "prime_standard": 25000.00,
        "frais_gestion": 3.00,
        "couverture_moyenne": 80.00,
        "couverture": 85.00,
        "est_actif": true,
        "technicien": {
          "id": 45,
          "nom": "Technicien",
          "prenoms": "Jean"
        },
        "categories_garanties": [
          {
            "id": 1,
            "libelle": "Soins ambulatoires",
            "description": "Consultations et soins externes",
            "couverture": 85.00,
            "garanties": [
              {
                "id": 1,
                "libelle": "Consultation médicale générale",
                "prix_standard": 15000.00,
                "taux_couverture": 85.00,
                "plafond": 75000.00
              }
            ]
          }
        ]
      }
    },
    
    "employes": [
      {
        "id": 234,
        "nom": "Kouassi",
        "prenoms": "Jean Baptiste",
        "email": "jean.kouassi@sunu.ci",
        "date_naissance": "1980-05-20",
        "sexe": "homme",
        "profession": "Comptable",
        "contact": "+225 07 11 22 33 44",
        "photo": "uploads/employes/jean_photo.jpg",
        "reponses_questionnaire": [
          {
            "question_id": 1,
            "question_libelle": "Avez-vous des antécédents médicaux ?",
            "type_donnee": "boolean",
            "reponse_bool": false
          }
        ],
        "beneficiaires": [
          {
            "id": 345,
            "nom": "Kouassi",
            "prenoms": "Marie",
            "date_naissance": "2010-08-12",
            "sexe": "femme",
            "lien_parente": "enfant",
            "photo": "uploads/beneficiaires/marie_photo.jpg"
          }
        ]
      }
    ],
    
    "statistiques": {
      "nombre_employes": 15,
      "repartition_employes_par_sexe": {
        "homme": 8,
        "femme": 7
      },
      "nombre_total_personnes_couvrir": 35,
      "nombre_beneficiaires": 20,
      "repartition_employes_par_age": {
        "19-30": 5,
        "31-50": 8,
        "51-65": 2,
        "65+": 0
      }
    }
  }
}
```

## 🔍 Cas Particuliers

### Aucun Contrat Proposé

Si aucun contrat n'a été proposé pour la demande d'adhésion :

```json
{
  "contrat_propose": null
}
```

### Contrat Refusé

Si le contrat a été refusé par le client :

```json
{
  "contrat_propose": {
    "proposition": {
      "id": 67,
      "statut": "REFUSE",
      "date_proposition": "2025-01-08T11:00:00.000000Z",
      "date_acceptation": null,
      "date_refus": "2025-01-08T16:45:00.000000Z",
      "motif_refus": "Prime trop élevée par rapport au budget"
    },
    "contrat": {
      // ... détails du contrat refusé
    }
  }
}
```

## 📋 Informations Incluses

### Proposition de Contrat
- **ID** de la proposition
- **Statut** : PROPOSE, ACCEPTE, REFUSE
- **Dates** : proposition, acceptation, refus
- **Motif de refus** (si applicable)

### Détails du Contrat
- **Informations générales** : type, primes, couvertures
- **Technicien** qui a créé le contrat
- **Catégories de garanties** avec leurs garanties détaillées
- **Prix standards** et **taux de couverture** par garantie
- **Plafonds** par garantie

## 🎯 Utilisation Frontend

Cette information permet au frontend de :

1. **Afficher le statut** de la proposition de contrat
2. **Montrer les détails** des garanties proposées
3. **Calculer les montants** de couverture par acte médical
4. **Gérer le workflow** d'acceptation/refus
5. **Afficher les informations** du technicien responsable

## 🔧 Compatibilité

- ✅ **Rétrocompatible** : Les anciennes réponses continuent de fonctionner
- ✅ **Null safe** : Retourne `null` si aucun contrat proposé
- ✅ **Optimisé** : Une seule requête pour récupérer toutes les informations
- ✅ **Complet** : Inclut toutes les relations nécessaires

Cette modification améliore significativement l'expérience utilisateur en fournissant toutes les informations nécessaires sur les contrats proposés dans une seule réponse API.