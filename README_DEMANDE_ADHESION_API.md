# API Demande d'Adhésion - Documentation Frontend

## Endpoint Show Demande d'Adhésion

### URL
```
GET /api/v1/demandes-adhesion/{id}
```

### Description
Cet endpoint retourne les détails d'une demande d'adhésion selon le type de demandeur (physique, prestataire, entreprise). La structure de la réponse varie selon le type de demandeur.

### Réponse Générale
```json
{
    "status": true,
    "message": "Détails de la demande d'adhésion",
    "data": {
        // Structure variable selon le type de demandeur
    }
}
```

---

## 1. Demandeur PHYSIQUE

### Structure de Réponse
```json
{
    "status": true,
    "message": "Détails de la demande d'adhésion",
    "data": {
        "id": 1,
        "type_demandeur": "physique",
        "statut": "en_attente",
        "created_at": "2025-08-06T03:22:41.000000Z",
        "updated_at": "2025-08-06T03:22:41.000000Z",
        "motif_rejet": null,
        "valide_par": null,
        "valider_a": null,
        "demandeur": {
            "nom": "Lekwauwa",
            "prenoms": "Uche",
            "date_naissance": "2002-02-02T00:00:00.000000Z",
            "sexe": "M",
            "profession": "developpeur",
            "contact": null,
            "email": "cpica5125@gmail.com"
        },
        "reponses_questionnaire": [
            {
                "question": "Quel sport pratiquez-vous?",
                "reponse_text": "Aucun",
                "reponse_bool": null,
                "reponse_number": null,
                "reponse_date": null,
                "reponse_fichier": null
            }
        ],
        "statistiques": {
            "nombre_beneficiaires": 4,
            "repartition_par_sexe": {
                "M": 2,
                "F": 2
            },
            "repartition_par_age": {
                "0-18": 4,
                "19-30": 0,
                "31-50": 0,
                "51-65": 0,
                "65+": 0
            }
        }
    }
}
```

### Champs Spécifiques au Demandeur Physique

#### `demandeur`
- `nom` (string): Nom de famille
- `prenoms` (string): Prénoms
- `date_naissance` (date): Date de naissance
- `sexe` (string): "M" ou "F"
- `profession` (string): Profession
- `contact` (string|null): Numéro de téléphone
- `email` (string): Adresse email

#### `reponses_questionnaire` (array)
Chaque réponse contient :
- `question` (string): Libellé de la question
- `reponse_text` (string|null): Réponse textuelle
- `reponse_bool` (boolean|null): Réponse oui/non
- `reponse_number` (number|null): Réponse numérique
- `reponse_date` (date|null): Réponse date
- `reponse_fichier` (string|null): URL du fichier joint

#### `statistiques`
- `nombre_beneficiaires` (number): Nombre total de bénéficiaires
- `repartition_par_sexe` (object): Répartition par sexe
  - `M` (number): Nombre d'hommes
  - `F` (number): Nombre de femmes
- `repartition_par_age` (object): Répartition par tranches d'âge
  - `0-18` (number): 0 à 18 ans
  - `19-30` (number): 19 à 30 ans
  - `31-50` (number): 31 à 50 ans
  - `51-65` (number): 51 à 65 ans
  - `65+` (number): 65 ans et plus

---

## 2. Demandeur PRESTATAIRE

### Types de Prestataires
- `centre_de_soins`
- `laboratoire_centre_diagnostic`
- `pharmacie`
- `optique`

### Structure de Réponse
```json
{
    "status": true,
    "message": "Détails de la demande d'adhésion",
    "data": {
        "id": 1,
        "type_demandeur": "centre_de_soins",
        "statut": "en_attente",
        "created_at": "2025-08-06T03:22:41.000000Z",
        "updated_at": "2025-08-06T03:22:41.000000Z",
        "motif_rejet": null,
        "valide_par": null,
        "valider_a": null,
        "demandeur": {
            "raison_sociale": "Centre Médical ABC",
            "email": "contact@centremedical.com",
            "contact": "+22890123456"
        },
        "reponses_questionnaire": [
            {
                "question": "Nombre de lits disponibles",
                "reponse_text": "50",
                "reponse_bool": null,
                "reponse_number": null,
                "reponse_date": null,
                "reponse_fichier": null
            }
        ]
    }
}
```

### Champs Spécifiques au Demandeur Prestataire

#### `demandeur`
- `raison_sociale` (string): Raison sociale du prestataire
- `email` (string): Adresse email
- `contact` (string): Numéro de téléphone

#### `reponses_questionnaire` (array)
Même structure que pour le demandeur physique.

---

## 3. Demandeur ENTREPRISE

### Structure de Réponse
```json
{
    "status": true,
    "message": "Détails de la demande d'adhésion",
    "data": {
        "id": 1,
        "type_demandeur": "entreprise",
        "statut": "en_attente",
        "created_at": "2025-08-06T03:22:41.000000Z",
        "updated_at": "2025-08-06T03:22:41.000000Z",
        "motif_rejet": null,
        "valide_par": null,
        "valider_a": null,
        "demandeur": {
            "raison_sociale": "Entreprise XYZ",
            "email": "contact@entreprise.com",
            "contact": "+22890123456"
        },
        "statistiques": {
            "nombre_employes": 25,
            "repartition_employes_par_sexe": {
                "M": 15,
                "F": 10
            },
            "nombre_total_personnes_couvrir": 45
        }
    }
}
```

### Champs Spécifiques au Demandeur Entreprise

#### `demandeur`
- `raison_sociale` (string): Raison sociale de l'entreprise
- `email` (string): Adresse email
- `contact` (string): Numéro de téléphone

#### `statistiques`
- `nombre_employes` (number): Nombre total d'employés
- `repartition_employes_par_sexe` (object): Répartition des employés par sexe
  - `M` (number): Nombre d'hommes employés
  - `F` (number): Nombre de femmes employées
- `nombre_total_personnes_couvrir` (number): Nombre total de personnes à couvrir (employés + bénéficiaires)

---

## Champs Communs à Tous les Types

### Informations de Base
- `id` (number): Identifiant unique de la demande
- `type_demandeur` (string): Type de demandeur
- `statut` (string): Statut de la demande ("en_attente", "validee", "rejetee")
- `created_at` (datetime): Date de création
- `updated_at` (datetime): Date de dernière modification
- `motif_rejet` (string|null): Motif de rejet si applicable
- `valide_par` (object|null): Informations sur la personne qui a validé
  - `id` (number): ID du validateur
  - `nom` (string): Nom du validateur
  - `prenoms` (string): Prénoms du validateur
- `valider_a` (datetime|null): Date de validation

---

## Gestion des Erreurs

### Erreur 404 - Demande non trouvée
```json
{
    "status": false,
    "message": "Demande d'adhésion non trouvée",
    "data": null
}
```

### Erreur 401 - Non autorisé
```json
{
    "status": false,
    "message": "Accès non autorisé",
    "data": null
}
```

---

## Exemples d'Utilisation Frontend

### JavaScript/TypeScript
```javascript
// Récupérer les détails d'une demande
const getDemandeDetails = async (id) => {
    try {
        const response = await fetch(`/api/v1/demandes-adhesion/${id}`);
        const data = await response.json();
        
        if (data.status) {
            const demande = data.data;
            
            // Afficher selon le type de demandeur
            switch (demande.type_demandeur) {
                case 'physique':
                    displayPhysiqueDemande(demande);
                    break;
                case 'entreprise':
                    displayEntrepriseDemande(demande);
                    break;
                default:
                    displayPrestataireDemande(demande);
                    break;
            }
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
};

// Exemple d'affichage pour un demandeur physique
const displayPhysiqueDemande = (demande) => {
    console.log(`Demandeur: ${demande.demandeur.nom} ${demande.demandeur.prenoms}`);
    console.log(`Bénéficiaires: ${demande.statistiques.nombre_beneficiaires}`);
    console.log(`Répartition par sexe:`, demande.statistiques.repartition_par_sexe);
    console.log(`Répartition par âge:`, demande.statistiques.repartition_par_age);
};
```

### React/Vue.js
```jsx
// Composant React exemple
const DemandeDetails = ({ demande }) => {
    const renderPhysiqueStats = () => (
        <div>
            <h3>Statistiques des Bénéficiaires</h3>
            <p>Nombre total: {demande.statistiques.nombre_beneficiaires}</p>
            <div>
                <h4>Répartition par sexe:</h4>
                <p>Hommes: {demande.statistiques.repartition_par_sexe.M}</p>
                <p>Femmes: {demande.statistiques.repartition_par_sexe.F}</p>
            </div>
            <div>
                <h4>Répartition par âge:</h4>
                {Object.entries(demande.statistiques.repartition_par_age).map(([tranche, count]) => (
                    <p key={tranche}>{tranche}: {count}</p>
                ))}
            </div>
        </div>
    );

    return (
        <div>
            <h2>Demande #{demande.id}</h2>
            <p>Type: {demande.type_demandeur}</p>
            <p>Statut: {demande.statut}</p>
            
            {demande.type_demandeur === 'physique' && renderPhysiqueStats()}
        </div>
    );
};
```

---

## Notes Importantes

1. **Types de demandeurs** : Le frontend doit gérer les 3 types principaux (physique, entreprise, prestataire)
2. **Statistiques** : Seuls les demandeurs physiques et entreprises ont des statistiques
3. **Réponses questionnaire** : Tous les types peuvent avoir des réponses au questionnaire
4. **Dates** : Toutes les dates sont au format ISO 8601
5. **Valeurs nulles** : Les champs optionnels peuvent être `null`
6. **Statuts** : Les statuts possibles sont "en_attente", "validee", "rejetee" 