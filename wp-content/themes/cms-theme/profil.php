<?php
session_start();
require_once('connexion.php');

// Vérifier si connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidat') {
    header('Location: connexion-traitement.php');
    exit;
}

// Récupérer les données du candidat
$stmt = $pdo->prepare("SELECT * FROM candidats WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$candidat = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les compétences
$stmt2 = $pdo->prepare("SELECT * FROM competences WHERE candidat_id = ?");
$stmt2->execute([$_SESSION['user_id']]);
$competences = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les candidatures
$stmt3 = $pdo->prepare("
    SELECT c.*, o.titre, o.entreprise, o.ville as offre_ville
    FROM candidatures c
    JOIN offres o ON c.offre_id = o.id
    WHERE c.candidat_id = ?
    ORDER BY c.date_candidature DESC
");
$stmt3->execute([$_SESSION['user_id']]);
$candidatures = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Calcul completion profil
$total = 8;
$rempli = 0;
if (!empty($candidat['prenom']))        $rempli++;
if (!empty($candidat['email']))         $rempli++;
if (!empty($candidat['ville']))         $rempli++;
if (!empty($candidat['niveau_etudes'])) $rempli++;
if (!empty($candidat['domaine']))       $rempli++;
if (!empty($candidat['presentation']))  $rempli++;
if (!empty($candidat['photo']))         $rempli++;
if (!empty($candidat['cv_pdf']))        $rempli++;
$completion = round(($rempli / $total) * 100);

// Initiales avatar
$initiales = strtoupper(substr($candidat['prenom'], 0, 1) . substr($candidat['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PIIP - Mon Profil</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f9f7ff;color:#1a0533}

  /* NAV */
  nav{background:#1a0533;padding:0 30px;height:65px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:999}
  .nav-logo{color:#fff;font-size:24px;font-weight:900;letter-spacing:4px;text-decoration:none}
  .nav-links{display:flex;gap:6px;align-items:center}
  .nav-links a{color:#c4b5fd;font-size:13px;padding:7px 12px;border-radius:6px;text-decoration:none}
  .nav-links a:hover{background:rgba(168,85,247,.15);color:#fff}
  .nav-user{background:rgba(168,85,247,.2);color:#e9d5ff;padding:7px 14px;border-radius:6px;font-size:13px;font-weight:600}
  .nav-logout{background:#dc2626;color:#fff !important;padding:7px 14px;border-radius:6px;font-size:13px;font-weight:600}

  /* HEADER */
  .page-header{background:linear-gradient(135deg,#1a0533,#4a1480);color:#fff;padding:40px 30px;text-align:center}
  .page-header h1{font-size:28px;font-weight:800;margin:10px 0 6px}
  .page-header p{color:#c4b5fd;font-size:13px}
  .badge-top{display:inline-block;background:#a855f7;color:#fff;font-size:11px;font-weight:700;letter-spacing:2px;padding:4px 14px;border-radius:20px;text-transform:uppercase;margin-bottom:12px}

  /* LAYOUT */
  .layout{max-width:1100px;margin:28px auto;padding:0 20px 60px;display:grid;grid-template-columns:290px 1fr;gap:22px}

  /* CARDS */
  .card{background:#fff;border:1px solid #e9d5ff;border-radius:14px;padding:22px;margin-bottom:18px}
  .card h3{font-size:13px;font-weight:700;color:#1a0533;margin-bottom:14px;border-bottom:1px solid #f3e8ff;padding-bottom:8px;display:flex;align-items:center;gap:6px}

  /* AVATAR */
  .avatar{width:86px;height:86px;border-radius:50%;background:#f5f3ff;border:3px solid #e9d5ff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;color:#6b21a8;margin:0 auto 12px;overflow:hidden}
  .avatar img{width:100%;height:100%;object-fit:cover}
  .profil-name{text-align:center;font-size:19px;font-weight:800}
  .profil-role{text-align:center;font-size:12px;color:#7c6d8e;margin:4px 0 12px}
  .profil-info{font-size:12px;color:#6b7280;display:flex;align-items:center;gap:6px;margin-bottom:6px}

  /* SCORE */
  .score-box{background:#f5f3ff;border-radius:10px;padding:18px;text-align:center;margin-top:12px}
  .score-circle{width:82px;height:82px;border-radius:50%;background:#6b21a8;color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;margin:0 auto 10px}
  .score-circle .sn{font-size:28px;font-weight:900;line-height:1}
  .score-circle .sm{font-size:10px;opacity:.8}
  .score-label{font-size:12px;color:#7c6d8e}

  /* COMPLETION */
  .comp-label{display:flex;justify-content:space-between;font-size:11px;color:#7c6d8e;margin-bottom:5px}
  .comp-bar{background:#e9d5ff;border-radius:20px;height:7px}
  .comp-fill{height:7px;border-radius:20px;background:#6b21a8}

  /* BADGES */
  .badge-list{display:flex;flex-wrap:wrap;gap:7px}
  .skill-badge{background:#f5f3ff;color:#6b21a8;border:1px solid #e9d5ff;border-radius:20px;padding:4px 12px;font-size:11px;font-weight:600}
  .skill-badge.ok{background:#6b21a8;color:#fff;border-color:#6b21a8}

  /* PROGRESS */
  .prog-label{display:flex;justify-content:space-between;font-size:12px;color:#5c4a72;margin-bottom:4px}
  .prog-track{background:#f3e8ff;border-radius:20px;height:7px;margin-bottom:12px}
  .prog-fill{height:7px;border-radius:20px;background:#6b21a8}

  /* TABS */
  .tabs{display:flex;gap:4px;margin-bottom:18px;border-bottom:2px solid #e9d5ff}
  .tab{padding:9px 16px;font-size:13px;font-weight:600;color:#7c6d8e;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px}
  .tab.active{color:#6b21a8;border-bottom-color:#6b21a8}

  /* INFO LIGNE */
  .info-ligne{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3e8ff;font-size:13px}
  .info-ligne:last-child{border-bottom:none}
  .info-ligne span:first-child{color:#7c6d8e;font-weight:500}
  .info-ligne span:last-child{color:#1a0533;font-weight:600}

  /* CANDIDATURES */
  .cand-item{border-left:3px solid #6b21a8;padding:10px 14px;margin-bottom:10px;background:#faf5ff;border-radius:0 8px 8px 0}
  .cand-title{font-size:13px;font-weight:700;color:#1a0533}
  .cand-company{font-size:12px;color:#6b21a8}
  .cand-date{font-size:11px;color:#9ca3af;margin-top:2px}
  .status-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase}
  .status-badge.attente{background:#fef3c7;color:#92400e}
  .status-badge.accepte{background:#d1fae5;color:#065f46}
  .status-badge.refuse{background:#fee2e2;color:#991b1b}

  /* BOUTONS */
  .btn-edit{display:inline-block;background:#6b21a8;color:#fff;padding:9px 20px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:none;cursor:pointer}
  .btn-outline{display:inline-block;background:transparent;color:#6b21a8;padding:9px 20px;border-radius:8px;font-size:12px;font-weight:600;border:2px solid #6b21a8;text-decoration:none;margin-left:8px}

  /* CV BOX */
  .cv-box{border:2px dashed #ddd6fe;border-radius:10px;padding:20px;text-align:center;background:#faf5ff}
  .cv-box p{font-size:12px;color:#7c6d8e;margin-top:6px}

  /* EMPTY STATE */
  .empty-state{text-align:center;padding:30px;color:#9ca3af;font-size:13px}
  .empty-state div{font-size:32px;margin-bottom:8px}

  footer{background:#0f0220;color:#9ca3af;text-align:center;padding:24px;font-size:12px;margin-top:40px}
  footer strong{color:#a855f7}

  @media(max-width:768px){.layout{grid-template-columns:1fr}.nav-links{display:none}}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="../../" class="nav-logo">PIIP</a>
  <div class="nav-links">
    <a href="../../">Accueil</a>
    <a href="offres.php">Offres</a>
    <a href="career.php">Career Booster</a>
    <span class="nav-user">👤 <?php echo htmlspecialchars($_SESSION['user_nom']); ?></span>
    <a href="deconnexion.php" class="nav-logout">Déconnexion</a>
  </div>
</nav>

<!-- HEADER -->
<div class="page-header">
  <div class="badge-top">Candidat</div>
  <h1>Mon Profil</h1>
  <p>Gérez votre profil et suivez votre score d'employabilité</p>
</div>

<!-- LAYOUT -->
<div class="layout">

  <!-- SIDEBAR -->
  <div>

    <!-- Profil Card -->
    <div class="card">
      <div class="avatar">
        <?php if (!empty($candidat['photo'])): ?>
          <img src="../../../uploads/photos/<?php echo htmlspecialchars($candidat['photo']); ?>" alt="Photo">
        <?php else: ?>
          <?php echo $initiales; ?>
        <?php endif; ?>
      </div>
      <div class="profil-name"><?php echo htmlspecialchars($candidat['prenom'] . ' ' . $candidat['nom']); ?></div>
      <div class="profil-role"><?php echo htmlspecialchars($candidat['domaine'] ?? 'Domaine non renseigné'); ?></div>
      <div class="profil-info">📍 <?php echo htmlspecialchars($candidat['ville'] ?? 'Ville non renseignée'); ?></div>
      <div class="profil-info">🎓 <?php echo htmlspecialchars($candidat['niveau_etudes'] ?? 'Non renseigné'); ?></div>
      <div class="profil-info">📅 <?php echo htmlspecialchars($candidat['disponibilite'] ?? 'Non renseigné'); ?></div>
      <div class="profil-info">💼 <?php echo htmlspecialchars($candidat['type_poste'] ?? 'Non renseigné'); ?></div>
      <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
        <a href="modifier-profil.php" class="btn-edit">✏️ Modifier</a>
        <?php if (!empty($candidat['cv_pdf'])): ?>
          <a href="../../../uploads/cvs/<?php echo htmlspecialchars($candidat['cv_pdf']); ?>" class="btn-outline" target="_blank">📄 CV</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Score -->
    <div class="card">
      <h3>🎯 Score d'Employabilité</h3>
      <div class="score-box">
        <div class="score-circle">
          <div class="sn"><?php echo $candidat['score']; ?></div>
          <div class="sm">/100</div>
        </div>
        <?php
          $score = $candidat['score'];
          if ($score >= 80) $niveau_score = '<strong style="color:#6b21a8">Excellent</strong>';
          elseif ($score >= 60) $niveau_score = '<strong style="color:#9333ea">Bon</strong>';
          elseif ($score >= 40) $niveau_score = '<strong style="color:#f59e0b">Moyen</strong>';
          else $niveau_score = '<strong style="color:#dc2626">Faible</strong>';
        ?>
        <div class="score-label">Score — <?php echo $niveau_score; ?></div>
      </div>
      <div style="margin-top:14px">
        <div class="comp-label">
          <span>Profil complété</span>
          <span><?php echo $completion; ?>%</span>
        </div>
        <div class="comp-bar">
          <div class="comp-fill" style="width:<?php echo $completion; ?>%"></div>
        </div>
        <div style="font-size:11px;color:#9ca3af;margin-top:4px">
          Complétez votre profil pour améliorer votre score
        </div>
      </div>
    </div>

    <!-- Compétences -->
    <div class="card">
      <h3>🏅 Compétences</h3>
      <?php if (!empty($competences)): ?>
        <div class="badge-list">
          <?php foreach($competences as $comp): ?>
            <span class="skill-badge <?php echo $comp['valide'] ? 'ok' : ''; ?>">
              <?php echo htmlspecialchars($comp['competence']); ?>
              <?php echo $comp['valide'] ? ' ✓' : ''; ?>
            </span>
          <?php endforeach; ?>
        </div>
        <div style="font-size:11px;color:#9ca3af;margin-top:10px">✓ = Validé par test PIIP</div>
      <?php else: ?>
        <div class="empty-state">
          <div>🏅</div>
          Aucune compétence ajoutée
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- MAIN -->
  <div>

    <!-- Tabs -->
    <div class="tabs">
      <div class="tab active">Informations</div>
      <div class="tab">Candidatures</div>
      <div class="tab">CV</div>
    </div>

    <!-- Informations -->
    <div class="card">
      <h3>👤 Informations Personnelles</h3>
      <div class="info-ligne">
        <span>Prénom</span>
        <span><?php echo htmlspecialchars($candidat['prenom']); ?></span>
      </div>
      <div class="info-ligne">
        <span>Nom</span>
        <span><?php echo htmlspecialchars($candidat['nom']); ?></span>
      </div>
      <div class="info-ligne">
        <span>Email</span>
        <span><?php echo htmlspecialchars($candidat['email']); ?></span>
      </div>
      <div class="info-ligne">
        <span>Téléphone</span>
        <span><?php echo htmlspecialchars($candidat['telephone'] ?? 'Non renseigné'); ?></span>
      </div>
      <div class="info-ligne">
        <span>Ville</span>
        <span><?php echo htmlspecialchars($candidat['ville'] ?? 'Non renseignée'); ?></span>
      </div>
      <div class="info-ligne">
        <span>Niveau d'études</span>
        <span><?php echo htmlspecialchars($candidat['niveau_etudes'] ?? 'Non renseigné'); ?></span>
      </div>
      <div class="info-ligne">
        <span>Domaine</span>
        <span><?php echo htmlspecialchars($candidat['domaine'] ?? 'Non renseigné'); ?></span>
      </div>
      <div class="info-ligne">
        <span>École</span>
        <span><?php echo htmlspecialchars($candidat['ecole'] ?? 'Non renseignée'); ?></span>
      </div>
      <div class="info-ligne">
        <span>Type de poste</span>
        <span><?php echo htmlspecialchars($candidat['type_poste'] ?? 'Non renseigné'); ?></span>
      </div>
      <div class="info-ligne">
        <span>Disponibilité</span>
        <span><?php echo htmlspecialchars($candidat['disponibilite'] ?? 'Non renseignée'); ?></span>
      </div>
      <div class="info-ligne">
        <span>Membre depuis</span>
        <span><?php echo date('d/m/Y', strtotime($candidat['date_inscription'])); ?></span>
      </div>
    </div>

    <!-- Présentation -->
    <?php if (!empty($candidat['presentation'])): ?>
    <div class="card">
      <h3>📝 Présentation</h3>
      <p style="font-size:14px;color:#5c4a72;line-height:1.7">
        <?php echo nl2br(htmlspecialchars($candidat['presentation'])); ?>
      </p>
    </div>
    <?php endif; ?>

    <!-- Candidatures -->
    <div class="card">
      <h3>📋 Mes Candidatures (<?php echo count($candidatures); ?>)</h3>
      <?php if (!empty($candidatures)): ?>
        <?php foreach($candidatures as $cand): ?>
          <div class="cand-item">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
              <div>
                <div class="cand-title"><?php echo htmlspecialchars($cand['titre']); ?></div>
                <div class="cand-company"><?php echo htmlspecialchars($cand['entreprise']); ?> — <?php echo htmlspecialchars($cand['offre_ville']); ?></div>
                <div class="cand-date">📅 <?php echo date('d/m/Y', strtotime($cand['date_candidature'])); ?></div>
              </div>
              <?php
                $statut_class = 'attente';
                if ($cand['statut'] === 'accepté') $statut_class = 'accepte';
                if ($cand['statut'] === 'refusé') $statut_class = 'refuse';
              ?>
              <span class="status-badge <?php echo $statut_class; ?>">
                <?php echo htmlspecialchars($cand['statut']); ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <div>📋</div>
          Aucune candidature pour le moment
          <br><br>
          <a href="offres.php" class="btn-edit">Voir les offres →</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- CV -->
    <div class="card">
      <h3>📄 Mon CV</h3>
      <?php if (!empty($candidat['cv_pdf'])): ?>
        <div class="cv-box">
          <div style="font-size:36px">📄</div>
          <p><?php echo htmlspecialchars($candidat['cv_pdf']); ?></p>
          <div style="display:flex;gap:8px;justify-content:center;margin-top:12px;flex-wrap:wrap">
            <a href="../../../uploads/cvs/<?php echo htmlspecialchars($candidat['cv_pdf']); ?>" class="btn-edit" target="_blank">📥 Télécharger</a>
            <a href="modifier-profil.php" class="btn-outline">🔄 Remplacer</a>
          </div>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div>📄</div>
          Aucun CV uploadé
          <br><br>
          <a href="modifier-profil.php" class="btn-edit">Uploader mon CV →</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<footer>
  <p><strong>PIIP</strong> — Plateforme Intelligente d'Insertion Professionnelle | Projet CMS 2025-2026</p>
</footer>

</body>
</html>