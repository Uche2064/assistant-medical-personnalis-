<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposition de contrat SUNU Santé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #c7183e;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px 0;
        }
        .proposition-banner {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .proposition-banner h2 {
            margin-top: 0;
            color: #856404;
        }
        .contrat-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .contrat-details h3 {
            margin-top: 0;
            color: #c7183e;
        }
        .contrat-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .contrat-details table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .contrat-details table td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #c7183e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #a0142f;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }
        .commentaires {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .commentaires h4 {
            margin-top: 0;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SUNU SANTÉ</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $user->personne->nom ?? $user->email }} {{ $user->personne->prenoms ?? '' }}</strong>,</p>
            
            <div class="proposition-banner">
                <h2>📋 Nouvelle proposition de contrat</h2>
                <p>Un technicien a analysé votre demande d'adhésion et vous propose un contrat adapté à vos besoins.</p>
            </div>
            
            <div class="contrat-details">
                <h3>📋 Détails de la proposition</h3>
                <table>
                    <tr>
                        <td>Type de contrat :</td>
                        <td><strong>{{ $contrat->libelle ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Prime standard :</td>
                        <td><strong>{{ number_format($contrat->prime_standard ?? 0, 0, ',', ' ') }} FCFA</strong></td>
                    </tr>
                    <tr>
                        <td>Proposé par :</td>
                        <td><strong>{{ $technicien->nom ?? '' }} {{ $technicien->prenoms ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Date de proposition :</td>
                        <td><strong>{{ $proposition->date_proposition ? $proposition->date_proposition->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}</strong></td>
                    </tr>
                </table>
            </div>

            @if($proposition->commentaires_technicien)
            <div class="commentaires">
                <h4>💬 Commentaires du technicien</h4>
                <p>{{ $proposition->commentaires_technicien }}</p>
            </div>
            @endif

            <div class="button-container">
                <a href="{{ config('app.url') }}/propositions-contrats" class="button">Voir la proposition</a>
            </div>

            <p><strong>⚠️ Important :</strong> Vous avez un délai pour accepter ou refuser cette proposition. Connectez-vous à votre espace client pour consulter les détails complets.</p>
        </div>

        <div class="footer">
            <p><strong>Merci de votre confiance,</strong><br>L'équipe SUNU Santé</p>
            <p style="font-size: 12px; color: #999;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>

