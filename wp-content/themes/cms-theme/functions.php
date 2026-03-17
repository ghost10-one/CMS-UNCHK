<?php
function piip_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'primary' => 'Menu Principal',
    ));
}
    add_action('after_setup_theme', 'piip_theme_setup');