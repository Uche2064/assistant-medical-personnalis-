<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Créé - SUNU Santé</title>
    <style>
        body {
            font-family: 'Poppins', Verdana, sans-serif;
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
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .success-banner {
            background: linear-gradient(135deg, #d1ecf1, #b3d7ff);
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .success-banner h1 {
            color: #004085;
            margin: 15px 0;
            font-size: 24px;
        }
        .account-details {
            background-color: #f8f9fa;
            border-left: 6px solid #007bff;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .account-details h3 {
            color: #007bff;
            margin: 0 0 15px;
        }
        .account-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .account-details td {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .account-details td:first-child {
            font-weight: bold;
            color: #495057;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 5px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
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
                <h1>🎊 Compte Créé avec Succès ! 🎊</h1>
                <p>Bienvenue dans la famille SUNU Santé</p>
            </div>
            
            <p>Bonjour <strong>{{ $user->personnel->nom ?? 'Utilisateur' }}</strong>,</p>
            <p>Félicitations ! Votre compte SUNU Santé a été <strong style="color: #007bff;">créé avec succès</strong> ! 🎉</p>
            <p>Nous sommes ravis de vous accueillir dans notre communauté dédiée à votre bien-être.</p>
            
            <div class="account-details">
                <h3>👤 Informations de votre compte</h3>
                <table>
                    <tr>
                        <td>📧 Email :</td>
                        <td><strong>{{ $user->email ?? 'email@exemple.com' }}</strong></td>
                    </tr>
                    <tr>
                        <td>📅 Date de création :</td>
                        <td><strong>{{ $user->created_at->format('d/m/Y à H:i') }}</strong></td>
                    </tr>
                </table>
            </div>

            <p style="text-align: center; color: #007bff; font-weight: bold;">
                Bienvenue dans votre nouveau parcours santé !<br>
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
