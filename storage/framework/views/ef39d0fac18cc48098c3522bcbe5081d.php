<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'adhésion reçue - SUNU Santé</title>
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
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: "📋";
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
        .success-banner {
            background: linear-gradient(135deg, #cce7ff, #b3d9ff);
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            position: relative;
        }
        .success-banner::before {
            content: "📨";
            font-size: 32px;
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-banner h1 {
            color: #003d82;
            margin: 15px 0 10px 0;
            font-size: 24px;
        }
        .success-banner .message {
            color: #003d82;
            font-size: 16px;
            font-weight: 500;
        }
        .request-details {
            background-color: #f8f9fa;
            border-left: 6px solid #007bff;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .request-details h3 {
            color: #007bff;
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
            color: #007bff;
            font-weight: 500;
        }
        .info-banner {
            background-color: #e7f3ff;
            border-left: 6px solid #17a2b8;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-banner h4 {
            color: #0c5460;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-banner p {
            color: #0c5460;
            margin: 8px 0;
            font-weight: 500;
        }
       
        .contact-info {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .contact-info h4 {
            color: #495057;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .contact-info p {
            margin: 8px 0;
            color: #6c757d;
        }
        .contact-info strong {
            color: #007bff;
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
            background: linear-gradient(90deg, #007bff, #0056b3, #007bff);
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
            <div class="success-banner">
                <h1>📋 Demande d'adhésion reçue !</h1>
                <p class="message">Votre demande est en cours de traitement</p>
            </div>
            
            <p>Bonjour <strong><?php echo e($demande->type_demande === 'prestataire' ? $demande->raison_sociale : $demande->nom_demandeur . ' ' . $demande->prenoms_demandeur); ?></strong>,</p>
            
            <p>Nous vous remercions pour votre demande d'adhésion à notre plateforme <strong style="color: #007bff;">SUNU Santé "Zéro Papier"</strong>. 🚀</p>
            
            <div class="request-details">
                <h3>📊 Informations de votre demande</h3>
                <table>
                    <tr>
                        <td>🆔 Référence :</td>
                        <td><strong><?php echo e($demande->id); ?></strong></td>
                    </tr>
                    <tr>
                        <td>📝 Type de demande :</td>
                        <td><strong><?php echo e(ucfirst($demande->type_demande->value)); ?></strong></td>
                    </tr>
                    <tr>
                        <td>📅 Date de soumission :</td>
                        <td><strong><?php echo e($demande->created_at->format('d/m/Y à H:i')); ?></strong></td>
                    </tr>
                    <tr>
                        <td>⏳ Statut :</td>
                        <td><span style="color: #ffc107; font-weight: bold;">🔄 En cours d'examen</span></td>
                    </tr>
                </table>
            </div>

            <div class="info-banner">
                <h4>ℹ️ Information importante</h4>
                <p><strong>Votre demande est actuellement en cours d'examen par notre équipe spécialisée.</strong></p>
                <p>Nous analysons attentivement tous les documents fournis pour garantir la qualité de notre plateforme.</p>
            </div>

            <div class="contact-info">
                <h4>📞 Besoin d'assistance ?</h4>
                <p><strong>Email :</strong> adhesions@sunusante.sn</p>
                <p><strong>Téléphone :</strong> +221 33 XXX XX XX</p>
                <p><strong>Horaires :</strong> Lundi - Vendredi, 8h00 - 18h00</p>
                <p style="margin-top: 15px; font-style: italic;">Notre équipe est disponible pour toute question concernant votre demande.</p>
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
            <p>&copy; <?php echo e(date('Y')); ?> SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html><?php /**PATH G:\projects\amp\amp_backend\resources\views\emails\en_attente.blade.php ENDPATH**/ ?>