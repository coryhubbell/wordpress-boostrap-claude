<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the testing environment for WordPress Bootstrap Claude
 *
 * @package WordPress_Bootstrap_Claude
 * @subpackage Tests
 */

// Define test mode
define('WPBC_TESTING', true);

// Define root directory
define('WPBC_ROOT', dirname(__DIR__));
define('WPBC_INCLUDES', WPBC_ROOT . '/includes');
define('WPBC_TRANSLATION_BRIDGE', WPBC_ROOT . '/translation-bridge');
define('WPBC_TRANSLATION_BRIDGE_DIR', WPBC_TRANSLATION_BRIDGE);
define('WPBC_VERSION', '3.2.0');

// Load Composer autoloader
$autoloader = WPBC_ROOT . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
} else {
    echo "Composer autoloader not found. Run: composer install\n";
    exit(1);
}

// Initialize Brain Monkey for WordPress function mocking
if (class_exists('\Brain\Monkey')) {
    \Brain\Monkey\setUp();
}

// Mock WordPress functions that are commonly used
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {
        return true;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args) {
        return $value;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        return true;
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data) {
        return $data;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return strip_tags($str);
    }
}

// Mock WP_Error class
if (!class_exists('WP_Error')) {
    class WP_Error {
        private $errors = [];
        private $error_data = [];

        public function __construct($code = '', $message = '', $data = '') {
            if (empty($code)) {
                return;
            }
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }

        public function get_error_code() {
            return key($this->errors);
        }

        public function get_error_message($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            return $this->errors[$code][0] ?? '';
        }

        public function get_error_data($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            return $this->error_data[$code] ?? null;
        }
    }
}

// Mock WP_REST_Request class
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        private $params = [];
        private $headers = [];

        public function __construct($method = 'GET', $route = '', $params = []) {
            $this->params = $params;
        }

        public function get_param($key) {
            return $this->params[$key] ?? null;
        }

        public function get_params() {
            return $this->params;
        }

        public function set_param($key, $value) {
            $this->params[$key] = $value;
        }

        public function get_header($key) {
            return $this->headers[$key] ?? null;
        }

        public function set_header($key, $value) {
            $this->headers[$key] = $value;
        }
    }
}

// Mock WP_REST_Response class
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        private $data;
        private $status;

        public function __construct($data = null, $status = 200) {
            $this->data = $data;
            $this->status = $status;
        }

        public function get_data() {
            return $this->data;
        }

        public function get_status() {
            return $this->status;
        }
    }
}

echo "PHPUnit Bootstrap loaded successfully\n";
echo "Test environment initialized\n";
