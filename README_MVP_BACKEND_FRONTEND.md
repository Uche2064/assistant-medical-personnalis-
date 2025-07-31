# SUNU Santé – Guide MVP Backend & Frontend

## 1. Rôles et Workflows

### Rôles principaux
- **Prospect (Assuré principal)** : Crée un compte, soumet sa demande d’adhésion, ajoute des bénéficiaires.
- **Bénéficiaire** : Ajouté par un assuré principal, pas de compte, soumet sa fiche médicale via le compte du principal.
- **Entreprise** : Crée un compte, invite ses employés à remplir leur fiche, soumet la demande groupée.
- **Employé** : Remplit sa fiche via un lien d’invitation, devient assuré principal si la demande est validée.
- **Prestataire de soins** : Crée un compte, soumet une demande d’adhésion avec documents, attend validation.
- **Technicien** : Analyse les demandes d’adhésion (physique/entreprise), propose un contrat.
- **Médecin contrôleur** : Valide les demandes des prestataires, gère les questions médicales.
- **Gestionnaire** : Gère les personnels SUNU (technicien, médecin, commercial, etc.).
- **Admin global** : Gère les gestionnaires.
- **Commercial** : Prospecte, fournit des codes de parrainage.

### Workflows clés

#### 1. Création de compte (Prospect, Entreprise, Prestataire)
- Saisie des infos, email, mot de passe, code de parrainage (optionnel)
- Envoi OTP par email → vérification obligatoire

#### 2. Demande d’adhésion (Prospect/Physique)
- Authentification
- Remplissage du questionnaire médical (`reponses`)
- Ajout de bénéficiaires (avec leurs propres réponses)
- Soumission
- Attente analyse technicien

#### 3. Demande d’adhésion (Entreprise)
- Création de compte entreprise
- Génération d’un lien d’invitation (expirable)
- Employés remplissent leur fiche via le lien
- L’entreprise soumet la demande groupée
- Attente analyse technicien

#### 4. Demande d’adhésion (Prestataire)
- Création de compte prestataire
- Remplissage du questionnaire + upload des documents requis
- Soumission
- Attente validation médecin contrôleur

#### 5. Validation & Contrat
- **Technicien** : Analyse la demande (physique/entreprise), propose un contrat (choix du type, garanties, prime)
- **Prospect** : Reçoit la proposition, accepte/refuse via un lien sécurisé
- **Médecin contrôleur** : Valide la demande prestataire

---

## 2. Exemples de payloads backend

### a) Création de compte (POST /api/v1/auth/register)
```json
{
  "nom": "Doe",
  "prenoms": "John",
  "email": "john.doe@email.com",
  "password": "motdepasse",
  "code_parrainage": "ABC123" // optionnel
}
```

### b) Soumission d’adhésion (personne physique)
```json
{
  "type_demandeur": "physique",
  "reponses": [
    { "question_id": 1, "reponse_text": "Aucune allergie" },
    { "question_id": 2, "reponse_bool": false },
    { "question_id": 3, "reponse_fichier": <FICHIER> }
  ],
  "beneficiaires": [
    {
      "nom": "Doe",
      "prenoms": "Jane",
      "date_naissance": "2010-05-12",
      "sexe": "feminin",
      "lien_parente": "fille",
      "reponses": [
        { "question_id": 1, "reponse_text": "Aucune allergie" },
        { "question_id": 2, "reponse_bool": false }
      ]
    }
  ]
}
```

### c) Soumission d’adhésion (prestataire)
```json
{
  "type_demandeur": "pharmacie",
  "reponses": [
    { "question_id": 10, "reponse_text": "Pharmacie du Centre" },
    { "question_id": 11, "reponse_fichier": <FICHIER> }
  ]
}
```

### d) Soumission d’adhésion (entreprise)
```json
// Aucun payload direct, l’entreprise doit juste appeler POST /demandes-adhesions/entreprise après que ses employés aient rempli leur fiche.
```

### e) Proposition de contrat (technicien)
```json
{
  "contrat_id": 1,
  "prime_proposee": 50000,
  "taux_couverture": 80,
  "frais_gestion": 20,
  "commentaires": "Prime standard pour profil jeune",
  "garanties_incluses": [1,2,3]
}
```

---

## 3. Design UI/UX – Préférences
- **Stack recommandée** : Vue.js 3 + DaisyUI/Tailwind CSS (pour rapidité et cohérence)
- **Structure** :
  - Sidebar/navigation par rôle
  - Tableaux pour listing (demandes, contrats, bénéficiaires…)
  - Formulaires dynamiques (affichage conditionnel des questions selon le type de demandeur)
  - Upload de fichiers natif (drag & drop ou bouton)
  - Notifications toast pour feedback utilisateur
- **Responsive** : Oui, mobile-first
- **Thème** : Clair par défaut, dark mode optionnel

---

## 4. Fonctionnalités MVP à livrer absolument
- Authentification (register/login/OTP)
- Soumission d’adhésion (prospect, entreprise, prestataire)
- Ajout de bénéficiaires
- Génération de lien d’invitation employé (entreprise)
- Upload de documents (prestataire, questions fichier)
- Listing et consultation des demandes (par rôle)
- Proposition de contrat (technicien)
- Acceptation/refus contrat (prospect)
- Validation des prestataires (médecin contrôleur)
- Notifications email et in-app
- Gestion des rôles et permissions (RBAC)

---

## 5. Schéma du flow utilisateur (MVP)

1. **Création de compte** (prospect, entreprise, prestataire) → Vérification OTP
2. **Connexion**
3. **Soumission d’adhésion**
   - Prospect : questionnaire + bénéficiaires
   - Entreprise : invite les employés, soumet la demande groupée
   - Prestataire : questionnaire + documents
4. **Analyse/validation**
   - Technicien : analyse, propose un contrat (prospect/entreprise)
   - Médecin contrôleur : valide la demande prestataire
5. **Proposition de contrat** (prospect/entreprise)
6. **Acceptation/refus** (prospect/entreprise)
7. **Activation du statut assuré/prestataire**

---

**Ce README sert de référence rapide pour toute l’équipe (backend & frontend).**
Pour toute question sur un flow ou un payload, se référer à la doc API ou demander à l’équipe technique. 

---

## 6. Routes API principales

### Authentification
| Méthode | URL | Description | Rôle | Payload attendu |
|---------|-----|-------------|------|-----------------|
| POST | /api/v1/auth/register | Créer un compte (prospect, entreprise, prestataire) | Public | nom, prenoms, email, password, code_parrainage (optionnel) |
| POST | /api/v1/auth/send-otp | Envoi OTP email | Public | email |
| POST | /api/v1/auth/verify-otp | Vérifier OTP | Public | email, otp |
| POST | /api/v1/auth/login | Connexion | Public | email, password |
| POST | /api/v1/auth/logout | Déconnexion | Auth | - |
| POST | /api/v1/auth/change-password | Changer mot de passe | Auth | old_password, new_password |
| GET  | /api/v1/auth/me | Infos utilisateur courant | Auth | - |

### Demandes d’adhésion
| Méthode | URL | Description | Rôle | Payload attendu |
|---------|-----|-------------|------|-----------------|
| POST | /api/v1/demandes-adhesions | Soumettre une demande (physique) | user | type_demandeur, reponses, beneficiaires |
| POST | /api/v1/demandes-adhesions/prestataire | Soumettre une demande (prestataire) | prestataire | type_demandeur, reponses |
| POST | /api/v1/demandes-adhesions/entreprise | Soumettre une demande (entreprise) | user | - (employés déjà invités) |
| GET  | /api/v1/demandes-adhesions | Lister les demandes | technicien, medecin_controleur, admin_global | - |
| GET  | /api/v1/demandes-adhesions/{id} | Détail d’une demande | technicien, medecin_controleur, admin_global | - |
| GET  | /api/v1/demandes-adhesions/demandes-adhesions/{id}/download | Télécharger PDF | technicien, medecin_controleur, admin_global | - |
| PUT  | /api/v1/demandes-adhesions/{demande_id}/proposer-contrat | Proposer un contrat | technicien | contrat_id, prime_proposee, ... |
| PUT  | /api/v1/demandes-adhesions/{demande_id}/valider-prestataire | Valider un prestataire | medecin_controleur | - |
| PUT  | /api/v1/demandes-adhesions/{demande_id}/rejeter | Rejeter une demande | technicien, medecin_controleur | motif_rejet |

### Gestion des bénéficiaires (assuré principal)
| Méthode | URL | Description | Rôle |
|---------|-----|-------------|------|
| GET | /api/v1/assure/beneficiaires | Lister les bénéficiaires | assure_principal |
| POST | /api/v1/assure/beneficiaires | Ajouter un bénéficiaire | assure_principal |
| PUT | /api/v1/assure/beneficiaires/{id} | Modifier un bénéficiaire | assure_principal |
| DELETE | /api/v1/assure/beneficiaires/{id} | Supprimer un bénéficiaire | assure_principal |

### Gestion des questions (médecin contrôleur)
| Méthode | URL | Description | Rôle |
|---------|-----|-------------|------|
| GET | /api/v1/questions/all | Lister toutes les questions | medecin_controleur |
| POST | /api/v1/questions | Ajouter des questions (bulk) | medecin_controleur |
| PUT | /api/v1/questions/{id} | Modifier une question | medecin_controleur |
| PATCH | /api/v1/questions/{id}/toggle | Activer/désactiver | medecin_controleur |
| DELETE | /api/v1/questions/{id} | Supprimer une question | medecin_controleur |
| POST | /api/v1/questions/bulk-delete | Suppression en masse | medecin_controleur |

### Gestion des contrats
| Méthode | URL | Description | Rôle |
|---------|-----|-------------|------|
| GET | /api/v1/contrats | Lister les contrats | technicien, medecin_controleur |
| POST | /api/v1/contrats | Créer un contrat | technicien |
| GET | /api/v1/contrats/{id} | Détail contrat | technicien |
| PUT | /api/v1/contrats/{id} | Modifier contrat | technicien |
| DELETE | /api/v1/contrats/{id} | Supprimer contrat | technicien |

### Gestion des employés (entreprise)
| Méthode | URL | Description | Rôle |
|---------|-----|-------------|------|
| POST | /api/v1/entreprise/inviter-employe | Générer lien d’invitation | user (entreprise) |
| POST | /api/v1/entreprise/soumettre-demande-adhesion | Soumettre la demande groupée | user (entreprise) |
| GET | /api/v1/employes/formulaire/{token} | Afficher formulaire employé | Public |
| POST | /api/v1/employes/formulaire/{token} | Soumettre fiche employé | Public |

### Gestion des prestataires
| Méthode | URL | Description | Rôle |
|---------|-----|-------------|------|
| GET | /api/v1/prestataire/dashboard | Dashboard prestataire | prestataire |
| GET | /api/v1/prestataire/profile | Voir profil | prestataire |
| PUT | /api/v1/prestataire/profile | Modifier profil | prestataire |
| GET | /api/v1/prestataire/questions | Questions pour le type | prestataire |
| GET | /api/v1/prestataire/documents-requis | Documents requis | prestataire |
| POST | /api/v1/prestataire/valider-documents | Valider documents | prestataire |
| POST | /api/v1/prestataire/demande-adhesion | Soumettre demande (redirige vers store générique) | prestataire |

### Gestion des gestionnaires/personnels (admin/gestionnaire)
| Méthode | URL | Description | Rôle |
|---------|-----|-------------|------|
| POST | /api/v1/admin/gestionnaires | Ajouter gestionnaire | admin_global |
| GET | /api/v1/admin/gestionnaires | Lister gestionnaires | admin_global |
| PATCH | /api/v1/admin/gestionnaires/{id}/suspend | Suspendre | admin_global |
| PATCH | /api/v1/admin/gestionnaires/{id}/activate | Réactiver | admin_global |
| DELETE | /api/v1/admin/gestionnaires/{id} | Supprimer | admin_global |
| POST | /api/v1/gestionnaire/personnels | Ajouter personnel | gestionnaire |
| GET | /api/v1/gestionnaire/personnels | Lister personnels | gestionnaire, admin_global |
| PATCH | /api/v1/gestionnaire/personnels/{id}/suspend | Suspendre | gestionnaire |
| PATCH | /api/v1/gestionnaire/personnels/{id}/activate | Réactiver | gestionnaire |
| DELETE | /api/v1/gestionnaire/personnels/{id} | Supprimer | gestionnaire |

---

**Pour chaque route nécessitant un payload, se référer à la section 2 pour la structure exacte.**
