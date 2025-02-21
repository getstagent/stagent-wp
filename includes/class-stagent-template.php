<?php

if (!defined('ABSPATH')) {
    exit;
}

// Render engine for template files
class Stagent_Template {
    public static function render($template_name, $variables = [], $return = false) {
        $template_path = STAGENT_PLUGIN_DIR . 'templates/' . $template_name;

        if (!file_exists($template_path)) {
            return '';
        }

        extract($variables, EXTR_SKIP);

        if ($return) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        } else {
            include $template_path;
        }
    }
}