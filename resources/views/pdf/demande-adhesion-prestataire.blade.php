<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'Adhésion - Prestataire - SUNU Santé</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 10px;
        }
        .title {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        .demande-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #2c5aa0;
            min-width: 150px;
        }
        .info-value {
            flex: 1;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c5aa0;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .user-info {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
        }
        .reponses-section {
            margin-top: 20px;
        }
        .reponse-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .question {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .reponse {
            color: #6c757d;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-en-attente {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-validee {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejetee {
            background-color: #f8d7da;
            color: #721c24;
        }
        .page-break {
            page-break-before: always;
        }
        .file-link {
            color: #2c5aa0;
            text-decoration: underline;
        }
        .prestataire-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            background-color: #e3f2fd;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SUNU SANTÉ</div>
        <div class="title">Demande d'Adhésion - Prestataire de Soins</div>
        <div style="font-size: 12px; color: #666;">Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

    <div class="demande-info">
        <div class="info-row">
            <span class="info-label">N° Demande :</span>
            <span class="info-value">{{ $demande->id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Type de demandeur :</span>
            <span class="info-value">
                <span class="prestataire-type">{{ ucfirst($demande->type_demandeur->value) }}</span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut :</span>
            <span class="info-value">
                <span class="status-badge status-{{ str_replace('_', '-', $demande->statut->value) }}">
                    {{ ucfirst(str_replace('_', ' ', $demande->statut->value)) }}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de soumission :</span>
            <span class="info-value">{{ $demande->created_at->format('d/m/Y à H:i') }}</span>
        </div>
        @if($demande->validePar)
        <div class="info-row">
            <span class="info-label">Validée par :</span>
            <span class="info-value">{{ $demande->validePar->nom ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de validation :</span>
            <span class="info-value">{{ $demande->valider_a ? \Carbon\Carbon::parse($demande->valider_a)->format('d/m/Y à H:i') : 'N/A' }}</span>
        </div>
        @endif
        @if($demande->motif_rejet)
        <div class="info-row">
            <span class="info-label">Motif de rejet :</span>
            <span class="info-value">{{ $demande->motif_rejet }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Informations du prestataire</div>
        <div class="user-info">
            <div class="info-row">
                <span class="info-label">Raison sociale :</span>
                <span class="info-value">
                    @if ($demande->user->prestataire && $demande->user->prestataire->raison_sociale)
                        {{ $demande->user->prestataire->raison_sociale }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Email :</span>
                <span class="info-value">{{ $demande->user->email }}</span>
            </div>
            @if($demande->user->contact)
            <div class="info-row">
                <span class="info-label">Téléphone :</span>
                <span class="info-value">{{ $demande->user->contact }}</span>
            </div>
            @endif
            @if($demande->user->prestataire && $demande->user->prestataire->adresse)
            <div class="info-row">
                <span class="info-label">Adresse :</span>
                <span class="info-value">{{ $demande->user->prestataire->adresse }}</span>
            </div>
            @endif
            @if($demande->user->prestataire && $demande->user->prestataire->type_prestataire)
            <div class="info-row">
                <span class="info-label">Type de prestataire :</span>
                <span class="info-value">{{ ucfirst($demande->user->prestataire->type_prestataire->value) }}</span>
            </div>
            @endif
            @if($demande->user->prestataire && $demande->user->prestataire->numero_agrement)
            <div class="info-row">
                <span class="info-label">Numéro d'agrément :</span>
                <span class="info-value">{{ $demande->user->prestataire->numero_agrement }}</span>
            </div>
            @endif
            @if($demande->user->prestataire && $demande->user->prestataire->date_agrement)
            <div class="info-row">
                <span class="info-label">Date d'agrément :</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($demande->user->prestataire->date_agrement)->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($demande->user->prestataire && $demande->user->prestataire->responsable_nom)
            <div class="info-row">
                <span class="info-label">Responsable :</span>
                <span class="info-value">{{ $demande->user->prestataire->responsable_nom }} {{ $demande->user->prestataire->responsable_prenoms ?? '' }}</span>
            </div>
            @endif
            @if($demande->user->prestataire && $demande->user->prestataire->responsable_contact)
            <div class="info-row">
                <span class="info-label">Contact responsable :</span>
                <span class="info-value">{{ $demande->user->prestataire->responsable_contact }}</span>
            </div>
            @endif
        </div>
    </div>

    @if($demande->reponsesQuestionnaire && $demande->reponsesQuestionnaire->count() > 0)
    <div class="section reponses-section">
        <div class="section-title">Réponses au questionnaire</div>
        @foreach($demande->reponsesQuestionnaire as $reponse)
        <div class="reponse-item">
            <div class="question">{{ $reponse->question->libelle ?? 'Question non trouvée' }}</div>
            <div class="reponse">
                @if($reponse->reponse_text)
                    {{ $reponse->reponse_text }}
                @elseif($reponse->reponse_bool !== null)
                    {{ $reponse->reponse_bool ? 'Oui' : 'Non' }}
                @elseif($reponse->reponse_number !== null)
                    {{ $reponse->reponse_number }}
                @elseif($reponse->reponse_date)
                    {{ \Carbon\Carbon::parse($reponse->reponse_date)->format('d/m/Y') }}
                @elseif($reponse->reponse_fichier)
                    <strong>Fichier joint :</strong> 
                    <a href="{{ $baseUrl }}/storage/{{ $reponse->reponse_fichier }}" class="file-link" target="_blank">
                        {{ \App\Helpers\ImageUploadHelper::getFileName($reponse->reponse_fichier) }}
                    </a>
                    <br><small style="color: #6c757d;">Type: {{ \App\Helpers\ImageUploadHelper::getFileExtension($reponse->reponse_fichier) }}</small>
                @else
                    Aucune réponse
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($demande->user->prestataire && $demande->user->prestataire->documents)
    <div class="section">
        <div class="section-title">Documents fournis</div>
        <div class="user-info">
            @foreach($demande->user->prestataire->documents as $document)
            <div class="info-row">
                <span class="info-label">{{ $document->type_document ?? 'Document' }} :</span>
                <span class="info-value">
                    @if($document->fichier_url)
                        <a href="{{ $baseUrl }}/storage/{{ $document->fichier_url }}" class="file-link" target="_blank">
                            {{ \App\Helpers\ImageUploadHelper::getFileName($document->fichier_url) }}
                        </a>
                        <br><small style="color: #6c757d;">Type: {{ \App\Helpers\ImageUploadHelper::getFileExtension($document->fichier_url) }}</small>
                    @else
                        Non fourni
                    @endif
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Ce document a été généré automatiquement par le système SUNU Santé.</p>
        <p>Pour toute question, contactez-nous à support@sunusante.com</p>
        <p>© {{ date('Y') }} SUNU Santé - Tous droits réservés</p>
    </div>
</body>
</html> 