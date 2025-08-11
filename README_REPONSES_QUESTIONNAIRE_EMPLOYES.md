# Réponses au Questionnaire des Employés - API Documentation

## Vue d'ensemble

Ce document décrit les nouvelles fonctionnalités pour récupérer et formater les réponses au questionnaire des employés d'une entreprise. Les réponses sont formatées pour ne garder que les champs non-null, facilitant ainsi l'intégration frontend.

## Endpoints

### 1. Récupérer toutes les réponses formatées des employés

**Endpoint:** `GET /api/v1/entreprise/reponses-employes`

**Description:** Récupère les réponses formatées au questionnaire pour tous les employés d'une entreprise.

**Authentification:** Requise (rôle: entreprise)

**Réponse:**
```json
{
    "success": true,
    "message": "Réponses des employés récupérées avec succès.",
    "data": {
        "entreprise_id": 1,
        "nombre_employes": 5,
        "employes_avec_reponses": [
            {
                "employe": {
                    "id": 1,
                    "nom": "Doe",
                    "prenoms": "John",
                    "email": "john.doe@example.com",
                    "date_naissance": "1990-01-01",
                    "sexe": "M",
                    "profession": "Ingénieur",
                    "contact": "+1234567890"
                },
                "reponses_questionnaire": [
                    {
                        "question_id": 1,
                        "question_libelle": "Avez-vous des antécédents médicaux ?",
                        "type_question": "bool",
                        "reponse_bool": true
                    },
                    {
                        "question_id": 2,
                        "question_libelle": "Quel est votre poids ?",
                        "type_question": "number",
                        "reponse_number": 75
                    }
                ],
                "nombre_reponses": 2
            }
        ],
        "statistiques": {
            "employes_avec_reponses": 3,
            "employes_sans_reponses": 2
        }
    }
}
```

### 2. Récupérer les réponses formatées d'un employé spécifique

**Endpoint:** `GET /api/v1/entreprise/reponses-employe/{employeId}`

**Description:** Récupère les réponses formatées au questionnaire pour un employé spécifique.

**Paramètres:**
- `employeId` (int): ID de l'employé

**Authentification:** Requise (rôle: entreprise)

**Réponse:**
```json
{
    "success": true,
    "message": "Réponses de l'employé récupérées avec succès.",
    "data": {
        "employe": {
            "id": 1,
            "nom": "Doe",
            "prenoms": "John",
            "email": "john.doe@example.com",
            "date_naissance": "1990-01-01",
            "sexe": "M",
            "profession": "Ingénieur",
            "contact": "+1234567890"
        },
        "reponses_questionnaire": [
            {
                "question_id": 1,
                "question_libelle": "Avez-vous des antécédents médicaux ?",
                "type_question": "bool",
                "reponse_bool": true
            },
            {
                "question_id": 2,
                "question_libelle": "Quel est votre poids ?",
                "type_question": "number",
                "reponse_number": 75
            },
            {
                "question_id": 3,
                "question_libelle": "Décrivez votre état de santé général",
                "type_question": "text",
                "reponse_text": "Excellent état de santé"
            }
        ],
        "nombre_reponses": 3
    }
}
```

## Formatage des Réponses

### Principe de Formatage

La méthode `formaterReponsesQuestionnaire()` dans `DemandeAdhesionStatsService` ne garde que les champs de réponse qui ne sont pas `null`. Cela permet d'avoir des données plus propres et plus faciles à traiter côté frontend.

### Champs Formatés

Pour chaque réponse, les champs suivants sont toujours inclus :
- `question_id`: ID de la question
- `question_libelle`: Libellé de la question
- `type_question`: Type de la question (text, bool, number, date, file, select)

Les champs de réponse suivants sont inclus seulement s'ils ne sont pas `null` :
- `reponse_text`: Réponse textuelle
- `reponse_bool`: Réponse booléenne
- `reponse_number`: Réponse numérique
- `reponse_date`: Réponse date
- `reponse_fichier`: Réponse fichier
- `reponse_select`: Réponse sélection

## Services Utilisés

### DemandeAdhesionStatsService

#### Méthodes Ajoutées

1. **`formaterReponsesQuestionnaire($reponses)`**
   - Formate une collection de réponses
   - Ne garde que les champs non-null
   - Retourne un tableau formaté

2. **`getReponsesEmployeFormatees(Assure $employe)`**
   - Récupère et formate les réponses d'un employé spécifique
   - Inclut les informations de l'employé
   - Retourne un tableau avec employé + réponses formatées

3. **`getReponsesEmployesEntreprise(int $entrepriseId)`**
   - Récupère et formate les réponses de tous les employés d'une entreprise
   - Inclut des statistiques
   - Retourne un tableau complet avec tous les employés

## Intégration Frontend

### TypeScript Types

```typescript
interface ReponseQuestionnaire {
    question_id: number;
    question_libelle: string;
    type_question: string;
    reponse_text?: string;
    reponse_bool?: boolean;
    reponse_number?: number;
    reponse_date?: string;
    reponse_fichier?: string;
    reponse_select?: string;
}

interface Employe {
    id: number;
    nom: string;
    prenoms: string;
    email: string;
    date_naissance: string;
    sexe: string;
    profession: string;
    contact: string;
}

interface ReponsesEmploye {
    employe: Employe;
    reponses_questionnaire: ReponseQuestionnaire[];
    nombre_reponses: number;
}

interface ReponsesEntreprise {
    entreprise_id: number;
    nombre_employes: number;
    employes_avec_reponses: ReponsesEmploye[];
    statistiques: {
        employes_avec_reponses: number;
        employes_sans_reponses: number;
    };
}
```

### Exemple d'Utilisation Frontend

```typescript
// Récupérer toutes les réponses
const reponses = await api.get('/entreprise/reponses-employes');
console.log(reponses.data);

// Récupérer les réponses d'un employé spécifique
const reponsesEmploye = await api.get('/entreprise/reponses-employe/1');
console.log(reponsesEmploye.data);

// Afficher les réponses formatées
reponsesEmploye.data.reponses_questionnaire.forEach(reponse => {
    console.log(`Question: ${reponse.question_libelle}`);
    
    // Afficher la réponse selon le type
    if (reponse.reponse_text) {
        console.log(`Réponse: ${reponse.reponse_text}`);
    } else if (reponse.reponse_bool !== undefined) {
        console.log(`Réponse: ${reponse.reponse_bool ? 'Oui' : 'Non'}`);
    } else if (reponse.reponse_number !== undefined) {
        console.log(`Réponse: ${reponse.reponse_number}`);
    }
});
```

## Gestion d'Erreurs

### Erreurs Possibles

1. **403 Forbidden**: L'utilisateur n'a pas le rôle entreprise
2. **404 Not Found**: L'employé n'existe pas ou n'appartient pas à l'entreprise

### Exemple d'Erreur

```json
{
    "success": false,
    "message": "Employé non trouvé ou n'appartient pas à votre entreprise.",
    "data": null
}
```

## Avantages du Formatage

1. **Données Propres**: Seuls les champs pertinents sont inclus
2. **Performance**: Réduction de la taille des données transférées
3. **Facilité d'Intégration**: Structure prévisible pour le frontend
4. **Maintenabilité**: Code plus lisible et maintenable
5. **Flexibilité**: S'adapte automatiquement aux différents types de questions

## Migration et Compatibilité

Ces nouvelles fonctionnalités sont ajoutées sans affecter les fonctionnalités existantes. Les anciennes méthodes de récupération des réponses restent disponibles pour la compatibilité. 