<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<nav style="background:#1a0533;padding:0 30px;height:65px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:999">
  <a href="<?php echo home_url(); ?>" style="color:#fff;font-size:26px;font-weight:900;letter-spacing:4px;text-decoration:none">PIIP</a>
  <div style="display:flex;gap:6px;flex-wrap:wrap">
    <a href="<?php echo home_url(); ?>" style="color:#c4b5fd;font-size:14px;padding:8px 14px;border-radius:6px;text-decoration:none">Accueil</a>
    <a href="<?php echo home_url('/offres'); ?>" style="color:#c4b5fd;font-size:14px;padding:8px 14px;border-radius:6px;text-decoration:none">Offres</a>
    <a href="<?php echo home_url('/mon-profil'); ?>" style="color:#c4b5fd;font-size:14px;padding:8px 14px;border-radius:6px;text-decoration:none">Mon Profil</a>
    <a href="<?php echo home_url('/career-booster'); ?>" style="color:#c4b5fd;font-size:14px;padding:8px 14px;border-radius:6px;text-decoration:none">Career Booster</a>
    <a href="<?php echo home_url('/inscription'); ?>" style="background:#a855f7;color:#fff;font-size:14px;padding:8px 20px;border-radius:6px;text-decoration:none;font-weight:600">Inscription</a>
  </div>
</nav>