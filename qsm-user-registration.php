<?php
/*
Plugin Name: QSM User Registration
Plugin URI: https://github.com/chris2k90/qsm-user-registration
Description: User Registration integration for Quiz Survey Master
Version: 0.1
*/

/**
 * Removes default QMS login filter
 * 
 * @since 0.1.0
 * @return void
 */
function qsmur_remove_default_qmn_require_login_check() {
    if (function_exists( 'qmn_require_login_check' )) {
        remove_filter('qmn_begin_shortcode', 'qmn_require_login_check', 10);
    }
}

/**
 * Overrides qmn_require_login_check function
 * from quiz-master-next/php/classes/class-qmn-quiz-manager.php
 * Skips wp_login_form
 * 
 * @since 0.1.0
 * @return string
 */
function qsmur_require_login_check($display, $qmn_quiz_options, $qmn_array_for_variables) {
  global $qmn_allowed_visit;
  if ($qmn_quiz_options->require_log_in == 1 && !is_user_logged_in()) {
      $qmn_allowed_visit = false;
      if(isset($qmn_quiz_options->require_log_in_text) && $qmn_quiz_options->require_log_in_text != ''){
          $mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->require_log_in_text, ENT_QUOTES));
      }else{
          $mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->require_log_in_text, ENT_QUOTES));
      }
      $mlw_message = apply_filters('mlw_qmn_template_variable_quiz_page', $mlw_message, $qmn_array_for_variables);
      $mlw_message = str_replace("\n", "<br>", $mlw_message);
      
      $display .= do_shortcode($mlw_message);
      $display .= qsmur_get_login_form($display);
  }
  return $display;
}

/**
 * Returns User Registration login form with redirection to the current page
 * 
 * @since 0.1.0
 * @return string User Registration login form
 */
function qsmur_get_login_form() {
    $current_rel_uri = add_query_arg( NULL, NULL );
    return do_shortcode('[user_registration_my_account redirect_url="'.$current_rel_uri.'"]');
}


/**
 * Loads the addon if QSM is installed and activated
 *
 * @since 0.1.0
 * @return void
 */
function qsm_addon_qsm_user_registration_load() {
    // Make sure User Registration is active
    $errors = false;
    if ( !class_exists('UserRegistration')) {
        add_action( 'admin_notices', 'qsm_addon_qsm_user_registration_missing_ur' );
        $errors = true;
    }
    // Make sure QSM is active
	if ( !class_exists( 'MLWQuizMasterNext' ) ) {
        add_action( 'admin_notices', 'qsm_addon_qsm_user_registration_missing_qsm' );
        $errors = true;
    }
    if (!$errors) {
        qsmur_remove_default_qmn_require_login_check();
        add_filter('qmn_begin_shortcode', 'qsmur_require_login_check', 10, 3);
    }
}

add_action( 'plugins_loaded', 'qsm_addon_qsm_user_registration_load' );

/**
 * Display notice if Quiz And Survey Master isn't installed
 *
 * @since 0.1.0
 * @return string The notice to display
 */
function qsm_addon_qsm_user_registration_missing_qsm() {
    echo '<div class="error"><p>QSM User Registration requires Quiz And Survey Master. Please install and activate the Quiz And Survey Master plugin.</p></div>';
}

/**
 * Display notice if User Registration isn't installed
 * 
 * @since 0.1.0
 * @return string The notice to display
 */
function qsm_addon_qsm_user_registration_missing_ur() {
    echo '<div class="error"><p>QSM User Registration requires User Registration. Please install and activate the User Registration plugin.</p></div>';
}