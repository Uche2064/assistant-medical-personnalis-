<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre contrat SUNU Santé est prêt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            color: #FF0000;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        h1 {
            color: #FF0000;
            font-size: 24px;
            margin-bottom: 10px;
        }
        h2 {
            color: #333;
            font-size: 18px;
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 2px solid #FF0000;
            padding-bottom: 5px;
        }
        p {
            line-height: 1.6;
            color: #333;
            margin-bottom: 15px;
        }
        .success-message {
            background-color: #d4edda;
            border-left: 6px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .contract-details {
            background-color: #f8f9fa;
            border-left: 6px solid #0066CC;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .contract-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .contract-details td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .contract-details td:first-child {
            font-weight: bold;
            width: 40%;
            color: #555;
        }
        .credentials {
            background-color: #FFCCCC;
            border-left: 6px solid #FF0000;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credentials table {
            width: 100%;
            border-collapse: collapse;
        }
        .credentials td {
            padding: 8px 0;
        }
        .credentials td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
        }
        .button:hover {
            background-color: #CC0000;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 6px solid #ffc107;
            padding: 10px 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        .contact-info {
            background-color: #e9ecef;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">SUNU SANTÉ</div>
        </div>

        <h1>Félicitations {{ $user->prenoms }} {{ $user->nom }} !</h1>
        
        <div class="success-message">
            <p><strong>🎉 Votre contrat d'assurance SUNU Santé est maintenant finalisé et actif !</strong></p>
        </div>

        <p>Nous sommes ravis de vous accueillir dans la famille SUNU Santé. Vous pouvez dès à présent accéder à notre plateforme pour gérer vos prestations, suivre vos remboursements et profiter de tous nos services.</p>
        
        <h2>📋 Détails de votre contrat</h2>
        <div class="contract-details">
            <table>
                <tr>
                    <td>Numéro de police :</td>
                    <td><strong>{{ $contrat->numero_police ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td>Date de début :</td>
                    <td>{{ $contrat->date_debut ? $contrat->date_debut->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Date de fin :</td>
                    <td>{{ $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Prime annuelle :</td>
                    <td><strong>{{ $contrat->prime ? number_format($contrat->prime, 0, ',', ' ') : '0' }} FCFA</strong></td>
                </tr>
                <tr>
                    <td>Statut :</td>
                    <td><span style="color: #28a745; font-weight: bold;">✅ Actif</span></td>
                </tr>
            </table>
        </div>

        <h2>🔐 Vos identifiants de connexion</h2>
        <div class="credentials">
            <table>
                <tr>
                    <td>Email :</td>
                    <td><strong>{{ $user->email }}</strong></td>
                </tr>
                <tr>
                    <td>Mot de passe :</td>
                    <td><strong>{{ $password }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="warning">
            <strong>⚠️ Important :</strong> Pour votre sécurité, nous vous recommandons fortement de changer votre mot de passe lors de votre première connexion.
        </div>
        
        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/login" class="button">🚀 Se connecter maintenant</a>
        </div>

        <h2>📞 Besoin d'aide ?</h2>
        <div class="contact-info">
            <p><strong>Notre service client est là pour vous accompagner :</strong></p>
            <p>
                📧 Email : support@sunusante.com<br>
                📱 Téléphone : +221 33 XXX XX XX<br>
                🕒 Horaires : Lundi - Vendredi, 8h00 - 18h00
            </p>
        </div>

        <p>Vous pouvez également consulter notre guide d'utilisation et notre FAQ directement sur la plateforme après votre connexion.</p>

        <div class="footer">
            <p><strong>Merci de votre confiance,</strong><br>
            L'équipe SUNU Santé</p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">
            <p style="font-size: 12px; color: #999;">
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.<br>
                SUNU Santé - Votre partenaire santé de confiance
            </p>
        </div>
    </div>
</body>
</html>