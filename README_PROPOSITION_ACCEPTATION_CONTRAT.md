# Proposition et Acceptation de Contrat - Workflow Complet

## 🎯 Vue d'ensemble

Le processus de proposition et d'acceptation de contrat suit un workflow détaillé impliquant le technicien, le client et l'assignation à un réseau de prestataires.

## 🔄 Workflow Détaillé

### **📋 Étape 1 : Interface Technicien - Sélection Client**

#### **Interface Dashboard Technicien :**
- **Liste des clients** avec leurs statistiques
- **Bouton "Faire Proposition"** pour chaque client en attente
- **Statut de la demande** visible (EN_ATTENTE, EN_PROPOSITION, ACCEPTEE)

#### **Action Technicien :**
```php
// Route pour récupérer les détails d'un client
GET /api/demandes-adhesions/{id}/details-client

// Réponse avec statistiques
{
    "success": true,
    "data": {
        "demande_id": 1,
        "client": {
            "id": 1,
            "nom": "John Doe",
            "email": "john@example.com",
            "type_demandeur": "physique",
            "statut": "EN_ATTENTE"
        },
        "statistiques": {
            "date_soumission": "2025-01-15",
            "duree_attente": "3 jours",
            "priorite": "normale"
        }
    }
}
```

### **🔧 Étape 2 : Popup de Proposition de Contrat**

#### **Interface Popup :**
- **Liste des contrats disponibles** avec leurs détails
- **Catégories de garanties** avec libellés
- **Garanties par catégorie** entre parenthèses, séparées par des virgules

#### **Structure des Contrats :**
```php
// Route pour récupérer les contrats avec garanties
GET /api/demandes-adhesions/contrats-disponibles

// Réponse formatée pour l'interface
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nom": "Contrat Santé Premium",
            "type_contrat": "sante",
            "description": "Couverture santé complète",
            "prime_de_base": 50000,
            "categories_garanties": [
                {
                    "id": 1,
                    "libelle": "Hospitalisation",
                    "garanties": "Hospitalisation (Chambre individuelle, Soins intensifs, Réanimation)"
                },
                {
                    "id": 2,
                    "libelle": "Consultations",
                    "garanties": "Consultations (Médecin généraliste, Spécialiste, Psychologue)"
                },
                {
                    "id": 3,
                    "libelle": "Analyses",
                    "garanties": "Analyses (Sang, Urine, Radiologie, Échographie)"
                }
            ]
        }
    ]
}
```

#### **Interface Popup (HTML/CSS) :**
```html
<div class="contrat-card">
    <h3>Contrat Santé Premium</h3>
    <p>Prime de base: 50,000 FCFA</p>
    
    <div class="categories">
        <div class="categorie">
            <strong>Hospitalisation</strong>
            <span class="garanties">(Chambre individuelle, Soins intensifs, Réanimation)</span>
        </div>
        <div class="categorie">
            <strong>Consultations</strong>
            <span class="garanties">(Médecin généraliste, Spécialiste, Psychologue)</span>
        </div>
        <div class="categorie">
            <strong>Analyses</strong>
            <span class="garanties">(Sang, Urine, Radiologie, Échographie)</span>
        </div>
    </div>
    
    <button class="btn-proposer">Proposer ce contrat</button>
</div>
```

### **📝 Étape 3 : Proposition de Contrat**

#### **Action Technicien :**
1. **Sélection du contrat** dans le popup
2. **Saisie des détails** : prime proposée, commentaires
3. **Clic sur "Proposer"**

#### **Route API :**
```
PUT /api/demandes-adhesions/{id}/proposer-contrat
```

#### **Payload :**
```json
{
    "contrat_id": 1,
    "prime_proposee": 45000,
    "taux_couverture": 85,
    "frais_gestion": 15,
    "commentaires": "Proposition adaptée à votre profil",
    "garanties_incluses": [1, 2, 3]
}
```

#### **Processus Backend :**
```php
// 1. Création de la proposition
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

// 2. Mise à jour du statut de la demande
$demande->update([
    'statut' => StatutDemandeAdhesionEnum::EN_PROPOSITION->value
]);

// 3. Notification au client
$this->notificationService->createNotification(
    $demande->user->id,
    'Nouvelle proposition de contrat',
    "Un technicien vous a proposé un contrat d'assurance.",
    'contrat_propose',
    [
        'proposition_id' => $propositionContrat->id,
        'contrat_nom' => $contrat->nom,
        'prime_proposee' => $propositionContrat->prime_proposee,
        'technicien' => $technicien->personnel->nom,
        'type' => 'contrat_propose'
    ]
);
```

### **📱 Étape 4 : Interface Client - Menu Contrats Proposés**

#### **Route API :**
```
GET /api/client/contrats-proposes
```

#### **Interface Client :**
- **Liste des contrats proposés** sous forme de cards
- **Détails complets** : catégories, garanties, prime
- **Bouton "Accepter"** pour chaque proposition

#### **Réponse API :**
```json
{
    "success": true,
    "data": [
        {
            "proposition_id": 1,
            "contrat": {
                "id": 1,
                "nom": "Contrat Santé Premium",
                "type_contrat": "sante",
                "description": "Couverture santé complète"
            },
            "details_proposition": {
                "prime_proposee": 45000,
                "taux_couverture": 85,
                "frais_gestion": 15,
                "commentaires_technicien": "Proposition adaptée à votre profil",
                "date_proposition": "2025-01-15T10:30:00Z"
            },
            "categories_garanties": [
                {
                    "libelle": "Hospitalisation",
                    "garanties": "Chambre individuelle, Soins intensifs, Réanimation"
                },
                {
                    "libelle": "Consultations", 
                    "garanties": "Médecin généraliste, Spécialiste, Psychologue"
                }
            ],
            "statut": "PROPOSEE"
        }
    ]
}
```

### **✅ Étape 5 : Acceptation du Contrat**

#### **Action Client :**
1. **Clic sur "Accepter"** dans l'interface
2. **Confirmation** de l'acceptation

#### **Route API :**
```
POST /api/client/contrats-proposes/{proposition_id}/accepter
```

#### **Processus Backend :**
```php
// 1. Validation de la proposition
$proposition = PropositionContrat::find($proposition_id);
if (!$proposition || $proposition->statut !== 'PROPOSEE') {
    return ApiResponse::error('Proposition non valide', 400);
}

// 2. Création du contrat final
$contrat = Contrat::create([
    'user_id' => $proposition->demandeAdhesion->user_id,
    'proposition_contrat_id' => $proposition->id,
    'type_contrat' => $proposition->contrat->type_contrat,
    'prime' => $proposition->prime_proposee,
    'taux_couverture' => $proposition->taux_couverture,
    'frais_gestion' => $proposition->frais_gestion,
    'statut' => StatutContratEnum::ACTIF->value,
    'date_debut' => now(),
    'date_fin' => now()->addYear(),
]);

// 3. Mise à jour de la proposition
$proposition->update([
    'statut' => StatutPropositionContratEnum::ACCEPTEE->value,
    'date_acceptation' => now()
]);

// 4. Mise à jour de la demande
$proposition->demandeAdhesion->update([
    'statut' => StatutDemandeAdhesionEnum::ACCEPTEE->value,
    'contrat_id' => $contrat->id
]);

// 5. Notification au technicien
$this->notificationService->createNotification(
    $proposition->technicien->user_id,
    'Contrat accepté par le client',
    "Le client {$proposition->demandeAdhesion->user->nom} a accepté votre proposition de contrat.",
    'contrat_accepte_technicien',
    [
        'client_nom' => $proposition->demandeAdhesion->user->nom,
        'contrat_nom' => $proposition->contrat->nom,
        'prime' => $contrat->prime,
        'type' => 'contrat_accepte_technicien'
    ]
);
```

### **🏥 Étape 6 : Assignation au Réseau de Prestataires**

#### **Interface Technicien :**
- **Menu "Réseau de Prestataires"**
- **Sélection du client** à assigner
- **Interface d'assignation** par type de prestataire

#### **Route API :**
```
POST /api/technicien/assigner-reseau-prestataires
```

#### **Payload :**
```json
{
    "client_id": 1,
    "contrat_id": 1,
    "prestataires": {
        "pharmacies": [1, 2],
        "centres_soins": [3, 4, 5],
        "optiques": [6, 7],
        "laboratoires": [8, 9],
        "centres_diagnostic": [10, 11]
    }
}
```

#### **Processus Backend :**
```php
// 1. Création de l'entrée dans la table client_contrat
$clientContrat = ClientContrat::create([
    'client_id' => $request->client_id,
    'contrat_id' => $request->contrat_id,
    'type_client' => $client->type_demandeur, // 'physique' ou 'entreprise'
    'date_debut' => $contrat->date_debut,
    'date_fin' => $contrat->date_fin,
    'statut' => 'ACTIF'
]);

// 2. Assignation des prestataires
foreach ($request->prestataires as $type => $prestataireIds) {
    foreach ($prestataireIds as $prestataireId) {
        ClientPrestataire::create([
            'client_contrat_id' => $clientContrat->id,
            'prestataire_id' => $prestataireId,
            'type_prestataire' => $type,
            'statut' => 'ACTIF'
        ]);
    }
}

// 3. Notification au client
$this->notificationService->createNotification(
    $client->user_id,
    'Réseau de prestataires assigné',
    "Un réseau de prestataires vous a été assigné. Vous pouvez maintenant vous soigner chez ces prestataires.",
    'reseau_assigne',
    [
        'client_contrat_id' => $clientContrat->id,
        'nombre_prestataires' => count($request->prestataires),
        'type' => 'reseau_assigne'
    ]
);
```

## 📊 Statuts des Demandes

### **Nouveaux Statuts :**
- `EN_ATTENTE` : Demande soumise, en attente de traitement
- `EN_PROPOSITION` : Proposition de contrat en cours
- `ACCEPTEE` : Contrat accepté par le client
- `REJETEE` : Demande rejetée
- `CONTRAT_ACTIF` : Contrat actif avec réseau assigné

## 🗄️ Nouvelles Tables

### **Table `client_contrats` :**
```sql
CREATE TABLE client_contrats (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    client_id BIGINT NOT NULL,
    contrat_id BIGINT NOT NULL,
    type_client ENUM('physique', 'entreprise') NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('ACTIF', 'INACTIF', 'EXPIRE') DEFAULT 'ACTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_id) REFERENCES users(id),
    FOREIGN KEY (contrat_id) REFERENCES contrats(id)
);
```

### **Table `client_prestataires` :**
```sql
CREATE TABLE client_prestataires (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    client_contrat_id BIGINT NOT NULL,
    prestataire_id BIGINT NOT NULL,
    type_prestataire ENUM('pharmacie', 'centre_soins', 'optique', 'laboratoire', 'centre_diagnostic') NOT NULL,
    statut ENUM('ACTIF', 'INACTIF') DEFAULT 'ACTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_contrat_id) REFERENCES client_contrats(id),
    FOREIGN KEY (prestataire_id) REFERENCES prestataires(id)
);
```

## 📧 Notifications

### **Types de Notifications :**

#### **Pour Client :**
- `contrat_propose` : Nouvelle proposition de contrat
- `contrat_accepte` : Contrat accepté avec succès
- `reseau_assigne` : Réseau de prestataires assigné

#### **Pour Technicien :**
- `contrat_accepte_technicien` : Client a accepté le contrat
- `reseau_assigne_technicien` : Réseau assigné avec succès

## 🔐 Sécurité et Permissions

### **Rôles et Permissions :**

#### **Technicien :**
- ✅ Voir les demandes physique/entreprise
- ✅ Proposer des contrats
- ✅ Assigner des réseaux de prestataires
- ✅ Voir les statistiques clients

#### **Client (Physique/Entreprise) :**
- ✅ Voir ses propositions de contrats
- ✅ Accepter/refuser les contrats
- ✅ Voir son réseau de prestataires assigné

## 🚨 Gestion des Erreurs

### **Erreurs Courantes :**

#### **Proposition Non Valide :**
```json
{
    "success": false,
    "message": "Proposition non valide ou déjà traitée",
    "code": 400
}
```

#### **Prestataires Non Disponibles :**
```json
{
    "success": false,
    "message": "Certains prestataires ne sont pas disponibles dans cette zone",
    "code": 400
}
```

---

*Ce README décrit le processus complet de proposition, acceptation et assignation de réseau de prestataires.* 