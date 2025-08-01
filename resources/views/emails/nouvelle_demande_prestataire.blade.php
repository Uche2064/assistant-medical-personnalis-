<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande d'adhésion prestataire</title>
    <style>
        body {
            font-family: Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .info-box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nouvelle demande d'adhésion prestataire</h1>
    </div>
    
    <div class="content">
        <p>Bonjour {{ $medecin->nom }} {{ $medecin->prenoms }},</p>
        
        <p>Une nouvelle demande d'adhésion a été soumise par un prestataire de soins.</p>
        
        <div class="info-box">
            <h3>Informations du prestataire :</h3>
            <p><strong>Établissement :</strong> {{ $prestataire->nom_etablissement }}</p>
            <p><strong>Raison sociale :</strong> {{ $prestataire->raison_sociale }}</p>
            <p><strong>Type :</strong> {{ $prestataire->type_prestataire }}</p>
            <p><strong>Adresse :</strong> {{ $prestataire->adresse }}, {{ $prestataire->ville }}, {{ $prestataire->pays }}</p>
            <p><strong>Contact :</strong> {{ $prestataire->contact }}</p>
            <p><strong>Email :</strong> {{ $prestataire->email }}</p>
            <p><strong>Responsable :</strong> {{ $prestataire->responsable_nom }} {{ $prestataire->responsable_prenoms }}</p>
        </div>
        
        <div class="info-box">
            <h3>Détails de la demande :</h3>
            <p><strong>Numéro de demande :</strong> #{{ $demande->id }}</p>
            <p><strong>Date de soumission :</strong> {{ $demande->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Statut :</strong> En attente de validation</p>
        </div>
        
        <p>Veuillez examiner cette demande et procéder à sa validation ou à son rejet selon les critères établis.</p>
        
        <p>Vous pouvez accéder à la demande complète via votre interface de gestion.</p>
        
        <p>Cordialement,<br>
        L'équipe SUNU Santé</p>
    </div>
    
    <div class="footer">
        <p>Cet email a été envoyé automatiquement par le système SUNU Santé.</p>
        <p>Si vous avez des questions, veuillez contacter le support technique.</p>
    </div>
</body>
</html> 