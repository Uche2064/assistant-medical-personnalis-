## API Frontend — Contrats proposés au client

Cette ressource permet d’afficher, côté client (physique/entreprise), la liste des propositions de contrat en attente.

### Endpoint
- Méthode: GET
- URL: `/api/v1/client/contrats-proposes`
- Authentification: Bearer token (`Authorization: Bearer <token>`)
- Rôle requis: utilisateur authentifié (le filtrage est fait par `user_id`)

### Réponse 200 (JSON)
Tableau de propositions. Structure d’un élément:

```json
{
  "proposition_id": 12,
  "contrat": {
    "id": 3,
    "nom": "Contrat Santé Plus",
    "type_contrat": "sante",
    "description": "Couverture santé étendue"
  },
  "details_proposition": {
    "prime_proposee": 45000,
    "taux_couverture": 80,
    "frais_gestion": 20,
    "commentaires_technicien": "Adapté à votre profil",
    "date_proposition": "2025-08-06T14:23:00.000000Z"
  },
  "categories_garanties": [
    { "libelle": "Hospitalisation", "garanties": "Chambre, Chirurgie, Soins intensifs" },
    { "libelle": "Soins courants", "garanties": "Consultations, Analyses" }
  ],
  "statut": "proposee"
}
```

Notes:
- `categories_garanties` est un tableau où chaque entrée regroupe les garanties par catégorie. Le champ `garanties` est une chaîne jointe (affichable directement en UI).
- `statut` vaut `proposee` pour les contrats en attente d’action du client.

### Exemple d’intégration (TypeScript)

```ts
type PropositionContrat = {
  proposition_id: number;
  contrat: { id: number; nom: string; type_contrat: string; description: string | null };
  details_proposition: {
    prime_proposee: number;
    taux_couverture: number;
    frais_gestion: number;
    commentaires_technicien: string | null;
    date_proposition: string; // ISO
  };
  categories_garanties: { libelle: string; garanties: string }[];
  statut: string; // "proposee"
};

async function fetchContratsProposes(token: string): Promise<PropositionContrat[]> {
  const res = await fetch('/api/v1/client/contrats-proposes', {
    headers: { Authorization: `Bearer ${token}` },
  });
  if (!res.ok) throw new Error('Fetch contrats proposés failed');
  const { data } = await res.json();
  return data as PropositionContrat[];
}
```

### Gestion des actions (liées)

Accepter une proposition:
- Méthode: POST
- URL: `/api/v1/client/contrats-proposes/{proposition_id}/accepter`
- Auth: Bearer token
- Réponse 200: `{ contrat_id: number, message: string }`

Refuser une proposition:
- Méthode: POST
- URL: `/api/v1/client/contrats-proposes/{proposition_id}/refuser`
- Body JSON (optionnel): `{ "raison_refus": "string" }`
- Auth: Bearer token
- Réponse 200: `{ proposition_id: number, message: string }`

### Erreurs possibles
- 401 Unauthorized: token manquant/expiré
- 500 Server Error: erreur interne (ex. données contrat incomplètes)

### Recommandations UI
- Afficher chaque proposition sous forme de carte: titre du contrat, `prime_proposee` (formatée), `taux_couverture`, `frais_gestion`.
- Lister les `categories_garanties` avec `libelle` et `garanties`.
- Boutons: "Accepter" et "Refuser" (avec modal optionnelle pour `raison_refus`).
