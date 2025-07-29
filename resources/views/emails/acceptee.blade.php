<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demande d'adhésion approuvée - SUNU Santé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #28a745;
            color: white;
            padding: 25px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .success-banner {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .success-banner h1 {
            color: #155724;
            margin: 15px 0;
            font-size: 24px;
        }
        .demande-details, .contrat-details {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .demande-details h3, .contrat-details h3 {
            color: #28a745;
            margin-top: 0;
        }
        .demande-details table, .contrat-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .demande-details td, .contrat-details td {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .demande-details td:first-child, .contrat-details td:first-child {
            font-weight: bold;
            color: #495057;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-size: 16px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #218838;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
        </div>
        
        <div class="content">
            <div class="success-banner">
                <h1>🎊 FÉLICITATIONS ! 🎊</h1>
                <p>Votre demande d'adhésion a été approuvée avec succès</p>
            </div>
            
            <p>Cher(e) <strong>{{ $demande->type_demandeur !== 'physique' ? $demande->raison_sociale : $demande->personne->nom . ' ' . $demande->personne->prenoms }}</strong>,</p>
            <p>Nous avons le plaisir de vous informer que votre demande d'adhésion à SUNU Santé a été <strong style="color: #28a745;">APPROUVÉE</strong> ! 🎉</p>
            
            <div class="demande-details">
                <h3>📋 Détails de votre demande</h3>
                <table>
                    <tr>
                        <td>Date d'approbation :</td>
                        <td><strong>{{ now()->format('d/m/Y à H:i') }}</strong></td>
                    </tr>
                </table>
            </div>

            @if(isset($contrat) && $contrat)
            <div class="contrat-details">
                <h3>📋 Détails du contrat</h3>
                <table>
                    <tr>
                        <td>ID du contrat :</td>
                        <td><strong>{{ $contrat->id }}</strong></td>
                    </tr>
                    <tr>
                        <td>Type de contrat :</td>
                        <td><strong>{{ $contrat->type_contrat ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Prime standard :</td>
                        <td><strong>{{ number_format($contrat->prime_standard, 2, ',', ' ') }} FCFA</strong></td>
                    </tr>
                </table>
            </div>
            @endif

            <div class="button-container">
                <a href="https://app.sunusante.sn/login" class="button">🚀 Accéder à ma plateforme</a>
            </div>

            <p style="text-align: center; color: #dc3545; font-weight: bold;">
                Nous restons à votre disposition,<br>
                L'équipe SUNU Santé 🏥
            </p>
        </div>
        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Ce message est généré automatiquement, merci de ne pas y répondre directement.</p>
            <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
