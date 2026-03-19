<?php
/* Template Name: Mon Profil */
get_header();

$user_id = get_current_user_id();
?>

<h1>Mon Profil</h1>

<h3>Compétences</h3>
<p><?php echo get_field('competences', 'user_' . $user_id); ?></p>

<h3>Diplômes</h3>
<p><?php echo get_field('diplomes', 'user_' . $user_id); ?></p>

<h3>Expériences</h3>
<p><?php echo get_field('experiences', 'user_' . $user_id); ?></p>

<h3>Langues</h3>
<p><?php echo get_field('langues', 'user_' . $user_id); ?></p>

<h3>Localisation</h3>
<p><?php echo get_field('localisation', 'user_' . $user_id); ?></p>

<h3>Disponibilité</h3>
<p><?php echo get_field('disponibilite', 'user_' . $user_id); ?></p>

<?php get_footer(); ?>