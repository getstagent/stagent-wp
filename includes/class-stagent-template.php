<?php
/**
 * Template rendering engine for the Stagent plugin.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_Template class
 *
 * Provides methods to render template files with variables.
 */
class Stagent_Template {

    /**
     * Base directory for templates.
     *
     * @var string
     */
    private static $template_dir = STAGENT_PLUGIN_DIR . 'templates/';

    /**
     * Render a template file with provided variables.
     *
     * @param string $template_name The name of the template file (e.g., 'stagent-settings-page.php').
     * @param array  $variables     Associative array of variables to extract into the template scope.
     * @param bool   $return        Whether to return the output as a string (true) or echo it (false).
     * @return string|void The rendered template content if $return is true, otherwise void.
     */
    public static function render($template_name, $variables = [], $return = false) {
        // Sanitize template name to prevent directory traversal
        $template_name = ltrim(str_replace(['..', '/', '\\'], '', sanitize_file_name($template_name)), '.');
        $template_path = self::$template_dir . $template_name;

        if (!file_exists($template_path)) {
            return $return ? '' : null;
        }

        // Validate $variables is an array
        $variables = is_array($variables) ? $variables : [];

        /**
         * Filter the variables passed to the template.
         *
         * @param array  $variables    The variables to extract.
         * @param string $template_name The name of the template being rendered.
         */
        $variables = apply_filters('stagent_template_variables', $variables, $template_name);

        // Extract variables into the current scope
        extract($variables, EXTR_SKIP);

        if ($return) {
            ob_start();
            include $template_path;
            $output = ob_get_clean();
            return $output;
        } else {
            include $template_path;
        }
    }

    /**
     * Get the full path to a template file without rendering it.
     *
     * @param string $template_name The name of the template file.
     * @return string|null The full path to the template file, or null if it doesn't exist.
     */
    public static function get_template_path($template_name) {
        $template_name = ltrim(str_replace(['..', '/', '\\'], '', sanitize_file_name($template_name)), '.');
        $template_path = self::$template_dir . $template_name;

        return file_exists($template_path) ? $template_path : null;
    }
}