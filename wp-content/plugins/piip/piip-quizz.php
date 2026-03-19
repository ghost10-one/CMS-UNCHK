<?php
/**
 * Plugin Name: PIIP Quiz
 * Description: Gestion des scores quiz et badges candidats
 * Version: 1.0
 */

// Quand un résultat de quiz est sauvegardé
add_action('wp_post_insert_qmn_log', 'piip_update_skill_from_quiz', 10, 1);

function piip_update_skill_from_quiz($log_id) {
    $user_id = get_current_user_id();
    
    if (!$user_id) return;
    
    // Récupérer les données du log
    global $wpdb;
    $log = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlw_quizzes_log WHERE log_id = %d",
            $log_id
        )
    );
    
    if (!$log) return;
    
    $quiz_id = $log->quiz_id;
    $score = $log->point_score;
    
    // Sauvegarder le score
    update_user_meta($user_id, 'quiz_score_' . $quiz_id, $score);
    
    // Si score >= 70 → badge
    if ($score >= 70) {
        $badges = get_user_meta($user_id, 'badges', true);
        if (!is_array($badges)) $badges = [];
        $badges[] = 'Quiz_' . $quiz_id;
        $badges = array_unique($badges);
        update_user_meta($user_id, 'badges', $badges);
    }
}