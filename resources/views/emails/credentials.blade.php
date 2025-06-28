<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos identifiants de connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #FF0000;
        }
        p {
            line-height: 1.6;
            color: #000000;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #000000;
        }
        .highlight {
            background-color: #FFCCCC;
            border-left: 6px solid #FF0000;
            padding: 10px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bonjour {{ $user->prenoms }} {{ $user->nom }},</h1>
        <p>Vos identifiants pour accéder à la plateforme ont été créés.</p>
        
        <div class="highlight">
            <strong>Email :</strong> {{ $user->email }}<br>
            <strong>Mot de passe :</strong> {{ $password }}
        </div>

        <p>Nous vous recommandons de changer votre mot de passe après votre première connexion.</p>
        
        <a href="#" class="button">Se connecter maintenant</a>

        <p class="footer">Merci,<br>L'équipe SUNU Santé</p>
    </div>
</body>
</html>
