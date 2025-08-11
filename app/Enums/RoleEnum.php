<?php

namespace App\Enums;

enum RoleEnum: String
{
    // ===== RÔLES INTERNES (Personnel SUNU Santé) =====
    
    /** Super administrateur - Gère les gestionnaires et configuration système */
    case ADMIN_GLOBAL = "admin_global";
    
    /** Administrateur RH - Gère le personnel SUNU */
    case GESTIONNAIRE = "gestionnaire";
    
    /** Analyste technique - Analyse demandes et propose contrats */
    case TECHNICIEN = 'technicien';
    
    /** Contrôle médical - Valide prestataires et contrôle actes */
    case MEDECIN_CONTROLEUR = 'medecin_controleur';
    
    /** Prospecteur - Prospecte clients et génère codes parrainage */
    case COMMERCIAL = 'commercial';
    
    /** Gestionnaire financier - Valide remboursements et flux financiers */
    case COMPTABLE = 'comptable';

    // ===== RÔLES EXTERNES (Clients/Partners) =====
    
    /** Client personne physique - Demande d'adhésion et gestion bénéficiaires */
    case PHYSIQUE = 'physique';
    
    /** Client moral - Gestion des employés et soumission groupée */
    case ENTREPRISE = 'entreprise';
    
    /** Centre de soins - Demande d'adhésion et facturation */
    case PRESTATAIRE = 'prestataire';

    /**
     * Get all role values as array
     */
    public static function values(): array {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get internal roles (SUNU Santé personnel)
     */
    public static function getInternalRoles(): array {
        return [
            self::ADMIN_GLOBAL->value,
            self::GESTIONNAIRE->value,
            self::TECHNICIEN->value,
            self::MEDECIN_CONTROLEUR->value,
            self::COMMERCIAL->value,
            self::COMPTABLE->value,
        ];
    }

    // get role label
    public static function getLabel(string $role): string {
        return match($role) {
            self::ADMIN_GLOBAL->value => 'Administrateur Global',
            self::GESTIONNAIRE->value => 'Gestionnaire',
            self::TECHNICIEN->value => 'Technicien',
            self::MEDECIN_CONTROLEUR->value => 'Médecin Contrôleur',
            self::COMMERCIAL->value => 'Commercial',
            self::COMPTABLE->value => 'Comptable',
            self::PHYSIQUE->value => 'Physique',
            self::ENTREPRISE->value => 'Entreprise',
            self::PRESTATAIRE->value => 'Prestataire',
        };
    }

    /**
     * Get external roles (Clients/Partners)
     */
    public static function getExternalRoles(): array {
        return [
            self::PHYSIQUE->value,
            self::ENTREPRISE->value,
            self::PRESTATAIRE->value,
        ];
    }

    /**
     * Check if role is internal (SUNU Santé personnel)
     */
    public function isInternal(): bool {
        return in_array($this->value, self::getInternalRoles());
    }

    /**
     * Check if role is external (Client/Partner)
     */
    public function isExternal(): bool {
        return in_array($this->value, self::getExternalRoles());
    }

    /**
     * Get role description
     */
    public function getDescription(): string {
        return match($this) {
            self::ADMIN_GLOBAL => 'Super administrateur - Gère les gestionnaires et configuration système',
            self::GESTIONNAIRE => 'Administrateur RH - Gère le personnel SUNU',
            self::TECHNICIEN => 'Analyste technique - Analyse demandes et propose contrats',
            self::MEDECIN_CONTROLEUR => 'Contrôle médical - Valide prestataires et contrôle actes',
            self::COMMERCIAL => 'Prospecteur - Prospecte clients et génère codes parrainage',
            self::COMPTABLE => 'Gestionnaire financier - Valide remboursements et flux financiers',
            self::PHYSIQUE => 'Client personne physique - Demande d\'adhésion et gestion bénéficiaires',
            self::ENTREPRISE => 'Client moral - Gestion des employés et soumission groupée',
            self::PRESTATAIRE => 'Centre de soins - Demande d\'adhésion et facturation',
        };
    }
}
