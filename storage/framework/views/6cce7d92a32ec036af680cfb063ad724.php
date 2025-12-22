<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouvelle demande d'adhésion personne physique</title>
    <style>
        body {
            font-family: Verdana, sans-serif;
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
            <h3>Demande engistrée</h3>
            <p>Votre demande a été enregistré avec succès. Elle sera traitée et un évaluée par nos technicien. Un mail vous serez envoyé par après.</p>
        </div>
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Plateforme de gestion d'assurance santé</p>
            <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; <?php echo e(date('Y')); ?> SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html> <?php /**PATH G:\projects\amp\amp_backend\resources\views\emails\demande_adhesion_enregistre.blade.php ENDPATH**/ ?>