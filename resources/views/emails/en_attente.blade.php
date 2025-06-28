<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'adhésion reçue</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 24px;
            color: #FF0000;
        }
        p {
            margin: 10px 0;
        }
        .info {
            background-color: #f9f9f9;
            border-left: 4px solid #FF0000;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 5px 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .button:hover {
            background-color: #FF0000;
        }
        
        .highlight {
            color: #FF0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>Demande d'adhésion reçue</h1>
        
        <p>Bonjour {{ $demande->type_demande === 'prestataire' ? $demande->raison_sociale : $demande->nom_demandeur . ' ' . $demande->prenoms_demandeur }},</p>
        
        <p>Nous vous remercions pour votre demande d'adhésion à notre plateforme <strong>SUNU Santé "Zéro Papier"</strong>.</p>
        
        <div class="info">
            <p><strong>Votre demande est actuellement en cours d'examen par notre équipe.</strong></p>
            <p><strong>Référence :</strong> {{ $demande->id }}</p>
            <p><strong>Type de demande :</strong> {{ ucfirst($demande->type_demande->value) }}</p>
            <p><strong>Date de soumission :</strong> {{ $demande->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        
        <p>Notre équipe examine attentivement toutes les demandes et vous serez notifié(e) par e-mail dès que votre demande sera traitée.</p>
                
        <p>Cordialement,<br>
        <strong>L'équipe SUNU Santé</strong></p>
    </div>
    
    <div class="footer">
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        <p>&copy; {{ date('Y') }} Sunu Santé. Tous droits réservés.</p>
    </div>
</body>
</html>