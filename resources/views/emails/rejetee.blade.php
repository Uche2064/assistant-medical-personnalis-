<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'adhésion non approuvée - SUNU Santé</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: "⚠️";
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .rejection-banner {
            background: linear-gradient(135deg, #f8d7da, #f1aeb5);
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            position: relative;
        }
        .rejection-banner::before {
            content: "❌";
            font-size: 32px;
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #dc3545;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rejection-banner h1 {
            color: #721c24;
            margin: 15px 0 10px 0;
            font-size: 24px;
        }
        .rejection-banner .message {
            color: #721c24;
            font-size: 16px;
            font-weight: 500;
        }
        .request-details {
            background-color: #f8f9fa;
            border-left: 6px solid #dc3545;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .request-details h3 {
            color: #dc3545;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .request-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .request-details td {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .request-details td:first-child {
            font-weight: bold;
            width: 35%;
            color: #495057;
        }
        .request-details td:last-child {
            color: #dc3545;
            font-weight: 500;
        }
        .rejection-info {
            background-color: #fff5f5;
            border: 2px solid #fed7d7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .rejection-info h4 {
            color: #c53030;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .rejection-info p {
            color: #c53030;
            margin: 8px 0;
            font-weight: 500;
        }
        .contact-box {
            background-color: #e7f3ff;
            border-left: 6px solid #007bff;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .contact-box h4 {
            color: #084298;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .contact-box p {
            color: #084298;
            margin: 8px 0;
        }
        .contact-box ul {
            color: #084298;
            margin: 10px 0;
            padding-left: 20px;
        }
        .contact-box li {
            margin-bottom: 5px;
        }
        .contact-box strong {
            color: #0056b3;
        }
        .next-steps {
            background-color: #fff8e1;
            border-left: 6px solid #ffc107;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .next-steps h4 {
            color: #856404;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .next-steps p {
            color: #856404;
            margin: 8px 0;
        }
        .next-steps ul {
            color: #856404;
            margin: 10px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 8px;
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
        .divider {
            height: 2px;
            background: linear-gradient(90deg, #dc3545, #c82333, #dc3545);
            margin: 25px 0;
            border-radius: 1px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
            <p class="subtitle">Votre partenaire santé de confiance</p>
        </div>
        
        <div class="content">
            <div class="rejection-banner">
                <h1>📋 Suivi de votre demande d'adhésion</h1>
                <p class="message">Décision concernant votre candidature</p>
            </div>
            
            <p>Cher(e) <strong>@if ($demande->type_demandeur === 'medecin_liberal' || $demande->type_demandeur ==='prospect_moral') {{ $demande->nom_demandeur . ' ' . $demande->prenoms_demandeur }} @else {{ $demande->raison_sociale }} @endif</strong>,</p>
            
            <p>Nous vous remercions pour l'intérêt que vous avez porté à notre plateforme <strong style="color: #dc3545;">SUNU Santé</strong>.</p>
            
            <div class="request-details">
                <h3>📋 Informations de votre demande</h3>
                <table>
                    <tr>
                        <td>🆔 Référence :</td>
                        <td><strong>{{ $demande->id }}</strong></td>
                    </tr>
                    <tr>
                        <td>📅 Date de soumission :</td>
                        <td><strong>{{ $demande->created_at->format('d/m/Y H:i') }}</strong></td>
                    </tr>
                    <tr>
                        <td>📝 Type de demande :</td>
                        <td><strong>{{ ucfirst($demande->type_demandeur) }}</strong></td>
                    </tr>
                    <tr>
                        <td>⚠️ Statut :</td>
                        <td><span style="color: #dc3545; font-weight: bold;">❌ Non approuvée</span></td>
                    </tr>
                </table>
            </div>

            <div class="rejection-info">
                <h4>📋 Décision de votre demande</h4>
                <p><strong>Nous regrettons de vous informer que votre demande d'adhésion n'a pas été approuvée à ce stade.</strong></p>
                @if($demande->motif_rejet)
                <p><strong>Motif :</strong> {{ $demande->motif_rejet }}</p>
                @endif
                <p>Cette décision a été prise après une analyse approfondie de votre dossier selon nos critères d'adhésion actuels.</p>
            </div>

            <div class="next-steps">
                <h4>🔄 Possibilités de recours</h4>
                <p>Si vous estimez que cette décision mérite d'être reconsidérée, vous pouvez :</p>
                <ul>
                    <li>Soumettre des documents complémentaires</li>
                    <li>Corriger les éléments mentionnés dans le motif de rejet</li>
                    <li>Présenter une nouvelle demande après amélioration de votre dossier</li>
                    <li>Demander un rendez-vous pour discuter de votre candidature</li>
                </ul>
            </div>

            <div class="contact-box">
                <h4>📞 Besoin de plus d'informations ?</h4>
                <p>Si vous souhaitez obtenir plus de détails concernant cette décision ou si vous estimez qu'il y a eu une erreur, n'hésitez pas à nous contacter :</p>
                <ul>
                    <li><strong>Email :</strong> adhesions@sunusante.sn</li>
                    <li><strong>Téléphone :</strong> +221 33 XXX XX XX</li>
                    <li><strong>Horaires :</strong> Lundi - Vendredi, 8h00 - 18h00</li>
                </ul>
                <p><strong>Important :</strong> Veuillez mentionner votre numéro de référence <strong>{{ $demande->id }}</strong> dans toute correspondance.</p>
            </div>

            <div class="divider"></div>
            
            <p style="text-align: center; color: #dc3545; font-weight: bold;">
                Nous restons à votre disposition,<br>
                L'équipe SUNU Santé 🏥
            </p>
        </div>
        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>