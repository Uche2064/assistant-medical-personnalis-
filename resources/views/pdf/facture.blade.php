<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $facture_details['numero_facture'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            max-height: 80px;
        }
        .company-info {
            margin-top: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #666;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
            color: #007bff;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .info-value {
            color: #333;
        }
        .patient-info, .sinistre-info, .facture-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .patient-details, .sinistre-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .detail-item {
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background: #007bff;
            color: white;
            font-weight: bold;
        }
        .table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            margin: 5px 0;
            font-size: 14px;
        }
        .total-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .total-value {
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            border-top: 2px solid #007bff;
            padding-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- En-tête avec logo et informations de l'entreprise -->
    <div class="header">
        @if(file_exists($entreprise['logo_path']))
            <img src="{{ $entreprise['logo_path'] }}" alt="Logo" class="logo">
        @endif
        <div class="company-info">
            <div class="company-name">{{ $entreprise['nom'] }}</div>
            <div class="company-details">
                {{ $entreprise['adresse'] }}<br>
                Tél: {{ $entreprise['telephone'] }} | Email: {{ $entreprise['email'] }}<br>
                {{ $entreprise['site_web'] }}
            </div>
        </div>
    </div>

    <!-- Titre de la facture -->
    <div class="invoice-title">FACTURE</div>

    <!-- Informations de la facture -->
    <div class="section">
        <div class="section-title">Informations de la Facture</div>
        <div class="info-grid">
            <div class="info-block">
                <div class="info-label">Numéro de facture:</div>
                <div class="info-value">{{ $facture_details['numero_facture'] }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Date de facture:</div>
                <div class="info-value">{{ $facture_details['date_facture'] }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Statut:</div>
                <div class="info-value">{{ $facture_details['statut'] }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Date de génération:</div>
                <div class="info-value">{{ $dateGeneration }}</div>
            </div>
        </div>
    </div>

    <!-- Informations du patient -->
    <div class="section">
        <div class="section-title">Informations du Patient</div>
        <div class="patient-info">
            <div class="patient-details">
                <div class="detail-item">
                    <div class="detail-label">Nom complet:</div>
                    <div class="detail-value">{{ $patient['nom'] }} {{ $patient['prenoms'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Type:</div>
                    <div class="detail-value">{{ $patient['type'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de naissance:</div>
                    <div class="detail-value">{{ $patient['date_naissance'] ?? 'Non renseigné' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Sexe:</div>
                    <div class="detail-value">{{ $patient['sexe'] ?? 'Non renseigné' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Profession:</div>
                    <div class="detail-value">{{ $patient['profession'] ?? 'Non renseigné' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Contact:</div>
                    <div class="detail-value">{{ $patient['contact'] ?? 'Non renseigné' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">{{ $patient['email'] ?? 'Non renseigné' }}</div>
                </div>
                @if(isset($patient['lien_parente']))
                <div class="detail-item">
                    <div class="detail-label">Lien de parenté:</div>
                    <div class="detail-value">{{ $patient['lien_parente'] }}</div>
                </div>
                @endif
            </div>

                         @if(isset($assure_principal) && $assure_principal)
             <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                 <div style="font-weight: bold; color: #007bff; margin-bottom: 10px;">Assuré Principal:</div>
                 <div class="patient-details">
                     <div class="detail-item">
                         <div class="detail-label">Nom:</div>
                         <div class="detail-value">{{ $assure_principal['nom'] }} {{ $assure_principal['prenoms'] }}</div>
                     </div>
                     <div class="detail-item">
                         <div class="detail-label">Date de naissance:</div>
                         <div class="detail-value">{{ $assure_principal['date_naissance'] ?? 'Non renseigné' }}</div>
                     </div>
                     <div class="detail-item">
                         <div class="detail-label">Sexe:</div>
                         <div class="detail-value">{{ $assure_principal['sexe'] ?? 'Non renseigné' }}</div>
                     </div>
                     <div class="detail-item">
                         <div class="detail-label">Profession:</div>
                         <div class="detail-value">{{ $assure_principal['profession'] ?? 'Non renseigné' }}</div>
                     </div>
                     <div class="detail-item">
                         <div class="detail-label">Contact:</div>
                         <div class="detail-value">{{ $assure_principal['contact'] ?? 'Non renseigné' }}</div>
                     </div>
                     <div class="detail-item">
                         <div class="detail-label">Email:</div>
                         <div class="detail-value">{{ $assure_principal['email'] ?? 'Non renseigné' }}</div>
                     </div>
                 </div>
             </div>
             @endif

            @if(isset($patient['entreprise']))
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <div style="font-weight: bold; color: #007bff; margin-bottom: 10px;">Entreprise:</div>
                <div class="patient-details">
                    <div class="detail-item">
                        <div class="detail-label">Raison sociale:</div>
                        <div class="detail-value">{{ $patient['entreprise']['raison_sociale'] }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Adresse:</div>
                        <div class="detail-value">{{ $patient['entreprise']['adresse'] ?? 'Non renseigné' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact:</div>
                        <div class="detail-value">{{ $patient['entreprise']['contact'] ?? 'Non renseigné' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value">{{ $patient['entreprise']['email'] ?? 'Non renseigné' }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Informations du sinistre -->
    <div class="section">
        <div class="section-title">Informations du Sinistre</div>
        <div class="sinistre-info">
            <div class="sinistre-details">
                <div class="detail-item">
                    <div class="detail-label">Numéro de sinistre:</div>
                    <div class="detail-value">{{ $sinistre['id'] ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date du sinistre:</div>
                    <div class="detail-value">{{ $sinistre['date_sinistre'] ?? 'Non renseigné' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Statut:</div>
                    <div class="detail-value">{{ $sinistre['statut'] ?? 'Non renseigné' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de création:</div>
                    <div class="detail-value">{{ $sinistre['created_at'] ?? 'Non renseigné' }}</div>
                </div>
            </div>
            @if(isset($sinistre['description']) && $sinistre['description'])
            <div style="margin-top: 15px;">
                <div class="detail-label">Description:</div>
                <div class="detail-value">{{ $sinistre['description'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Informations du prestataire -->
    <div class="section">
        <div class="section-title">Informations du Prestataire</div>
        <div class="info-grid">
            <div class="info-block">
                <div class="info-label">Nom:</div>
                <div class="info-value">{{ $facture_details['prestataire']['nom'] }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Adresse:</div>
                <div class="info-value">{{ $facture_details['prestataire']['adresse'] ?? 'Non renseigné' }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Contact:</div>
                <div class="info-value">{{ $facture_details['prestataire']['contact'] ?? 'Non renseigné' }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $facture_details['prestataire']['email'] ?? 'Non renseigné' }}</div>
            </div>
        </div>
    </div>

    <!-- Détail des prestations -->
    <div class="section">
        <div class="section-title">Détail des Prestations</div>
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Garantie</th>
                    <th>Libellé de l'acte</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture_details['lignes'] as $index => $ligne)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $ligne['garantie'] }}</td>
                    <td>{{ $ligne['libelle_acte'] }}</td>
                    <td>{{ $ligne['prix_unitaire'] }} FCFA</td>
                    <td>{{ $ligne['quantite'] }}</td>
                    <td>{{ $ligne['total_ligne'] }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

         <!-- Totaux -->
     <div class="totals">
         <div class="total-row">
             <span class="total-label">Montant total:</span>
             <span class="total-value">{{ $facture_details['montant_total'] }} FCFA</span>
         </div>
         <div class="total-row">
             <span class="total-label">Montant remboursé:</span>
             <span class="total-value">{{ $facture_details['montant_rembourse'] }} FCFA</span>
         </div>
         <div class="total-row grand-total">
             <span class="total-label">Montant à payer:</span>
             <span class="total-value">{{ $facture_details['montant_patient'] }} FCFA</span>
         </div>
     </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>Cette facture a été générée automatiquement le {{ $dateGeneration }}</p>
        <p>{{ $entreprise['nom'] }} - {{ $entreprise['adresse'] }}</p>
        <p>Pour toute question, contactez-nous au {{ $entreprise['telephone'] }} ou par email à {{ $entreprise['email'] }}</p>
    </div>
</body>
</html>
