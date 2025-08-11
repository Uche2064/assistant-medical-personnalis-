# API Rejet Demande d'Adhésion

## 🎯 **Vue d'ensemble**

Cette documentation décrit la route API pour rejeter une demande d'adhésion par un technicien ou un médecin contrôleur.

## 📋 **Route API**

### **PUT** `/api/demandes-adhesion/{demande_id}/rejeter`

**Description :** Rejette une demande d'adhésion en attente.

**Permissions :** 
- Technicien
- Médecin contrôleur

**Méthode :** PUT

**Paramètres URL :**
- `demande_id` (integer, requis) : ID de la demande d'adhésion à rejeter

## 🔐 **Authentification**

Toutes les routes nécessitent un token JWT dans le header :
```
Authorization: Bearer {token}
```

## 📤 **Payload Request**

### **Headers requis :**
```http
Content-Type: application/json
Authorization: Bearer {token}
```

### **Body JSON :**
```json
{
  "motif_rejet": "Documents incomplets ou informations manquantes"
}
```

### **Paramètres du body :**
- `motif_rejet` (string, requis) : Motif détaillé du rejet de la demande

## 📥 **Payload Response**

### **Succès (200) :**
```json
{
  "success": true,
  "message": "Demande d'adhésion rejetée avec succès",
  "data": {
    "demande_id": 123,
    "statut": "rejetee",
    "rejetee_par": "John Doe"
  }
}
```

### **Erreur - Demande non trouvée (404) :**
```json
{
  "success": false,
  "message": "Demande d'adhésion non trouvée",
  "data": null
}
```

### **Erreur - Demande déjà traitée (400) :**
```json
{
  "success": false,
  "message": "Seules les demandes en attente peuvent être rejetées",
  "data": null
}
```

### **Erreur - Validation (422) :**
```json
{
  "success": false,
  "message": "Error de validation",
  "data": {
    "motif_rejet": [
      "Le motif de rejet est requis"
    ]
  }
}
```

### **Erreur - Permission (403) :**
```json
{
  "success": false,
  "message": "Accès non autorisé",
  "data": null
}
```

### **Erreur - Serveur (500) :**
```json
{
  "success": false,
  "message": "Erreur interne du serveur",
  "data": null
}
```

## 📊 **Exemples d'utilisation**

### **Exemple 1 : Rejet par un technicien**

#### **Request :**
```bash
curl -X PUT "http://localhost:8000/api/demandes-adhesion/123/rejeter" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "motif_rejet": "Les documents fournis ne sont pas conformes aux exigences. Veuillez fournir des documents valides et à jour."
  }'
```

#### **Response :**
```json
{
  "success": true,
  "message": "Demande d'adhésion rejetée avec succès",
  "data": {
    "demande_id": 123,
    "statut": "rejetee",
    "rejetee_par": "Jean Dupont"
  }
}
```

### **Exemple 2 : Rejet par un médecin contrôleur**

#### **Request :**
```bash
curl -X PUT "http://localhost:8000/api/demandes-adhesion/456/rejeter" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "motif_rejet": "Les informations médicales fournies ne correspondent pas aux standards requis pour un prestataire de soins."
  }'
```

#### **Response :**
```json
{
  "success": true,
  "message": "Demande d'adhésion rejetée avec succès",
  "data": {
    "demande_id": 456,
    "statut": "rejetee",
    "rejetee_par": "Dr. Marie Martin"
  }
}
```

## 🔄 **Actions automatiques**

Lors du rejet d'une demande d'adhésion, le système effectue automatiquement :

1. **Mise à jour du statut** : La demande passe de "en_attente" à "rejetee"
2. **Enregistrement du motif** : Le motif de rejet est sauvegardé
3. **Enregistrement du rejeteur** : L'identité du technicien/médecin contrôleur est enregistrée
4. **Email de notification** : Un email est envoyé au demandeur
5. **Notification in-app** : Une notification est créée pour le demandeur

## 📧 **Email envoyé**

Un email automatique est envoyé au demandeur avec :
- Le statut de rejet
- Le motif détaillé
- Les informations de contact pour plus de détails
- Les possibilités de nouvelle soumission

## 🛡️ **Sécurité et validation**

### **Vérifications effectuées :**
- ✅ Authentification de l'utilisateur
- ✅ Vérification des permissions (technicien ou médecin contrôleur)
- ✅ Existence de la demande d'adhésion
- ✅ Statut de la demande (doit être "en_attente")
- ✅ Validation du motif de rejet (requis et non vide)

### **Contraintes :**
- Seuls les techniciens et médecins contrôleurs peuvent rejeter
- Seules les demandes en attente peuvent être rejetées
- Le motif de rejet est obligatoire
- L'action est irréversible

## 📱 **Utilisation Frontend**

### **JavaScript/TypeScript :**
```javascript
async function rejeterDemande(demandeId, motifRejet) {
  try {
    const response = await fetch(`/api/demandes-adhesion/${demandeId}/rejeter`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        motif_rejet: motifRejet
      })
    });

    const data = await response.json();
    
    if (data.success) {
      console.log('Demande rejetée avec succès');
      // Mettre à jour l'interface
      updateDemandeStatus(demandeId, 'rejetee');
    } else {
      console.error('Erreur:', data.message);
    }
  } catch (error) {
    console.error('Erreur de connexion:', error);
  }
}
```

### **Angular :**
```typescript
rejeterDemande(demandeId: number, motifRejet: string): Observable<any> {
  return this.http.put(
    `${this.apiUrl}/demandes-adhesion/${demandeId}/rejeter`,
    { motif_rejet: motifRejet },
    { headers: this.getHeaders() }
  );
}
```

## 🎯 **Cas d'usage**

### **1. Rejet par un technicien :**
- Demandes de personnes physiques
- Demandes d'entreprises
- Documents incomplets
- Informations manquantes

### **2. Rejet par un médecin contrôleur :**
- Demandes de prestataires de soins
- Critères médicaux non respectés
- Qualifications insuffisantes
- Standards de qualité non atteints

## 📊 **Statuts possibles**

- `en_attente` : Demande en cours de traitement
- `validee` : Demande acceptée
- `rejetee` : Demande refusée

## 🔍 **Logs et traçabilité**

Toutes les actions de rejet sont loggées avec :
- ID de la demande
- ID du rejeteur
- Motif de rejet
- Timestamp
- IP de l'utilisateur 