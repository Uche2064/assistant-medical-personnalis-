<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle fiche employé soumise - SUNU Santé</title>
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
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: "👤";
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
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            position: relative;
        }
        .success-banner::before {
            content: "✅";
            font-size: 32px;
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #28a745;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-banner h1 {
            color: #155724;
            margin: 15px 0 10px 0;
            font-size: 24px;
        }
        .success-banner .message {
            color: #155724;
            font-size: 16px;
            font-weight: 500;
        }
        .employee-details {
            background-color: #f8f9fa;
            border-left: 6px solid #28a745;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .employee-details h3 {
            color: #28a745;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
        }
        .company-info {
            background-color: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .company-info h4 {
            color: #155724;
            margin: 0 0 10px 0;
        }
        .next-steps {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .next-steps h4 {
            color: #856404;
            margin: 0 0 15px 0;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
            color: #856404;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
        }
        .button:hover {
            background: linear-gradient(135deg, #218838, #1ea085);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SUNU SANTÉ</h2>
            <div class="subtitle">Nouvelle fiche employé soumise</div>
        </div>
        
        <div class="content">
            <div class="success-banner">
                <h1>✅ Nouvelle fiche employé reçue</h1>
                <div class="message">Un employé a soumis sa fiche d'adhésion avec succès</div>
            </div>

            <p>Bonjour,</p>
            
            <p>Une nouvelle fiche employé a été soumise pour votre entreprise. Voici les détails :</p>

            <div class="employee-details">
                <h3>📋 Informations de l'employé</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Nom complet :</span>
                    <span class="detail-value"><?php echo e($assure->nom); ?> <?php echo e($assure->prenoms); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email :</span>
                    <span class="detail-value"><?php echo e($assure->email); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Contact :</span>
                    <span class="detail-value"><?php echo e($assure->contact ?? 'Non renseigné'); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date de naissance :</span>
                    <span class="detail-value"><?php echo e($assure->date_naissance ? \Carbon\Carbon::parse($assure->date_naissance)->format('d/m/Y') : 'Non renseignée'); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Sexe :</span>
                    <span class="detail-value"><?php echo e($assure->sexe == 'M' ? 'Masculin' : 'Féminin'); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Profession :</span>
                    <span class="detail-value"><?php echo e($assure->profession ?? 'Non renseignée'); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date de soumission :</span>
                    <span class="detail-value"><?php echo e(\Carbon\Carbon::now()->format('d/m/Y à H:i')); ?></span>
                </div>
            </div>

            <div class="company-info">
                <h4>🏢 Informations de l'entreprise</h4>
                <p><strong>Entreprise :</strong> <?php echo e($entreprise->raison_sociale); ?></p>
                <p><strong>Secteur d'activité :</strong> <?php echo e($entreprise->secteur_activite ?? 'Non renseigné'); ?></p>
            </div>

            <div class="next-steps">
                <h4>📝 Prochaines étapes</h4>
                <ul>
                    <li>La fiche sera examinée par notre équipe médicale</li>
                    <li>Un processus de validation sera initié</li>
                    <li>Vous recevrez une notification une fois la validation terminée</li>
                    <li>L'employé pourra accéder à ses services une fois validé</li>
                </ul>
            </div>

            <p>Cette notification vous permet de suivre l'évolution des demandes d'adhésion de vos employés.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo e(env('FRONTEND_URL')); ?>/entreprise/dashboard" class="button">📊 Voir le dashboard</a>
            </div>

            <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe SUNU Santé</strong></p>
        </div>
        
        <div class="footer">
            <p><strong>SUNU Santé</strong> - Votre partenaire santé de confiance</p>
            <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; <?php echo e(date('Y')); ?> SUNU Santé. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html> <?php /**PATH G:\projects\amp\amp_backend\resources\views\emails\nouvelle_fiche_employe.blade.php ENDPATH**/ ?>