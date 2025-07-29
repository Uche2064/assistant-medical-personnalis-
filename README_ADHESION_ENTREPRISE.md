# 📋 Demande d’adhésion – Entreprise

## 1. URL de l’API
```
POST /api/v1/demandes-adhesions/entreprise
```

---

## 2. Payload attendu

### Champs obligatoires
| Champ                | Type      | Description                                 |
|----------------------|-----------|---------------------------------------------|
| raison_sociale       | string    | Raison sociale de l’entreprise              |
| email                | string    | Email de l’entreprise                       |
| contact              | string    | Téléphone                                   |
| secteur_activite     | string    | Secteur d’activité                          |
| nombre_employes      | int       | Nombre d’employés                           |
| adresse              | string    | Adresse (optionnel)                         |
| code_parrainage      | string    | Code de parrainage (optionnel)              |
| employes             | array     | Liste des employés (voir ci-dessous)        |

### Employés (tableau)
| Champ                                 | Type      | Description                                 |
|----------------------------------------|-----------|---------------------------------------------|
| employes[].nom                        | string    | Nom de l’employé                            |
| employes[].prenoms                    | string    | Prénoms de l’employé                        |
| employes[].email                      | string    | Email de l’employé                          |
| employes[].date_naissance             | date      | Date de naissance                           |
| employes[].sexe                       | string    | Sexe (M ou F)                               |
| employes[].profession                 | string    | Profession (optionnel)                      |
| employes[].reponses                   | array     | Réponses au questionnaire médical           |
| employes[].reponses[].question_id     | int       | ID de la question                           |
| employes[].reponses[].valeur          | string    | Valeur de la réponse                        |

### Exemple de payload JSON
```json
{
  "raison_sociale": "SOCIETE ABC",
  "email": "contact@abc.com",
  "contact": "770000000",
  "secteur_activite": "Informatique",
  "nombre_employes": 12,
  "adresse": "Dakar, Sénégal",
  "code_parrainage": "COMM2024",
  "employes": [
    {
      "nom": "DURAND",
      "prenoms": "Paul",
      "email": "paul.durand@abc.com",
      "date_naissance": "1985-03-10",
      "sexe": "M",
      "profession": "Développeur",
      "reponses": [
        { "question_id": 1, "valeur": "Non" }
      ]
    }
  ]
}
```

---

## 3. Réponse type

### Succès
```json
{
  "status": true,
  "message": "Demande d'adhésion entreprise soumise avec succès.",
  "data": {
    "id": 456,
    "type_demandeur": "entreprise",
    "statut": "en_attente",
    "raison_sociale": "SOCIETE ABC",
    "email": "contact@abc.com",
    "contact": "770000000",
    "employes": [
      {
        "nom": "DURAND",
        "prenoms": "Paul",
        "email": "paul.durand@abc.com"
      }
    ]
  }
}
```

### Erreur de validation
```json
{
  "status": false,
  "message": "Erreur de validation",
  "errors": {
    "raison_sociale": ["La raison sociale est obligatoire."],
    "employes": ["Au moins un employé est requis."]
  }
}
```

---

## 4. Champs à afficher dans le formulaire
- Raison sociale (texte)
- Email (email)
- Contact (texte)
- Secteur d’activité (texte)
- Nombre d’employés (nombre)
- Adresse (texte)
- Code de parrainage (texte, optionnel)
- **Employés** (section répétable, mêmes champs que personne physique)

---

## 5. UX attendue après soumission
- Message clair de succès : « Votre demande a bien été soumise. Un technicien va l’analyser. »
- Redirection vers une page de confirmation ou le tableau de bord entreprise.
- Notification email de confirmation.
- Bouton pour télécharger la demande (PDF généré par l’API).

---

## 6. Récapitulatif avant soumission
- **Oui, fortement recommandé** :
  - Afficher un résumé de toutes les informations saisies (y compris les employés et leurs réponses)
  - Permettre à l’entreprise de corriger avant validation finale

---

## 7. Téléchargement du formulaire
- Endpoint pour télécharger la demande au format PDF :
  ```
  GET /api/v1/demandes-adhesions/{id}/download
  ```
- Bouton “Télécharger la demande” à afficher après la soumission ou dans l’espace entreprise.

---

## 8. Résumé pour le designer
- Formulaire multi-étapes (infos entreprise, employés, récapitulatif)
- UX fluide : feedback immédiat, erreurs claires, récapitulatif avant envoi
- Téléchargement PDF après soumission
- Message de succès + email de confirmation 