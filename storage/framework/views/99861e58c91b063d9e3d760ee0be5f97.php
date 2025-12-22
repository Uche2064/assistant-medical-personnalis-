<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'Adhésion - Entreprise - SUNU Santé</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 10px;
        }
        .title {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        .demande-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #2c5aa0;
            min-width: 150px;
        }
        .info-value {
            flex: 1;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c5aa0;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .user-info {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
        }
        .employes-section {
            margin-top: 20px;
        }
        .employe-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .employe-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .employe-name {
            font-weight: bold;
            color: #2c5aa0;
            font-size: 14px;
        }
        .employe-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 10px;
            background-color: #e9ecef;
            color: #6c757d;
        }
        .beneficiaires-list {
            margin-top: 10px;
            padding-left: 15px;
        }
        .beneficiaire-item {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-en-attente {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-validee {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejetee {
            background-color: #f8d7da;
            color: #721c24;
        }
        .page-break {
            page-break-before: always;
        }
        .file-link {
            color: #2c5aa0;
            text-decoration: underline;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .stat-card {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SUNU SANTÉ</div>
        <div class="title">Demande d'Adhésion - Entreprise</div>
        <div style="font-size: 12px; color: #666;">Document généré le <?php echo e(now()->format('d/m/Y à H:i')); ?></div>
    </div>

    <div class="demande-info">
        <div class="info-row">
            <span class="info-label">N° Demande :</span>
            <span class="info-value"><?php echo e($demande->id); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Type de demandeur :</span>
            <span class="info-value"><?php echo e(ucfirst($demande->type_demandeur->value)); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut :</span>
            <span class="info-value">
                <span class="status-badge status-<?php echo e(str_replace('_', '-', $demande->statut->value)); ?>">
                    <?php echo e(ucfirst(str_replace('_', ' ', $demande->statut->value))); ?>

                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de soumission :</span>
            <span class="info-value"><?php echo e($demande->created_at->format('d/m/Y à H:i')); ?></span>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demande->validePar): ?>
        <div class="info-row">
            <span class="info-label">Validée par :</span>
            <span class="info-value"><?php echo e($demande->validePar->nom ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de validation :</span>
            <span class="info-value"><?php echo e($demande->valider_a ? \Carbon\Carbon::parse($demande->valider_a)->format('d/m/Y à H:i') : 'N/A'); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demande->motif_rejet): ?>
        <div class="info-row">
            <span class="info-label">Motif de rejet :</span>
            <span class="info-value"><?php echo e($demande->motif_rejet); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">Informations de l'entreprise</div>
        <div class="user-info">
            <div class="info-row">
                <span class="info-label">Raison sociale :</span>
                <span class="info-value">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demande->user->entreprise && $demande->user->entreprise->raison_sociale): ?>
                        <?php echo e($demande->user->entreprise->raison_sociale); ?>

                    <?php else: ?>
                        N/A
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Email :</span>
                <span class="info-value"><?php echo e($demande->user->email); ?></span>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demande->user->contact): ?>
            <div class="info-row">
                <span class="info-label">Téléphone :</span>
                <span class="info-value"><?php echo e($demande->user->contact); ?></span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demande->user->entreprise && $demande->user->entreprise->adresse): ?>
            <div class="info-row">
                <span class="info-label">Adresse :</span>
                <span class="info-value"><?php echo e($demande->user->entreprise->adresse); ?></span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($statistiques)): ?>
    <div class="section">
        <div class="section-title">Statistiques globales</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo e($statistiques['total_personnes']); ?></div>
                <div class="stat-label">Total de personnes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo e($statistiques['total_employes']); ?></div>
                <div class="stat-label">Total d'employés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo e($statistiques['total_beneficiaires']); ?></div>
                <div class="stat-label">Total de bénéficiaires</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo e($statistiques['repartition_sexe']['hommes'] + $statistiques['repartition_sexe']['femmes']); ?></div>
                <div class="stat-label">Total couvert</div>
            </div>
        </div>
        
        <div class="user-info" style="margin-top: 20px;">
            <div class="info-row">
                <span class="info-label">Répartition par sexe :</span>
                <span class="info-value">
                    Hommes: <?php echo e($statistiques['repartition_sexe']['hommes']); ?>, 
                    Femmes: <?php echo e($statistiques['repartition_sexe']['femmes']); ?>

                </span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Répartition par âge :</span>
                <span class="info-value">
                    18-25 ans: <?php echo e($statistiques['repartition_age']['18-25']); ?>, 
                    26-35 ans: <?php echo e($statistiques['repartition_age']['26-35']); ?>, 
                    36-45 ans: <?php echo e($statistiques['repartition_age']['36-45']); ?>, 
                    46-55 ans: <?php echo e($statistiques['repartition_age']['46-55']); ?>, 
                    55+ ans: <?php echo e($statistiques['repartition_age']['55+']); ?>

                </span>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php
        $employes = $demande->assures ?? collect();
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employes && $employes->count() > 0): ?>
    <div class="section employes-section">
        <div class="section-title">Liste des employés</div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $employes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="employe-item">
            <div class="employe-header">
                <div class="employe-name"><?php echo e($employe->nom); ?> <?php echo e($employe->prenoms); ?></div>
                <div class="employe-status"><?php echo e($employe->est_principal ? 'Principal' : 'Bénéficiaire'); ?></div>
            </div>
            
            <div class="info-row">
                <span class="info-label">Date de naissance :</span>
                <span class="info-value"><?php echo e($employe->date_naissance ? \Carbon\Carbon::parse($employe->date_naissance)->format('d/m/Y') : 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Sexe :</span>
                <span class="info-value"><?php echo e(ucfirst($employe->sexe->value)); ?></span>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employe->contact): ?>
            <div class="info-row">
                <span class="info-label">Contact :</span>
                <span class="info-value"><?php echo e($employe->contact); ?></span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employe->email): ?>
            <div class="info-row">
                <span class="info-label">Email :</span>
                <span class="info-value"><?php echo e($employe->email); ?></span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employe->profession): ?>
            <div class="info-row">
                <span class="info-label">Profession :</span>
                <span class="info-value"><?php echo e($employe->profession); ?></span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employe->reponsesQuestionnaire && $employe->reponsesQuestionnaire->count() > 0): ?>
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                <div style="font-weight: bold; margin-bottom: 5px; color: #2c5aa0;">Réponses au questionnaire :</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $employe->reponsesQuestionnaire; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reponse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="margin-bottom: 5px; padding-left: 10px;">
                    <div style="font-weight: bold; font-size: 12px;"><?php echo e($reponse->question->libelle ?? 'Question non trouvée'); ?></div>
                    <div style="font-size: 12px; color: #6c757d;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reponse->reponse_text): ?>
                            <?php echo e($reponse->reponse_text); ?>

                        <?php elseif($reponse->reponse_bool !== null): ?>
                            <?php echo e($reponse->reponse_bool ? 'Oui' : 'Non'); ?>

                        <?php elseif($reponse->reponse_number !== null): ?>
                            <?php echo e($reponse->reponse_number); ?>

                        <?php elseif($reponse->reponse_date): ?>
                            <?php echo e(\Carbon\Carbon::parse($reponse->reponse_date)->format('d/m/Y')); ?>

                        <?php elseif($reponse->reponse_fichier): ?>
                            <strong>Fichier joint :</strong> 
                            <a href="<?php echo e($baseUrl); ?>/storage/<?php echo e($reponse->reponse_fichier); ?>" class="file-link" target="_blank">
                                <?php echo e(\App\Helpers\ImageUploadHelper::getFileName($reponse->reponse_fichier)); ?>

                            </a>
                        <?php else: ?>
                            Aucune réponse
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php
                $beneficiaires = $employe->beneficiaires;
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($beneficiaires && $beneficiaires->count() > 0): ?>
            <div class="beneficiaires-list">
                <div style="font-weight: bold; margin-bottom: 5px; color: #2c5aa0;">Bénéficiaires associés :</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $beneficiaires; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $beneficiaire): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="beneficiaire-item">
                    <div style="font-weight: bold;"><?php echo e($beneficiaire->nom); ?> <?php echo e($beneficiaire->prenoms); ?></div>
                    <div style="font-size: 11px; color: #6c757d;">
                        <?php echo e($beneficiaire->date_naissance ? \Carbon\Carbon::parse($beneficiaire->date_naissance)->format('d/m/Y') : 'N/A'); ?> - 
                        <?php echo e(ucfirst($beneficiaire->sexe->value)); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($beneficiaire->lien_parente): ?>
                            - <?php echo e(ucfirst($beneficiaire->lien_parente->value)); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="footer">
        <p>Ce document a été généré automatiquement par le système SUNU Santé.</p>
        <p>Pour toute question, contactez-nous à support@sunusante.com</p>
        <p>© <?php echo e(date('Y')); ?> SUNU Santé - Tous droits réservés</p>
    </div>
</body>
</html> <?php /**PATH G:\projects\amp\amp_backend\resources\views\pdf\demande-adhesion-entreprise.blade.php ENDPATH**/ ?>