<?php
require_once('connexion.php');

// Initialisation des variables
$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données
    $prenom       = trim($_POST['prenom'] ?? '');
    $nom          = trim($_POST['nom'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $telephone    = trim($_POST['telephone'] ?? '');
    $ville        = trim($_POST['ville'] ?? '');
    $niveau       = trim($_POST['niveau_etudes'] ?? '');
    $domaine      = trim($_POST['domaine'] ?? '');
    $ecole        = trim($_POST['ecole'] ?? '');
    $type_poste   = trim($_POST['type_poste'] ?? '');
    $disponibilite = trim($_POST['disponibilite'] ?? '');
    $presentation = trim($_POST['presentation'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
    $confirmation = trim($_POST['confirmation'] ?? '');

    // Validation
    if (empty($prenom))        $erreurs[] = "Le prénom est obligatoire.";
    if (empty($nom))           $erreurs[] = "Le nom est obligatoire.";
    if (empty($email))         $erreurs[] = "L'email est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
    if (empty($ville))         $erreurs[] = "La ville est obligatoire.";
    if (empty($niveau))        $erreurs[] = "Le niveau d'études est obligatoire.";
    if (empty($type_poste))    $erreurs[] = "Le type de poste est obligatoire.";
    if (empty($disponibilite)) $erreurs[] = "La disponibilité est obligatoire.";
    if (empty($mot_de_passe))  $erreurs[] = "Le mot de passe est obligatoire.";
    if (strlen($mot_de_passe) < 8) $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    if ($mot_de_passe !== $confirmation) $erreurs[] = "Les mots de passe ne correspondent pas.";

    // Vérifier si l'email existe déjà
    if (empty($erreurs)) {
        $stmt = $pdo->prepare("SELECT id FROM candidats WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreurs[] = "Cet email est déjà utilisé.";
        }
    }

    // Upload photo
    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $ext_photo = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $nom_photo = uniqid('photo_') . '.' . $ext_photo;
        $dossier_upload = '../../../uploads/photos/';
        if (!is_dir($dossier_upload)) mkdir($dossier_upload, 0777, true);
        move_uploaded_file($_FILES['photo']['tmp_name'], $dossier_upload . $nom_photo);
        $photo = $nom_photo;
    }

    // Upload CV
    $cv_pdf = '';
    if (!empty($_FILES['cv_pdf']['name'])) {
        $nom_cv = uniqid('cv_') . '.pdf';
        $dossier_cv = '../../../uploads/cvs/';
        if (!is_dir($dossier_cv)) mkdir($dossier_cv, 0777, true);
        move_uploaded_file($_FILES['cv_pdf']['tmp_name'], $dossier_cv . $nom_cv);
        $cv_pdf = $nom_cv;
    }

    // Enregistrement en base de données
    if (empty($erreurs)) {
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $sql = "INSERT INTO candidats 
                (prenom, nom, email, telephone, ville, niveau_etudes, domaine, ecole, type_poste, disponibilite, presentation, photo, cv_pdf, mot_de_passe)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $prenom, $nom, $email, $telephone, $ville,
            $niveau, $domaine, $ecole, $type_poste,
            $disponibilite, $presentation, $photo, $cv_pdf, $hash
        ]);
        $succes = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PIIP - Inscription</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f9f7ff;color:#1a0533}
  .page-header{background:linear-gradient(135deg,#1a0533,#4a1480);color:#fff;padding:45px 30px;text-align:center}
  .page-header h1{font-size:32px;font-weight:800;margin:10px 0 8px}
  .page-header p{color:#c4b5fd;font-size:14px}
  .badge-top{display:inline-block;background:#a855f7;color:#fff;font-size:11px;font-weight:700;letter-spacing:2px;padding:4px 14px;border-radius:20px;text-transform:uppercase;margin-bottom:14px}
  .container{max-width:700px;margin:36px auto;padding:0 20px 60px}
  .card{background:#fff;border:1px solid #e9d5ff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(107,33,168,.07)}
  .card-header{background:#6b21a8;padding:20px 28px}
  .card-header h2{font-size:17px;font-weight:700;color:#fff}
  .card-header p{font-size:12px;color:#ddd6fe;margin-top:2px}
  .card-body{padding:28px}
  .alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:16px;border-radius:8px;margin-bottom:20px;font-size:14px}
  .alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:16px;border-radius:8px;margin-bottom:20px;font-size:14px}
  .alert-error ul{margin-top:8px;padding-left:20px}
  .alert-error li{margin-bottom:4px;font-size:13px}
  .section-title{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#a855f7;border-bottom:1px solid #f3e8ff;padding-bottom:8px;margin:22px 0 16px}
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .form-group{margin-bottom:16px}
  .form-group label{display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px}
  .form-group label .req{color:#dc2626}
  .form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fafafa;font-family:inherit}
  .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#a855f7;background:#fff}
  .form-group textarea{resize:vertical;min-height:80px}
  .form-group .hint{font-size:11px;color:#9ca3af;margin-top:4px}
  .upload-box{border:2px dashed #ddd6fe;border-radius:10px;padding:18px;text-align:center;background:#faf5ff}
  .upload-box p{font-size:12px;color:#7c6d8e;margin-top:6px}
  .check-group{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px}
  .check-group input{width:15px;height:15px;margin-top:2px;accent-color:#6b21a8;flex-shrink:0}
  .check-group label{font-size:12px;color:#4b5563;line-height:1.5}
  .btn-submit{width:100%;background:#6b21a8;color:#fff;padding:14px;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:4px}
  .btn-submit:hover{background:#581c87}
  .login-link{text-align:center;margin-top:14px;font-size:12px;color:#6b7280}
  .login-link a{color:#6b21a8;font-weight:600;text-decoration:none}
  .success-box{text-align:center;padding:40px 20px}
  .success-icon{font-size:60px;margin-bottom:16px}
  .success-box h2{font-size:24px;font-weight:800;color:#1a0533;margin-bottom:10px}
  .success-box p{font-size:14px;color:#5c4a72;margin-bottom:24px}
  .btn-profil{display:inline-block;background:#6b21a8;color:#fff;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none}
  @media(max-width:600px){.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="page-header">
  <div class="badge-top">Candidat</div>
  <h1>Créer mon profil PIIP</h1>
  <p>Rejoignez la plateforme et découvrez votre score d'employabilité</p>
</div>

<div class="container">
  <div class="card">
    <div class="card-header">
      <h2>📋 Formulaire d'inscription</h2>
      <p>Tous les champs marqués * sont obligatoires</p>
    </div>
    <div class="card-body">

      <?php if ($succes): ?>
        <!-- SUCCES -->
        <div class="success-box">
          <div class="success-icon">🎉</div>
          <h2>Bienvenue sur PIIP !</h2>
          <p>Votre profil a été créé avec succès. Vous pouvez maintenant accéder à votre espace candidat.</p>
          <a href="profil.php" class="btn-profil">Voir mon profil →</a>
        </div>

      <?php else: ?>

        <!-- ERREURS -->
        <?php if (!empty($erreurs)): ?>
          <div class="alert-error">
            ⚠️ Veuillez corriger les erreurs suivantes :
            <ul>
              <?php foreach($erreurs as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- FORMULAIRE -->
        <form method="POST" enctype="multipart/form-data">

          <div class="section-title">Identité</div>
          <div class="form-row">
            <div class="form-group">
              <label>Prénom <span class="req">*</span></label>
              <input type="text" name="prenom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" placeholder="Ex : Mohamed">
            </div>
            <div class="form-group">
              <label>Nom <span class="req">*</span></label>
              <input type="text" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" placeholder="Ex : Diallo">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email <span class="req">*</span></label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="votre@email.com">
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <input type="tel" name="telephone" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" placeholder="+221 XX XXX XX XX">
            </div>
          </div>
          <div class="form-group">
            <label>Ville <span class="req">*</span></label>
            <input type="text" name="ville" value="<?php echo htmlspecialchars($_POST['ville'] ?? ''); ?>" placeholder="Ex : Dakar">
          </div>

          <div class="section-title">Formation</div>
          <div class="form-row">
            <div class="form-group">
              <label>Niveau d'études <span class="req">*</span></label>
              <select name="niveau_etudes">
                <option value="">— Sélectionner —</option>
                <option value="Bac" <?php echo (($_POST['niveau_etudes'] ?? '') == 'Bac') ? 'selected' : ''; ?>>Bac</option>
                <option value="Bac+2" <?php echo (($_POST['niveau_etudes'] ?? '') == 'Bac+2') ? 'selected' : ''; ?>>Bac+2 / BTS</option>
                <option value="Bac+3" <?php echo (($_POST['niveau_etudes'] ?? '') == 'Bac+3') ? 'selected' : ''; ?>>Bac+3 / Licence</option>
                <option value="Bac+4" <?php echo (($_POST['niveau_etudes'] ?? '') == 'Bac+4') ? 'selected' : ''; ?>>Bac+4 / Master 1</option>
                <option value="Bac+5" <?php echo (($_POST['niveau_etudes'] ?? '') == 'Bac+5') ? 'selected' : ''; ?>>Bac+5 / Master 2</option>
                <option value="Doctorat" <?php echo (($_POST['niveau_etudes'] ?? '') == 'Doctorat') ? 'selected' : ''; ?>>Doctorat</option>
              </select>
            </div>
            <div class="form-group">
              <label>Domaine d'études <span class="req">*</span></label>
              <select name="domaine">
                <option value="">— Sélectionner —</option>
                <option value="Informatique">Informatique / Développement</option>
                <option value="Réseaux">Réseaux & Télécommunications</option>
                <option value="Gestion">Gestion / Management</option>
                <option value="Marketing">Marketing / Communication</option>
                <option value="Finance">Finance / Comptabilité</option>
                <option value="Droit">Droit</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Établissement / École</label>
            <input type="text" name="ecole" value="<?php echo htmlspecialchars($_POST['ecole'] ?? ''); ?>" placeholder="Ex : Université Cheikh Anta Diop">
          </div>

          <div class="section-title">Profil professionnel</div>
          <div class="form-row">
            <div class="form-group">
              <label>Type de poste <span class="req">*</span></label>
              <select name="type_poste">
                <option value="">— Sélectionner —</option>
                <option value="CDI">CDI</option>
                <option value="CDD">CDD</option>
                <option value="Stage">Stage</option>
                <option value="Alternance">Alternance</option>
                <option value="Freelance">Freelance</option>
              </select>
            </div>
            <div class="form-group">
              <label>Disponibilité <span class="req">*</span></label>
              <select name="disponibilite">
                <option value="">— Sélectionner —</option>
                <option value="Immédiate">Immédiate</option>
                <option value="1 mois">Dans 1 mois</option>
                <option value="3 mois">Dans 3 mois</option>
                <option value="En poste">En cours d'emploi</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Présentation rapide</label>
            <textarea name="presentation" placeholder="Décrivez-vous en quelques lignes..."><?php echo htmlspecialchars($_POST['presentation'] ?? ''); ?></textarea>
            <div class="hint">Max 300 caractères</div>
          </div>

          <div class="section-title">CV & Photo</div>
          <div class="form-row">
            <div class="form-group">
              <label>Photo de profil</label>
              <div class="upload-box">
                <div style="font-size:28px">📷</div>
                <input type="file" name="photo" accept="image/*" style="margin-top:8px;width:100%">
                <p>JPG, PNG — Max 2 MB</p>
              </div>
            </div>
            <div class="form-group">
              <label>CV (PDF)</label>
              <div class="upload-box">
                <div style="font-size:28px">📄</div>
                <input type="file" name="cv_pdf" accept=".pdf" style="margin-top:8px;width:100%">
                <p>PDF uniquement — Max 5 MB</p>
              </div>
            </div>
          </div>

          <div class="section-title">Sécurité du compte</div>
          <div class="form-row">
            <div class="form-group">
              <label>Mot de passe <span class="req">*</span></label>
              <input type="password" name="mot_de_passe" placeholder="••••••••">
              <div class="hint">Min 8 caractères</div>
            </div>
            <div class="form-group">
              <label>Confirmer le mot de passe <span class="req">*</span></label>
              <input type="password" name="confirmation" placeholder="••••••••">
            </div>
          </div>

          <div class="check-group">
            <input type="checkbox" name="cgu" id="cgu" required>
            <label for="cgu">J'accepte les conditions d'utilisation de PIIP <span class="req">*</span></label>
          </div>
          <div class="check-group">
            <input type="checkbox" name="notif" id="notif">
            <label for="notif">Je souhaite recevoir des notifications sur les nouvelles offres</label>
          </div>

          <button type="submit" class="btn-submit">Créer mon profil PIIP →</button>
          <div class="login-link">Déjà inscrit ? <a href="connexion.php">Se connecter ici</a></div>

        </form>
      <?php endif; ?>

    </div>
  </div>
</div>

</body>
</html>