# 📋 Demande d’adhésion – Prestataire de soins

## 1. URL de l’API
```
POST /api/v1/demandes-adhesions/prestataire
```

---

## 2. Payload attendu

### Champs obligatoires
| Champ                | Type      | Description                                 |
|----------------------|-----------|---------------------------------------------|
| raison_sociale       | string    | Raison sociale du prestataire               |
| email                | string    | Email du prestataire                        |
| contact              | string    | Téléphone                                   |
| adresse              | string    | Adresse                                     |
| type_prestataire     | string    | Type (pharmacie, centre de soins, etc.)     |
| documents_requis     | fichiers  | Documents à uploader (voir ci-dessous)      |
| reponses_questionnaire | array   | Réponses au questionnaire                   |
| reponses_questionnaire[].question_id | int | ID de la question                         |
| reponses_questionnaire[].valeur      | string | Valeur de la réponse                     |
| reponses_questionnaire[].fichier     | fichier | Fichier joint (optionnel)                |

### Documents à uploader (selon le type)
- **Pharmacie** : Autorisation d’ouverture, plan, diplôme responsable, attestation ordre, photos structure
- **Centre de soins** : Autorisation, plan, diplômes responsables, grille tarifaire, photos, carte fiscale
- **Optique** : Autorisation, plan, diplômes responsables, grille tarifaire, photos, carte fiscale
- **Laboratoire/Diagnostic** : Autorisation, plan, diplômes responsables, grille tarifaire, photos, carte fiscale
- **Médecin libéral** : Autorisation, plan, diplôme, attestation ordre, photos

### Exemple de payload (multipart/form-data)
```
raison_sociale: "PHARMACIE DU CENTRE"
email: "pharmacie@centre.com"
contact: "770000000"
adresse: "Dakar, Sénégal"
type_prestataire: "pharmacie"
documents_requis[autorisation_ouverture]: (fichier PDF)
documents_requis[plan_situation_geographique]: (fichier PDF)
documents_requis[diplome_responsable]: (fichier PDF)
documents_requis[attestation_ordre]: (fichier PDF)
documents_requis[presentation_structure]: (fichier PDF)
reponses_questionnaire[0][question_id]: 1
reponses_questionnaire[0][valeur]: "Oui"
reponses_questionnaire[0][fichier]: (fichier optionnel)
```

---

## 3. Réponse type

### Succès
```json
{
  "status": true,
  "message": "Demande d'adhésion prestataire soumise avec succès.",
  "data": {
    "id": 789,
    "type_demandeur": "pharmacie",
    "statut": "en_attente",
    "raison_sociale": "PHARMACIE DU CENTRE",
    "email": "pharmacie@centre.com",
    "contact": "770000000",
    "documents_requis": {
      "autorisation_ouverture": "url/fichier.pdf",
      "plan_situation_geographique": "url/fichier.pdf"
    },
    "reponses_questionnaire": [
      { "question_id": 1, "valeur": "Oui" }
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
    "documents_requis.autorisation_ouverture": ["Ce document est obligatoire."],
    "reponses_questionnaire": ["Le questionnaire est obligatoire."]
  }
}
```

---

## 4. Champs à afficher dans le formulaire
- Raison sociale (texte)
- Email (email)
- Contact (texte)
- Adresse (texte)
- Type de prestataire (select)
- **Upload des documents requis** (dynamique selon le type)
- **Questionnaire d’adhésion** (questions dynamiques)

---

## 5. UX attendue après soumission
- Message clair de succès : « Votre demande a bien été soumise. Un médecin contrôleur va l’analyser. »
- Redirection vers une page de confirmation ou le tableau de bord prestataire.
- Notification email de confirmation.
- Bouton pour télécharger la demande (PDF généré par l’API).

---

## 6. Récapitulatif avant soumission
- **Oui, fortement recommandé** :
  - Afficher un résumé de toutes les informations saisies (y compris les documents uploadés)
  - Permettre au prestataire de corriger avant validation finale

---

## 7. Téléchargement du formulaire
- Endpoint pour télécharger la demande au format PDF :
  ```
  GET /api/v1/demandes-adhesions/{id}/download
  ```
- Bouton “Télécharger la demande” à afficher après la soumission ou dans l’espace prestataire.

---

## 8. Résumé pour le designer
- Formulaire multi-étapes (infos prestataire, uploads, questionnaire, récapitulatif)
- Upload fichiers (documents requis, pièces jointes)
- UX fluide : feedback immédiat, erreurs claires, récapitulatif avant envoi
- Téléchargement PDF après soumission
- Message de succès + email de confirmation 