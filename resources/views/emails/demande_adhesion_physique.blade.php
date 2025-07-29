<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouvelle demande d'adhésion personne physique</title>
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
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-table td:first-child {
            font-weight: bold;
            color: #333;
            width: 40%;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 10px 10px;
            font-size: 13px;
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
            <h3>Nouvelle demande d'adhésion - Personne physique</h3>
            <p>Un nouveau prospect vient de soumettre une demande d'adhésion. Voici les informations principales :</p>
            <table class="info-table">
                <tr>
                    <td>Nom :</td>
                    <td>{{ $user->client->nom ?? $user->nom ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Prénoms :</td>
                    <td>{{ $user->client->prenoms ?? $user->prenoms ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Email :</td>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <td>Téléphone :</td>
                    <td>{{ $user->contact ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Date de naissance :</td>
                    <td>{{ $user->client->date_naissance ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Sexe :</td>
                    <td>{{ $user->client->sexe ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Profession :</td>
                    <td>{{ $user->client->profession ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Adresse :</td>
                    <td>{{ $user->adresse ?? '-' }}</td>
                </tr>
            </table>
            <p><strong>Consultez la plateforme pour voir le détail complet de la demande et le questionnaire médical.</strong></p>
        </div>
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Plateforme de gestion d'assurance santé</p>
            <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{ date('Y') }} SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html> 