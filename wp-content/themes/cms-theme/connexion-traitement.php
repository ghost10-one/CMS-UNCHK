<?php
session_start();
require_once('connexion.php');

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email        = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

    // Validation
    if (empty($email))        $erreurs[] = "L'email est obligatoire.";
    if (empty($mot_de_passe)) $erreurs[] = "Le mot de passe est obligatoire.";

    if (empty($erreurs)) {

        // Vérifier si c'est un candidat
        $stmt = $pdo->prepare("SELECT * FROM candidats WHERE email = ?");
        $stmt->execute([$email]);
        $candidat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($candidat && password_verify($mot_de_passe, $candidat['mot_de_passe'])) {
            // Connexion réussie — candidat
            $_SESSION['user_id']   = $candidat['id'];
            $_SESSION['user_nom']  = $candidat['prenom'] . ' ' . $candidat['nom'];
            $_SESSION['user_role'] = 'candidat';
            header('Location: profil.php');
            exit;
        }

        // Vérifier si c'est un recruteur
        $stmt2 = $pdo->prepare("SELECT * FROM recruteurs WHERE email = ?");
        $stmt2->execute([$email]);
        $recruteur = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($recruteur && password_verify($mot_de_passe, $recruteur['mot_de_passe'])) {
            // Connexion réussie — recruteur
            $_SESSION['user_id']   = $recruteur['id'];
            $_SESSION['user_nom']  = $recruteur['prenom'] . ' ' . $recruteur['nom'];
            $_SESSION['user_role'] = 'recruteur';
            header('Location: dashboard.php');
            exit;
        }

        // Identifiants incorrects
        $erreurs[] = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PIIP - Connexion</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f9f7ff;color:#1a0533;min-height:100vh;display:flex;flex-direction:column}
  .page-header{background:linear-gradient(135deg,#1a0533,#4a1480);color:#fff;padding:45px 30px;text-align:center}
  .page-header h1{font-size:32px;font-weight:800;margin:10px 0 8px}
  .page-header p{color:#c4b5fd;font-size:14px}
  .badge-top{display:inline-block;background:#a855f7;color:#fff;font-size:11px;font-weight:700;letter-spacing:2px;padding:4px 14px;border-radius:20px;text-transform:uppercase;margin-bottom:14px}
  .container{max-width:480px;margin:40px auto;padding:0 20px 60px;flex:1}
  .card{background:#fff;border:1px solid #e9d5ff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(107,33,168,.07)}
  .card-header{background:#6b21a8;padding:20px 28px;display:flex;align-items:center;gap:14px}
  .card-header-icon{width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px}
  .card-header h2{font-size:17px;font-weight:700;color:#fff}
  .card-header p{font-size:12px;color:#ddd6fe;margin-top:2px}
  .card-body{padding:32px}
  .alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:14px;border-radius:8px;margin-bottom:20px;font-size:13px;display:flex;align-items:center;gap:8px}
  .alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:14px;border-radius:8px;margin-bottom:20px;font-size:13px}
  .form-group{margin-bottom:18px}
  .form-group label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
  .form-group input{width:100%;padding:12px 14px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;color:#374151;background:#fafafa;font-family:inherit;transition:border-color .2s}
  .form-group input:focus{outline:none;border-color:#a855f7;background:#fff;box-shadow:0 0 0 3px rgba(168,85,247,.10)}
  .forgot-link{text-align:right;margin-top:-12px;margin-bottom:16px}
  .forgot-link a{font-size:12px;color:#6b21a8;text-decoration:none}
  .btn-submit{width:100%;background:#6b21a8;color:#fff;padding:14px;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .2s}
  .btn-submit:hover{background:#581c87}
  .divider{text-align:center;margin:20px 0;position:relative}
  .divider::before{content:'';position:absolute;top:50%;left:0;right:0;height:1px;background:#e9d5ff}
  .divider span{background:#fff;padding:0 14px;font-size:12px;color:#9ca3af;position:relative}
  .register-link{text-align:center;font-size:13px;color:#6b7280}
  .register-link a{color:#6b21a8;font-weight:600;text-decoration:none}
  .role-tabs{display:flex;gap:8px;margin-bottom:24px}
  .role-tab{flex:1;padding:10px;border:1.5px solid #e9d5ff;border-radius:8px;text-align:center;cursor:pointer;font-size:13px;font-weight:600;color:#7c6d8e;transition:all .2s}
  .role-tab.active{background:#6b21a8;color:#fff;border-color:#6b21a8}
  footer{background:#0f0220;color:#9ca3af;text-align:center;padding:24px;font-size:12px}
  footer strong{color:#a855f7}
</style>
</head>
<body>

<div class="page-header">
  <div class="badge-top">Connexion</div>
  <h1>Bienvenue sur PIIP</h1>
  <p>Connectez-vous pour accéder à votre espace personnel</p>
</div>

<div class="container">

  <?php if (isset($_GET['inscription']) && $_GET['inscription'] == 'succes'): ?>
    <div class="alert-success">
      ✅ Inscription réussie ! Connectez-vous maintenant.
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <div class="card-header-icon">🔐</div>
      <div>
        <h2>Se connecter</h2>
        <p>Entrez vos identifiants PIIP</p>
      </div>
    </div>
    <div class="card-body">

      <!-- TABS ROLE -->
      <div class="role-tabs">
        <div class="role-tab active">🎓 Candidat</div>
        <div class="role-tab">🏢 Recruteur</div>
      </div>

      <!-- ERREURS -->
      <?php if (!empty($erreurs)): ?>
        <div class="alert-error">
          ⚠️ <?php echo htmlspecialchars($erreurs[0]); ?>
        </div>
      <?php endif; ?>

      <!-- FORMULAIRE -->
      <form method="POST">
        <div class="form-group">
          <label>Adresse email</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="votre@email.com" autofocus>
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" name="mot_de_passe" placeholder="••••••••">
        </div>
        <div class="forgot-link">
          <a href="#">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn-submit">Se connecter →</button>
      </form>

      <div class="divider"><span>ou</span></div>

      <div class="register-link">
        Pas encore de compte ? <a href="inscription-traitement.php">Créer mon profil</a>
      </div>

    </div>
  </div>
</div>

<footer>
  <p><strong>PIIP</strong> — Plateforme Intelligente d'Insertion Professionnelle | Projet CMS 2025-2026</p>
</footer>

</body>
</html>