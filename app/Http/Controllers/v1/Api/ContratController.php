<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\LienEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Helpers\PdfUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContratFormRequest;
use App\Models\Assure;
use App\Models\Contrat;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContratController extends Controller
{
    /**
     * Récupérer tous les contrats
     * Filtrable par statut (actif, suspendu, résilié)
     */
    public function index(Request $request)
    {
        $query = Contrat::with(['client', 'technicien']);
        $perPage = $request->input('per_page', 10);

        // Filtrage par statut si fourni
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filtrage par client si fourni
        if ($request->has('client_id')) {
            $query->where('client_id', $request->query('client_id'));
        }

        $contrats = $query->orderBy('created_at', 'desc')->paginate($perPage);

        if ($contrats->isEmpty()) {
            return ApiResponse::success($contrats, 'Aucun contrat trouvé');
        }

        return ApiResponse::success($contrats, 'Liste des contrats récupérée avec succès');
    }

    /**
     * Enregistrer un nouveau contrat
     */
    public function store(ContratFormRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Vérifier si le client a déjà un contrat pour cette période
            $clientId = $data['client_id'];
            $dateDebut = $data['date_debut'];
            $dateFin = $data['date_fin'];

            $existingContrat = Contrat::where('client_id', $clientId)
                ->where(function($query) use ($dateDebut, $dateFin) {
                    // Vérifie les chevauchements de périodes
                    // Cas 1: La nouvelle période commence pendant une période existante
                    $query->where(function($q) use ($dateDebut, $dateFin) {
                        $q->where('date_debut', '<=', $dateDebut)
                          ->where('date_fin', '>=', $dateDebut);
                    })
                    // Cas 2: La nouvelle période se termine pendant une période existante
                    ->orWhere(function($q) use ($dateDebut, $dateFin) {
                        $q->where('date_debut', '<=', $dateFin)
                          ->where('date_fin', '>=', $dateFin);
                    })
                    // Cas 3: La nouvelle période englobe entièrement une période existante
                    ->orWhere(function($q) use ($dateDebut, $dateFin) {
                        $q->where('date_debut', '>=', $dateDebut)
                          ->where('date_fin', '<=', $dateFin);
                    });
                })
                ->first();

            if ($existingContrat) {
                return ApiResponse::error('Ce client possède déjà un contrat actif pour cette période. Un client ne peut avoir qu\'un seul contrat à la fois.', 422);
            }

            // Traiter les fichiers photo_document
            $processedDocuments = [];
            
            if (isset($data['photo_document']) && is_array($data['photo_document'])) {
                foreach ($data['photo_document'] as $document) {
                    if (is_object($document) && method_exists($document, 'getClientOriginalName')) {
                        $mimeType = $document->getMimeType();
                        $storagePath = 'contrats/documents';
                        
                        // Traiter selon le type de fichier
                        if (str_starts_with($mimeType, 'image/')) {
                            // Image
                            $fileUrl = ImageUploadHelper::uploadImage($document, $storagePath);
                            if ($fileUrl) {
                                $processedDocuments[] = [
                                    'type' => 'fichier',
                                    'chemin' => $fileUrl,
                                    'nom_original' => $document->getClientOriginalName(),
                                    'mime_type' => $mimeType
                                ];
                            }
                        } elseif ($mimeType === 'application/pdf') {
                            // PDF
                            $result = PdfUploadHelper::storePdf(
                                file_get_contents($document->getRealPath()),
                                $storagePath,
                                $document->getClientOriginalName()
                            );
                            if ($result) {
                                $processedDocuments[] = [
                                    'type' => 'fichier',
                                    'chemin' => $result['url'],
                                    'nom_original' => $document->getClientOriginalName(),
                                    'mime_type' => $mimeType
                                ];
                            }
                        } else {
                            // Autre type de fichier
                            $fileName = uniqid('doc_') . '_' . time() . '.' . $document->getClientOriginalExtension();
                            $filePath = $document->storeAs($storagePath, $fileName, 'public');
                            $processedDocuments[] = [
                                'type' => 'fichier',
                                'chemin' => asset('storage/' . $filePath),
                                'nom_original' => $document->getClientOriginalName(),
                                'mime_type' => $mimeType
                            ];
                        }
                    }
                }
            }
            
            // Ajouter les documents traités aux données
            $data['photo_document'] = $processedDocuments;

            // Création du contrat
            $contrat = Contrat::create($data);

            // enregistré le client dans la table Assuré
            $client = Client::with(['user'])->where('id', $data['client_id'])->first();

            $assure = Assure::create([
                'user_id' => $client->user_id,
                'client_id' => $client->id,
                'lien_parente' => LienEnum::PRINCIPAL,
            ]);


            DB::commit();

            return ApiResponse::success([
                'contrat_id' => $contrat->id,
                'assure' => $assure,
            ], 'Contrat enregistré avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Afficher les détails d'un contrat
     */
    public function show(string $numeroPolice)
    {
        $contrat = Contrat::with(['client', 'technicien'])
            ->where('numero_police', $numeroPolice)
            ->first();

        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        return ApiResponse::success($contrat, 'Détails du contrat');
    }

    /**
     * Mettre à jour un contrat
     */
    public function update(ContratFormRequest $request, string $numeroPolice)
    {
        $contrat = Contrat::where('numero_police', $numeroPolice)->first();

        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $contrat->update($data);

            DB::commit();

            return ApiResponse::success([
                'contrat_id' => $contrat->id,
                'numero_police' => $contrat->numero_police,
            ], 'Contrat mis à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Changer le statut d'un contrat (suspension, résiliation)
     */
    // public function changeStatus(Request $request, string $uuid)
    // {
    //     $request->validate([
    //         'status' => 'required|string|in:actif,suspendu,résilié',
    //         'motif' => 'sometimes|string'
    //     ]);

    //     $contrat = Contrat::where('uuid', $uuid)->first();

    //     if (!$contrat) {
    //         return ApiResponse::error('Contrat non trouvé', 404);
    //     }

    //     try {
    //         $oldStatus = $contrat->status;
    //         $contrat->status = $request->status;
    //         $contrat->updated_by = Auth::id();
            
    //         // Enregistrer le motif du changement de statut si fourni
    //         if ($request->has('motif')) {
    //             $infosComplementaires = $contrat->infos_complementaires ?? [];
    //             $infosComplementaires['changements_status'][] = [
    //                 'date' => now()->toDateTimeString(),
    //                 'ancien_status' => $oldStatus,
    //                 'nouveau_status' => $request->status,
    //                 'motif' => $request->motif,
    //                 'utilisateur_id' => Auth::id()
    //             ];
                
    //             $contrat->infos_complementaires = $infosComplementaires;
    //         }
            
    //         $contrat->save();

    //         return ApiResponse::success([
    //             'contrat_id' => $contrat->id,
    //             'uuid' => $contrat->uuid,
    //             'status' => $contrat->status
    //         ], 'Statut du contrat modifié avec succès');
    //     } catch (\Exception $e) {
    //         return ApiResponse::error($e->getMessage(), 500);
    //     }
    // }
}