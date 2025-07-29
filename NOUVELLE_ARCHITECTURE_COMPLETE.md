# Nouvelle Architecture Base de Données - SUNU Santé

## Vue d'ensemble

Cette nouvelle architecture a été conçue pour clarifier les relations entre les entités et éliminer les redondances de l'ancienne structure.

## Tables principales

### 1. **users** - Utilisateurs du système
- `id`, `email`, `contact`, `password`, `adresse`, `photo_url`
- `est_actif`, `email_verified_at`, `mot_de_passe_a_changer`
- **Relations** : Central pour tous les autres modèles

### 2. **personnels** - Employés SUNU Santé
- `user_id`, `nom`, `prenoms`, `sexe`, `date_naissance`
- `code_parainage`, `gestionnaire_id` (auto-référence)
- **Rôles** : Admin Global, Gestionnaire, Technicien, Médecin Contrôleur, Comptable, Commercial

### 3. **clients** - Prospects (pas encore assurés)
- `user_id`, `commercial_id`, `type_client` (physique/moral)
- `profession`, `code_parainage`, `statut` (prospect/client/assure)
- **Transition** : prospect → client → assure

### 4. **entreprises** - Clients moraux
- `user_id`, `raison_sociale`, `siret`, `adresse_siege`
- `nombre_employes`, `statut`, `lien_adhesion`
- **Particularité** : Gestion des employés via liens expirables

### 5. **assures** - Assurés principaux et bénéficiaires
- `user_id` (NULL pour bénéficiaires), `client_id`, `entreprise_id`
- `assure_principal_id` (auto-référence pour bénéficiaires)
- `contrat_id`, `lien_parente`, `est_principal`
- **Logique** : Un assuré principal peut avoir plusieurs bénéficiaires

### 6. **contrats** - Contrats d'assurance
- `numero_police`, `type_contrat`, `technicien_id`
- `prime_standard`, `frais_gestion`, `commission_commercial`
- `date_debut`, `date_fin`, `statut`, `est_actif`

### 7. **categories_garanties** - Catégories de garanties
- `libelle`, `description`, `medecin_controleur_id`

### 8. **garanties** - Garanties spécifiques
- `libelle`, `categorie_garantie_id`, `medecin_controleur_id`
- `plafond`, `prix_standard`, `taux_couverture`

### 9. **contrat_categorie_garantie** - Table pivot
- `contrat_id`, `categorie_garantie_id`, `couverture`

### 10. **prestataires** - Prestataires de soins
- `user_id`, `type_prestataire`, `nom_etablissement`
- `adresse`, `medecin_controleur_id`, `statut`

### 11. **demandes_adhesions** - Demandes d'adhésion
- `user_id`, `type_demandeur`, `statut`
- `motif_rejet`, `valide_par_id`, `code_parainage`

### 12. **questions** - Questions dynamiques
- `libelle`, `type_donnee`, `options` (JSON)
- `destinataire`, `obligatoire`, `est_actif`, `cree_par_id`

### 13. **reponses_questionnaire** - Réponses aux questionnaires
- `question_id`, `demande_adhesion_id`
- `personne_type`, `personne_id` (polymorphique)
- `reponse_text`, `reponse_bool`, `reponse_decimal`, etc.

### 14. **sinistres** - Sinistres
- `assure_id`, `prestataire_id`, `description`
- `date_sinistre`, `statut`

### 15. **factures** - Factures des prestataires
- `numero_facture`, `sinistre_id`, `prestataire_id`
- `montant_reclame`, `montant_a_rembourser`, `diagnostic`
- `photo_justificatifs`, `ticket_moderateur`, `statut`
- **Workflow** : Technicien → Médecin → Comptable

## Tables de support

### 16. **notifications** - Notifications système
### 17. **conversations** - Conversations entre utilisateurs
### 18. **messages** - Messages dans les conversations
### 19. **invitation_employes** - Liens d'invitation pour entreprises
### 20. **otp** - Codes OTP pour authentification
### 21. **jobs** - Jobs en queue
### 22. **cache** - Cache système

## Relations clés

### Clients Physiques
```
User → Client → Assure (principal) → Assure (bénéficiaires)
```

### Clients Moraux (Entreprises)
```
User → Entreprise → Assure (employés) → Assure (bénéficiaires)
```

### Workflow Facturation
```
Sinistre → Facture → Validation (Technicien → Médecin → Comptable)
```

### Gestion des Rôles
```
Admin Global → Gestionnaire → Personnel (différents rôles)
```

## Avantages de cette architecture

1. **Clarté** : Séparation nette entre prospects, clients et assurés
2. **Flexibilité** : Support des clients physiques et moraux
3. **Évolutivité** : Structure modulaire pour ajouter de nouvelles fonctionnalités
4. **Intégrité** : Relations claires avec contraintes appropriées
5. **Performance** : Index et relations optimisées

## Prochaines étapes

1. **Créer les modèles Eloquent** correspondants
2. **Mettre à jour les contrôleurs** existants
3. **Adapter les seeders** pour les nouvelles structures
4. **Tester les migrations** et relations
5. **Implémenter les nouveaux workflows** métier 