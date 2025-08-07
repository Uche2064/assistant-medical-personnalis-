<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'Adhésion - Personne Physique - SUNU Santé</title>
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
            position: relative;
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
        .photo-container {
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
            height: 120px;
            border: 2px solid #2c5aa0;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f8f9fa;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef;
            color: #6c757d;
            font-size: 12px;
            text-align: center;
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
        .reponses-section {
            margin-top: 20px;
        }
        .reponse-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .question {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .reponse {
            color: #6c757d;
        }
        .beneficiaires-section {
            margin-top: 20px;
        }
        .beneficiaire-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SUNU SANTÉ</div>
        <div class="title">Demande d'Adhésion - Personne Physique</div>
        <div style="font-size: 12px; color: #666;">Document généré le <?php echo e(now()->format('d/m/Y à H:i')); ?></div>
        
        <!-- Photo du demandeur en haut à droite -->
        <div class="photo-container">
            <?php if($demande->user->assure && $demande->user->photo): ?>
                <img src="<?php echo e($baseUrl); ?>/storage/<?php echo e($demande->user->photo); ?>" 
                     alt="Photo du demandeur">
            <?php else: ?>
                <div class="photo-placeholder">
                    Photo<br>non disponible
                </div>
            <?php endif; ?>
        </div>
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
        <?php if($demande->validePar): ?>
        <div class="info-row">
            <span class="info-label">Validée par :</span>
            <span class="info-value"><?php echo e($demande->validePar->nom ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de validation :</span>
            <span class="info-value"><?php echo e($demande->valider_a ? \Carbon\Carbon::parse($demande->valider_a)->format('d/m/Y à H:i') : 'N/A'); ?></span>
        </div>
        <?php endif; ?>
        <?php if($demande->motif_rejet): ?>
        <div class="info-row">
            <span class="info-label">Motif de rejet :</span>
            <span class="info-value"><?php echo e($demande->motif_rejet); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">Informations du demandeur</div>
        <div class="user-info">
            <div class="info-row">
                <span class="info-label">Nom complet :</span>
                <span class="info-value"><?php echo e($demande->user->assure->nom ?? 'N/A'); ?> <?php echo e($demande->user->assure->prenoms ?? ''); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email :</span>
                <span class="info-value"><?php echo e($demande->user->email); ?></span>
            </div>
            <?php if($demande->user->contact): ?>
            <div class="info-row">
                <span class="info-label">Téléphone :</span>
                <span class="info-value"><?php echo e($demande->user->contact); ?></span>
            </div>
            <?php endif; ?>
            <?php if($demande->user->assure && $demande->user->assure->profession): ?>
            <div class="info-row">
                <span class="info-label">Profession :</span>
                <span class="info-value"><?php echo e($demande->user->assure->profession); ?></span>
            </div>
            <?php endif; ?>
            <?php if($demande->user->assure && $demande->user->assure->date_naissance): ?>
            <div class="info-row">
                <span class="info-label">Date de naissance :</span>
                <span class="info-value"><?php echo e(\Carbon\Carbon::parse($demande->user->assure->date_naissance)->format('d/m/Y')); ?></span>
            </div>
            <?php endif; ?>
            <?php if($demande->user->assure && $demande->user->assure->sexe): ?>
            <div class="info-row">
                <span class="info-label">Sexe :</span>
                <span class="info-value"><?php echo e(ucfirst($demande->user->assure->sexe->value)); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($demande->reponsesQuestionnaire && $demande->reponsesQuestionnaire->count() > 0): ?>
    <div class="section reponses-section">
        <div class="section-title">Réponses au questionnaire médical</div>
        <?php $__currentLoopData = $demande->reponsesQuestionnaire; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reponse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="reponse-item">
            <div class="question"><?php echo e($reponse->question->libelle ?? 'Question non trouvée'); ?></div>
            <div class="reponse">
                <?php if($reponse->reponse_text): ?>
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
                    <br><small style="color: #6c757d;">Type: <?php echo e(\App\Helpers\ImageUploadHelper::getFileExtension($reponse->reponse_fichier)); ?></small>
                <?php else: ?>
                    Aucune réponse
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php
        $beneficiaires = $demande->user->assure->beneficiaires;
    ?>
    <?php if($beneficiaires && $beneficiaires->count() > 0): ?>
    <div class="section beneficiaires-section">
        <div class="section-title">Bénéficiaires associés</div>
        <?php $__currentLoopData = $beneficiaires; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assure): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="beneficiaire-item">
            <div class="info-row">
                <span class="info-label">Nom complet :</span>
                <span class="info-value"><?php echo e($assure->nom); ?> <?php echo e($assure->prenoms); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date de naissance :</span>
                <span class="info-value"><?php echo e($assure->date_naissance ? \Carbon\Carbon::parse($assure->date_naissance)->format('d/m/Y') : 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Sexe :</span>
                <span class="info-value"><?php echo e(ucfirst($assure->sexe->value)); ?></span>
            </div>
            <?php if($assure->contact): ?>
            <div class="info-row">
                <span class="info-label">Contact :</span>
                <span class="info-value"><?php echo e($assure->contact); ?></span>
            </div>
            <?php endif; ?>
            <?php if($assure->email): ?>
            <div class="info-row">
                <span class="info-label">Email :</span>
                <span class="info-value"><?php echo e($assure->email); ?></span>
            </div>
            <?php endif; ?>
            <?php if($assure->profession): ?>
            <div class="info-row">
                <span class="info-label">Profession :</span>
                <span class="info-value"><?php echo e($assure->profession); ?></span>
            </div>
            <?php endif; ?>
            <?php if($assure->lien_parente): ?>
            <div class="info-row">
                <span class="info-label">Lien de parenté :</span>
                <span class="info-value"><?php echo e(ucfirst($assure->lien_parente->value)); ?></span>
            </div>
            <?php endif; ?>

            <?php if($assure->reponsesQuestionnaire && $assure->reponsesQuestionnaire->count() > 0): ?>
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                <div style="font-weight: bold; margin-bottom: 5px; color: #2c5aa0;">Réponses au questionnaire :</div>
                <?php $__currentLoopData = $assure->reponsesQuestionnaire; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reponse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="margin-bottom: 5px; padding-left: 10px;">
                    <div style="font-weight: bold; font-size: 12px;"><?php echo e($reponse->question->libelle ?? 'Question non trouvée'); ?></div>
                    <div style="font-size: 12px; color: #6c757d;">
                        <?php if($reponse->reponse_text): ?>
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
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if(isset($statistiques)): ?>
    <div class="section">
        <div class="section-title">Statistiques des bénéficiaires</div>
        <div class="user-info">
            <div class="info-row">
                <span class="info-label">Total de personnes :</span>
                <span class="info-value"><?php echo e($statistiques['total_personnes']); ?></span>
            </div>
            <?php if($statistiques['total_beneficiaires'] > 0): ?>
            <div class="info-row">
                <span class="info-label">Total de bénéficiaires :</span>
                <span class="info-value"><?php echo e($statistiques['total_beneficiaires']); ?></span>
            </div>
            <?php endif; ?>
            
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
    <?php endif; ?>

    <div class="footer">
        <p>Ce document a été généré automatiquement par le système SUNU Santé.</p>
        <p>Pour toute question, contactez-nous à support@sunusante.com</p>
        <p>© <?php echo e(date('Y')); ?> SUNU Santé - Tous droits réservés</p>
    </div>
</body>
</html> <?php /**PATH D:\projects\amp\amp_backend\resources\views/pdf/demande-adhesion-physique.blade.php ENDPATH**/ ?>