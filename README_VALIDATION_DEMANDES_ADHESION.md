# Validation des Demandes d'Adhésion par Type de Demandeur

## 🎯 Vue d'ensemble

Le processus de validation des demandes d'adhésion diffère selon le type de demandeur. Il existe deux workflows distincts :

### **1. Prestataires de Soins (Validation Directe)**
- **Validateur** : Médecin Contrôleur
- **Processus** : Validation directe par clic
- **Statut final** : VALIDEE

### **2. Entreprises & Personnes Physiques (Validation avec Proposition de Contrat)**
- **Validateur** : Technicien
- **Processus** : Proposition de contrat → Acceptation client → Début du contrat
- **Statut final** : CONTRAT_ACTIF

## 🔄 Workflow Détaillé

### **📋 Étape 1 : Réception de la Demande**

#### **Pour tous les types de demandeurs :**
```php
// Demande reçue avec statut EN_ATTENTE
$demande = DemandeAdhesion::create([
    'type_demandeur' => TypeDemandeurEnum::PHYSIQUE->value, // ou ENTREPRISE, PRESTATAIRE
    'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
    'user_id' => $user->id,
]);
```

### **🏥 Étape 2A : Validation Prestataires (Médecin Contrôleur)**

#### **Route API :**
```
PUT /api/demandes-adhesions/{id}/valider-prestataire
```

#### **Permissions :**
- Rôle : `medecin_controleur`
- Accès : Demandes de type prestataire uniquement

#### **Processus :**
1. **Vérification** : Demande en attente et de type prestataire
2. **Validation** : Mise à jour du statut à `VALIDEE`
3. **Notification** : Email + notification in-app au prestataire
4. **Logs** : Enregistrement de l'action

#### **Code du contrôleur :**
```php
public function validerPrestataire(int $id)
{
    $medecinControleur = Auth::user();
    $demande = DemandeAdhesion::find($id);
    
    // Validation via le service
    $demande = $this->demandeAdhesionService->validerDemande($demande, $medecinControleur->personnel);
    
    // Notification
    $this->notificationService->createNotification($demande->user->id, ...);
    
    return ApiResponse::success([...], 'Demande validée');
}
```

### **🔧 Étape 2B : Proposition de Contrat (Technicien)**

#### **Route API :**
```
PUT /api/demandes-adhesions/{id}/proposer-contrat
```

#### **Permissions :**
- Rôle : `technicien`
- Accès : Demandes de type physique et entreprise uniquement

#### **Processus :**

##### **1. Récupération des Contrats Disponibles**
```php
// Route pour récupérer les contrats
GET /api/demandes-adhesions/contrats-disponibles

// Réponse :
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nom": "Contrat Santé Premium",
            "type_contrat": "sante",
            "description": "Couverture santé complète",
            "prime_de_base": 50000,
            "garanties": [
                {
                    "id": 1,
                    "nom": "Hospitalisation",
                    "description": "Frais d'hospitalisation",
                    "taux_couverture": 80,
                    "categorie": {
                        "id": 1,
                        "nom": "Santé"
                    }
                }
            ]
        }
    ]
}
```

##### **2. Proposition de Contrat**
```php
// Payload de la proposition
{
    "contrat_id": 1,
    "prime_proposee": 45000,
    "taux_couverture": 85,
    "frais_gestion": 15,
    "commentaires": "Proposition adaptée à votre profil",
    "garanties_incluses": [1, 2, 3]
}
```

##### **3. Création de la Proposition**
```php
$propositionContrat = PropositionContrat::create([
    'demande_adhesion_id' => $demande->id,
    'contrat_id' => $contrat->id,
    'prime_proposee' => $validatedData['prime_proposee'],
    'taux_couverture' => $validatedData['taux_couverture'],
    'frais_gestion' => $validatedData['frais_gestion'],
    'commentaires_technicien' => $validatedData['commentaires'],
    'technicien_id' => $technicien->personnel->id,
    'statut' => StatutPropositionContratEnum::PROPOSEE->value,
    'date_proposition' => now(),
]);
```

##### **4. Génération du Token d'Acceptation**
```php
$token = Str::random(60);
$tokenExpiration = now()->addDays(7);

Cache::put("proposition_contrat_{$propositionContrat->id}", [
    'proposition_id' => $propositionContrat->id,
    'demande_id' => $demande->id,
    'user_id' => $demande->user->id,
    'expires_at' => $tokenExpiration,
], $tokenExpiration);
```

##### **5. Notification au Client**
- **Email** : Avec lien d'acceptation
- **Notification in-app** : Détails de la proposition
- **URL d'acceptation** : `https://frontend.com/contrat/accepter/{token}`

### **✅ Étape 3 : Acceptation du Contrat (Client)**

#### **Route API :**
```
POST /api/contrats/accepter/{token}
```

#### **Processus d'Acceptation :**

##### **1. Validation du Token**
```php
$tokenData = Cache::get("proposition_contrat_{$token}");
if (!$tokenData || $tokenData['expires_at'] < now()) {
    return ApiResponse::error('Token invalide ou expiré', 400);
}
```

##### **2. Création du Contrat Final**
```php
$contrat = Contrat::create([
    'user_id' => $tokenData['user_id'],
    'proposition_contrat_id' => $tokenData['proposition_id'],
    'type_contrat' => $proposition->contrat->type_contrat,
    'prime' => $proposition->prime_proposee,
    'taux_couverture' => $proposition->taux_couverture,
    'frais_gestion' => $proposition->frais_gestion,
    'statut' => StatutContratEnum::ACTIF->value,
    'date_debut' => now(),
    'date_fin' => now()->addYear(),
]);
```

##### **3. Mise à Jour de la Demande**
```php
$demande->update([
    'statut' => StatutDemandeAdhesionEnum::CONTRAT_ACTIF->value,
    'contrat_id' => $contrat->id,
]);
```

##### **4. Notification de Confirmation**
```php
$this->notificationService->createNotification(
    $tokenData['user_id'],
    'Contrat accepté avec succès',
    "Votre contrat d'assurance est maintenant actif.",
    'contrat_accepte',
    [
        'contrat_id' => $contrat->id,
        'date_debut' => $contrat->date_debut,
        'prime' => $contrat->prime,
        'type' => 'contrat_accepte'
    ]
);
```

## 📊 Statuts des Demandes

### **Statuts Possibles :**
- `EN_ATTENTE` : Demande soumise, en attente de traitement
- `VALIDEE` : Demande validée (prestataires uniquement)
- `REJETEE` : Demande rejetée
- `CONTRAT_ACTIF` : Contrat accepté et actif (physique/entreprise)

### **Statuts des Propositions de Contrat :**
- `PROPOSEE` : Proposition envoyée au client
- `ACCEPTEE` : Proposition acceptée par le client
- `REFUSEE` : Proposition refusée par le client
- `EXPIREE` : Proposition expirée

## 🔐 Sécurité et Permissions

### **Rôles et Permissions :**

#### **Médecin Contrôleur :**
- ✅ Valider les demandes prestataires
- ❌ Accéder aux demandes physique/entreprise
- ❌ Proposer des contrats

#### **Technicien :**
- ✅ Voir les demandes physique/entreprise
- ✅ Proposer des contrats
- ✅ Rejeter les demandes
- ❌ Valider les demandes prestataires

#### **Client (Physique/Entreprise) :**
- ✅ Voir sa demande
- ✅ Accepter/refuser les propositions
- ✅ Consulter son contrat actif

## 📧 Notifications

### **Types de Notifications :**

#### **Pour Prestataires :**
- `demande_validee` : Demande validée par médecin contrôleur

#### **Pour Physique/Entreprise :**
- `contrat_propose` : Proposition de contrat reçue
- `contrat_accepte` : Contrat accepté et actif
- `contrat_refuse` : Contrat refusé

## 🚨 Gestion des Erreurs

### **Erreurs Courantes :**

#### **Token Expiré :**
```json
{
    "success": false,
    "message": "Token invalide ou expiré",
    "code": 400
}
```

#### **Demande Déjà Traitée :**
```json
{
    "success": false,
    "message": "Cette demande a déjà été traitée",
    "code": 400
}
```

#### **Contrat Non Valide :**
```json
{
    "success": false,
    "message": "Contrat non valide ou introuvable",
    "code": 400
}
```

## 🔄 Workflow Complet

### **Diagramme de Flux :**

```
Demande Soumise (EN_ATTENTE)
         ↓
    Type Demandeur ?
         ↓
┌─────────────────┬─────────────────┐
│   PRESTATAIRE   │ PHYSIQUE/ENTREPRISE │
│                 │                 │
│ Médecin Contrôleur │ Technicien    │
│         ↓        │         ↓      │
│   Validation     │ Proposition     │
│   Directe        │   de Contrat    │
│         ↓        │         ↓      │
│   VALIDEE        │ Client Accepte? │
│                 │         ↓      │
│                 │ ┌─────┬─────┐   │
│                 │ │ OUI │ NON │   │
│                 │ │  ↓  │  ↓  │   │
│                 │ │CONTRAT│REJET│   │
│                 │ │ACTIF │     │   │
└─────────────────┴─────────────────┘
```

## 📋 Ce qui manque dans le contrôleur

### **❌ Méthode manquante :**
- **Route d'acceptation de contrat** : `POST /api/contrats/accepter/{token}`
- **Méthode `accepterContrat()`** dans le contrôleur
- **Gestion de l'expiration des tokens**
- **Création du contrat final**

### **🔧 À implémenter :**
1. **Méthode `accepterContrat()`** dans `DemandeAdhesionController`
2. **Route API** pour l'acceptation
3. **Validation du token** et gestion de l'expiration
4. **Création du contrat final** avec toutes les garanties
5. **Mise à jour du statut** de la demande
6. **Notifications de confirmation**

---

*Ce README décrit le processus complet de validation des demandes d'adhésion selon le type de demandeur.* 