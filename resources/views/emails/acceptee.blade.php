<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demande d'adhésion approuvée</title>
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
        .success {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 10px;
            margin: 15px 0;
        }
        .next-steps {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            margin: 10px 0;
        }
        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SUNU Santé</h2>
    </div>
    
    <div class="content">
        <h1>Félicitations ! Votre demande d'adhésion est approuvée</h1>
        
        <p>Cher(e) {{ $demande->type_demande === 'prestataire' ? $demande->raison_sociale : $demande->nom_demandeur . ' ' . $demande->prenoms_demandeur }},</p>
        
        <p>Nous avons le plaisir de vous informer que votre demande d'adhésion à la plateforme SUNU Santé  a été <strong>approuvée</strong>.</p>
        
        <div class="success">
            <p><strong>Votre demande a été acceptée avec succès !</strong></p>
            <p>Référence de votre demande : <strong>{{ $demande->id }}</strong></p>
            <p>Type de demande : <strong>{{ ucfirst($demande->type_demande->value) }}</strong></p>
            <p>Date d'approbation : <strong>{{ now()->format('d/m/Y H:i') }}</strong></p>
        </div>
        
        <a href="https://app.sunusante.sn/login" class="button">Accéder à la plateforme</a>

        
        <p>Toute l'équipe de SUNU Santé vous souhaite la bienvenue et reste à votre disposition pour vous accompagner dans l'utilisation de notre plateforme.</p>
        
        <p>Pour toute question, notre service d'assistance est disponible par e-mail à support@sunusante.sn ou par téléphone au +221 XX XXX XX XX.</p>
        
        <p>Cordialement,<br>
        L'équipe SUNU Santé</p>
    </div>
    
    <div class="footer">
        <p>Ce message est généré automatiquement, merci de ne pas y répondre directement.</p>
        <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
    </div>
</body>
</html>
