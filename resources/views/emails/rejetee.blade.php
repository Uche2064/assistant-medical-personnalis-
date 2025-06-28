<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demande d'adhésion non approuvée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #FF0000;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }
        h1 {
            color: #FF0000;
            margin-top: 0;
        }
        .info {
            background-color: #fff0f0;
            border-left: 4px solid #d32f2f;
            padding: 10px;
            margin: 15px 0;
        }
        .contact-box {
            background-color: #f5f5f5;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SUNU Santé</h2>
    </div>
    
    <div class="content">
        <h1>Suivi de votre demande d'adhésion</h1>
        <p>Cher(e) @if ($demande->type_demandeur === 'medecin_liberal' || $demande->type_demandeur ==='prospect_moral') {{ $demande->nom_demandeur . ' ' . $demande->prenoms_demandeur }} @else {{ $demande->raison_sociale }} @endif,</p>
        
        <p>Nous vous remercions pour l'intérêt que vous avez porté à notre plateforme SUNU Santé.</p>
        
        <div class="info">
            <p><strong>Nous regrettons de vous informer que votre demande d'adhésion n'a pas été approuvée à ce stade.</strong></p>
            <p>Référence de votre demande : <strong>{{ $demande->id }}</strong></p>
            <p>Date de soumission : <strong>{{ $demande->created_at->format('d/m/Y H:i') }}</strong></p>
            @if($demande->motif_rejet)
            <p>Motif : <strong>{{ $demande->motif_rejet }}</strong></p>
            @endif
        </div>
        
        <p>Cette décision a été prise après une analyse approfondie de votre dossier selon nos critères d'adhésion actuels.</p>
        
        <div class="contact-box">
            <p><strong>Besoin de plus d'informations ?</strong></p>
            <p>Si vous souhaitez obtenir plus de détails concernant cette décision ou si vous estimez qu'il y a eu une erreur, n'hésitez pas à nous contacter :</p>
            <ul>
                <li>Par téléphone : +221 XX XXX XX XX</li>
                <li>Par e-mail : adhesions@sunusante.sn</li>
            </ul>
            <p>Veuillez mentionner votre numéro de référence dans toute correspondance.</p>
        </div>
        
        <p>Cordialement,<br>
        L'équipe SUNU Santé</p>
    </div>
    
    <div class="footer">
        <p>Ce message est généré automatiquement, merci de ne pas y répondre directement.</p>
        <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
    </div>
</body>
</html>
