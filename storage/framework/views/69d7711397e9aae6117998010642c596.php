<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?php echo e($facture_details['numero_facture']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #c7183e;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            max-height: 80px;
        }
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #c7183e;
            margin-bottom: 10px;
        }
        .company-info {
            margin-top: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #c7183e;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #666;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
            color: #c7183e;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #c7183e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #c7183e;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .info-value {
            color: #333;
        }
        .patient-info, .sinistre-info, .facture-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .patient-details, .sinistre-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .detail-item {
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background: #c7183e;
            color: white;
            font-weight: bold;
        }
        .table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            margin: 5px 0;
            font-size: 14px;
        }
        .total-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .total-value {
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
                color: #c7183e;
            border-top: 2px solid #c7183e;
            padding-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- En-tête avec logo et informations de l'entreprise -->
    <div class="header">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($entreprise['logo_base64']) && $entreprise['logo_base64']): ?>
            <img src="data:image/png;base64,<?php echo e($entreprise['logo_base64']); ?>" alt="Sunu santé" class="logo">
        <?php else: ?>
            <div class="logo-text"><?php echo e($entreprise['nom']); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="company-info">
            <div class="company-name"><?php echo e($entreprise['nom']); ?></div>
            <div class="company-details">
                <?php echo e($entreprise['adresse']); ?><br>
                Tél: <?php echo e($entreprise['telephone']); ?> | Email: <?php echo e($entreprise['email']); ?><br>
                <?php echo e($entreprise['site_web']); ?>

            </div>
        </div>
    </div>

    <!-- Titre de la facture -->
    <div class="invoice-title">FACTURE</div>

    <!-- Informations de la facture -->
    <div class="section">
        <div class="section-title">Informations de la Facture</div>
        <div class="info-grid">
            <div class="info-block">
                <div class="info-label">Numéro de facture:</div>
                <div class="info-value"><?php echo e($facture_details['numero_facture']); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Date de facture:</div>
                <div class="info-value"><?php echo e($facture_details['date_facture']); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Statut:</div>
                <div class="info-value"><?php echo e($facture_details['statut']); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Date de génération:</div>
                <div class="info-value"><?php echo e($dateGeneration); ?></div>
            </div>
        </div>
    </div>

    <!-- Informations du patient -->
    <div class="section">
        <div class="section-title">Informations du Patient</div>
        <div class="patient-info">
            <div class="patient-details">
                <div class="detail-item">
                    <div class="detail-label">Nom complet:</div>
                    <div class="detail-value"><?php echo e($patient['nom']); ?> <?php echo e($patient['prenoms']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Type:</div>
                    <div class="detail-value"><?php echo e($patient['type']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de naissance:</div>
                    <div class="detail-value"><?php echo e($patient['date_naissance'] ?? 'Non renseigné'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Sexe:</div>
                    <div class="detail-value"><?php echo e($patient['sexe'] ?? 'Non renseigné'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Profession:</div>
                    <div class="detail-value"><?php echo e($patient['profession'] ?? 'Non renseigné'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Contact:</div>
                    <div class="detail-value"><?php echo e($patient['contact'] ?? 'Non renseigné'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value"><?php echo e($patient['email'] ?? 'Non renseigné'); ?></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($patient['lien_parente'])): ?>
                <div class="detail-item">
                    <div class="detail-label">Lien de parenté:</div>
                    <div class="detail-value"><?php echo e($patient['lien_parente']); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($patient['entreprise'])): ?>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <div style="font-weight: bold; color: #c7183e; margin-bottom: 10px;">Entreprise:</div>
                <div class="patient-details">
                    <div class="detail-item">
                        <div class="detail-label">Raison sociale:</div>
                        <div class="detail-value"><?php echo e($patient['entreprise']['raison_sociale']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Adresse:</div>
                        <div class="detail-value"><?php echo e($patient['entreprise']['adresse'] ?? 'Non renseigné'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact:</div>
                        <div class="detail-value"><?php echo e($patient['entreprise']['contact'] ?? 'Non renseigné'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value"><?php echo e($patient['entreprise']['email'] ?? 'Non renseigné'); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Informations du sinistre -->
    <div class="section">
        <div class="section-title">Informations du Sinistre</div>
        <div class="sinistre-info">
            <div class="sinistre-details">
                <div class="detail-item">
                    <div class="detail-label">Numéro de sinistre:</div>
                    <div class="detail-value"><?php echo e($sinistre['id'] ?? 'N/A'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date du sinistre:</div>
                    <div class="detail-value"><?php echo e($sinistre['date_sinistre'] ?? 'Non renseigné'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Statut:</div>
                    <div class="detail-value"><?php echo e($sinistre['statut'] ?? 'Non renseigné'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de création:</div>
                    <div class="detail-value"><?php echo e($sinistre['created_at'] ?? 'Non renseigné'); ?></div>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($sinistre['description']) && $sinistre['description']): ?>
            <div style="margin-top: 15px;">
                <div class="detail-label">Description:</div>
                <div class="detail-value"><?php echo e($sinistre['description']); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Informations du prestataire -->
    <div class="section">
        <div class="section-title">Informations du Prestataire</div>
        <div class="info-grid">
            <div class="info-block">
                <div class="info-label">Nom:</div>
                <div class="info-value"><?php echo e($facture_details['prestataire']['nom']); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Adresse:</div>
                <div class="info-value"><?php echo e($facture_details['prestataire']['adresse'] ?? 'Non renseigné'); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Contact:</div>
                <div class="info-value"><?php echo e($facture_details['prestataire']['contact'] ?? 'Non renseigné'); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo e($facture_details['prestataire']['email'] ?? 'Non renseigné'); ?></div>
            </div>
        </div>
    </div>

    <!-- Détail des prestations -->
    <div class="section">
        <div class="section-title">Détail des Prestations</div>
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Garantie</th>
                    <th>Libellé de l'acte</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $facture_details['lignes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ligne): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($ligne['garantie']); ?></td>
                    <td><?php echo e($ligne['libelle_acte']); ?></td>
                    <td><?php echo e($ligne['prix_unitaire']); ?> FCFA</td>
                    <td><?php echo e($ligne['quantite']); ?></td>
                    <td><?php echo e($ligne['total_ligne']); ?> FCFA</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

         <!-- Totaux -->
     <div class="totals">
         <div class="total-row">
             <span class="total-label">Montant total:</span>
             <span class="total-value"><?php echo e($facture_details['montant_total']); ?> FCFA</span>
         </div>
         <div class="total-row">
             <span class="total-label">Montant remboursé:</span>
             <span class="total-value"><?php echo e($facture_details['montant_rembourse']); ?> FCFA</span>
         </div>
         <div class="total-row grand-total">
             <span class="total-label">Montant à payer:</span>
             <span class="total-value"><?php echo e($facture_details['montant_patient']); ?> FCFA</span>
         </div>
     </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>Cette facture a été générée automatiquement le <?php echo e($dateGeneration); ?></p>
        <p><?php echo e($entreprise['nom']); ?> - <?php echo e($entreprise['adresse']); ?></p>
        <p>Pour toute question, contactez-nous au <?php echo e($entreprise['telephone']); ?> ou par email à <?php echo e($entreprise['email']); ?></p>
    </div>
</body>
</html>
<?php /**PATH G:\projects\amp\amp_backend\resources\views\pdf\facture.blade.php ENDPATH**/ ?>