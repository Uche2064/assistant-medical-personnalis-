<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadHelper
{
    /**
     * Upload une image.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return string|null
     */


     /**
      * Upload une image avec organisation par type de fichier
      *
      * @param \Illuminate\Http\UploadedFile $file
      * @param string $folder Dossier de base (par défaut 'uploads')
      * @param string|null $userEmail Email de l'utilisateur pour organiser les fichiers
      * @param string|null $fileType Type de fichier: 'demande_adhesion' ou 'user_photo' (par défaut null = ancien comportement)
      * @return string|null
      */
     public static function uploadImage($file, $folder = 'uploads', $userEmail = null, $fileType = null)
     {
         try {
             // Vérifier si le fichier est valide
             if (!$file->isValid()) {
                 throw new \Exception('File is not valid.');
             }

             // Vérifier si le type MIME est autorisé (images + pdf)
             $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
             if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                 throw new \Exception('Invalid file type. Only JPEG, PNG, GIF, WEBP and PDF are allowed.');
             }

             // Générer un nom unique pour le fichier (UUID)
             $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

             // Stocker dans un dossier plat "uploads" (plus de sous-dossiers)
             $path = $file->storeAs('uploads', $filename, 'public');

             // Ajouter un log pour débogage
             Log::info('Image uploaded successfully: ' . $path);

             // Retourner l'URL publique (symbole storage) ou la route de téléchargement
             return asset('storage/' . $path);
         } catch (\Exception $e) {
             // Logger l'erreur
             Log::error('Image upload failed: ' . $e->getMessage());
             report($e);

             return null;
         }
     }




    /**
     * Supprime une image existante.
     *
     * @param string $path
     * @return bool
     */
    public static function deleteImage($path)
    {
        try {
            // Vérifie si le fichier existe
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            return false;
        } catch (\Exception $e) {
            // Gestion des erreurs (optionnel)
            report($e);
            return false;
        }
    }

    /**
     * Obtient l'URL publique d'un fichier.
     *
     * @param string $path
     * @return string|null
     */
    public static function getFileUrl($path)
    {
        try {
            if (empty($path)) {
                return null;
            }

            // Si le chemin commence déjà par http, le retourner tel quel
            if (str_starts_with($path, 'http')) {
                return $path;
            }

            // Retirer le préfixe 'storage/' s'il existe
            $cleanPath = str_replace('storage/', '', $path);

            // Vérifier si le fichier existe
            if (Storage::disk('public')->exists($cleanPath)) {
                // Utiliser la route de téléchargement par filename (uuid.ext)
                return url('/api/download/' . basename($cleanPath));
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting file URL: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtient le nom du fichier à partir du chemin.
     *
     * @param string $path
     * @return string
     */
    public static function getFileName($path)
    {
        if (empty($path)) {
            return 'Fichier inconnu';
        }

        return basename($path);
    }

    /**
     * Obtient l'extension du fichier.
     *
     * @param string $path
     * @return string
     */
    public static function getFileExtension($path)
    {
        if (empty($path)) {
            return '';
        }

        return pathinfo($path, PATHINFO_EXTENSION);
    }
}
