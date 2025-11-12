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
    
    case CLIENT = 'client';
    
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

    public static function getLabel(string $role): string {
        return match($role) {
            self::ADMIN_GLOBAL->value => 'Super Administrateur',
            self::GESTIONNAIRE->value => 'Gestionnaire',
            self::TECHNICIEN->value => 'Technicien',
            self::MEDECIN_CONTROLEUR->value => 'Médecin Contrôleur',
            self::COMMERCIAL->value => 'Commercial',
            self::COMPTABLE->value => 'Comptable',
            self::CLIENT->value => 'Client',
            self::PRESTATAIRE->value => 'Prestataire',
        };
    }
}
