<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre compte SUNU Santé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f7fafc;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 640px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #dc2626;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .otp-section {
            background: #f8fafc;
            border: 2px solid #dc2626;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .cta-button {
            display: inline-block;
            background: #dc2626;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>SUNU SANTÉ</h1>
            <p>Votre partenaire santé de confiance</p>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Merci de vous être inscrit sur <strong>SUNU Santé</strong> !</p>
            <p>Pour finaliser la création de votre compte, veuillez saisir le code de vérification ci-dessous :</p>
            <div class="otp-section">
                <h2>Code de vérification</h2>
                <p>{{ $otp->otp }}</p>
            </div>
            <p>Ce code expire le {{ $otp->expire_at->format('d/m/Y à H:i') }}</p>
            <a href="{{ config('app.url') }}" class="cta-button">Accéder à SUNU Santé</a>
        </div>
        <div class="footer">
            <p>Merci pour votre confiance,</p>
            <p>L'équipe SUNU Santé</p>
            <p>© {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
