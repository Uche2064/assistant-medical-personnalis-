# 📋 Demande d’adhésion – Personne Physique

## 1. URL de l’API
```
POST /api/v1/demandes-adhesions
```

---

## 2. Payload attendu

### Champs obligatoires
> **Remarque :** Les champs *nom*, *prénoms*, *email*, *date de naissance*, *sexe*, *contact*, *adresse* sont déjà connus car saisis à la création du compte. **Ils doivent être préremplis et non modifiables dans le formulaire d’adhésion.**

| Champ                | Type      | Description                                 |
|----------------------|-----------|---------------------------------------------|
| reponses             | array     | Réponses au questionnaire médical           |
| reponses[].question_id | int     | ID de la question                           |
| reponses[].valeur    | string    | Valeur de la réponse                        |
| photo_url            | fichier   | Photo de profil (optionnel, jpg/png/webp)   |

### Bénéficiaires (optionnel)
| Champ                                 | Type      | Description                                 |
|----------------------------------------|-----------|---------------------------------------------|
| beneficiaires[].nom                    | string    | Nom du bénéficiaire                         |
| beneficiaires[].prenoms                | string    | Prénoms du bénéficiaire                     |
| beneficiaires[].date_naissance         | date      | Date de naissance                           |
| beneficiaires[].sexe                   | string    | Sexe (M ou F)                               |
| beneficiaires[].lien_parente           | string    | Lien de parenté (ex: conjoint, enfant)      |
| beneficiaires[].reponses               | array     | Réponses au questionnaire médical           |
| beneficiaires[].reponses[].question_id | int       | ID de la question                           |
| beneficiaires[].reponses[].valeur      | string    | Valeur de la réponse                        |

### Exemple de payload JSON
```json
{
  "reponses": [
    { "question_id": 1, "valeur": "Non" },
    { "question_id": 2, "valeur": "Oui" }
  ],
  "photo_url": (fichier),
  "beneficiaires": [
    {
      "nom": "DUPONT",
      "prenoms": "Marie",
      "date_naissance": "1995-08-20",
      "sexe": "F",
      "lien_parente": "conjoint",
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
  "message": "Demande d'adhésion soumise avec succès.",
  "data": {
    "id": 123,
    "type_demandeur": "physique",
    "statut": "en_attente",
    "reponses_questionnaire": [
      { "question_id": 1, "valeur": "Non" }
    ],
    "beneficiaires": [
      {
        "nom": "DUPONT",
        "prenoms": "Marie",
        "date_naissance": "1995-08-20",
        "sexe": "F",
        "lien_parente": "conjoint"
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
    "reponses": ["Le questionnaire médical est obligatoire."]
  }
}
```

---

## 4. Champs à afficher dans le formulaire
- **Informations personnelles** (préremplies, non modifiables) :
  - Nom
  - Prénoms
  - Email
  - Date de naissance
  - Sexe
  - Contact
  - Adresse
- Photo de profil (upload image, optionnel)
- **Questionnaire médical** (questions dynamiques)
- **Bénéficiaires** (section répétable, tous les champs sont à saisir)

---

## 5. UX attendue après soumission
- Message clair de succès : « Votre demande a bien été soumise. Un technicien va l’analyser. »
- Redirection vers une page de confirmation ou le tableau de bord utilisateur.
- Notification email de confirmation.
- Bouton pour télécharger la demande (PDF généré par l’API).

---

## 6. Récapitulatif avant soumission
- **Oui, fortement recommandé** :
  - Afficher un résumé de toutes les informations saisies (y compris les bénéficiaires et réponses au questionnaire)
  - Permettre à l’utilisateur de corriger avant validation finale

---

## 7. Téléchargement du formulaire
- Endpoint pour télécharger la demande au format PDF :
  ```
  GET /api/v1/demandes-adhesions/{id}/download
  ```
- Bouton “Télécharger ma demande” à afficher après la soumission ou dans l’espace utilisateur.

---

## 8. Résumé pour le designer
- Formulaire multi-étapes (infos personnelles préremplies, questionnaire, bénéficiaires, récapitulatif)
- Upload fichiers (photo, pièces jointes)
- UX fluide : feedback immédiat, erreurs claires, récapitulatif avant envoi
- Téléchargement PDF après soumission
- Message de succès + email de confirmation 