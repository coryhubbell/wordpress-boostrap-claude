<?php
/**
 * WPBC Logger
 *
 * Handles logging for CLI operations
 *
 * @package    WordPress_Bootstrap_Claude
 * @subpackage CLI
 * @version    3.1.0
 */

class WPBC_Logger {
    /**
     * Log levels
     */
    const LEVEL_DEBUG   = 'DEBUG';
    const LEVEL_INFO    = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR   = 'ERROR';

    /**
     * Log file rotation settings
     */
    const MAX_FILE_SIZE = 10485760; // 10MB in bytes
    const MAX_FILES     = 5;         // Number of rotated log files to keep

    /**
     * Log directory
     *
     * @var string
     */
    private $log_dir;

    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;

    /**
     * Whether logging is enabled
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * Minimum log level
     *
     * @var string
     */
    private $min_level = self::LEVEL_INFO;

    /**
     * Maximum log file size (in bytes)
     *
     * @var int
     */
    private $max_file_size = self::MAX_FILE_SIZE;

    /**
     * Number of rotated log files to keep
     *
     * @var int
     */
    private $max_files = self::MAX_FILES;

    /**
     * Constructor
     *
     * @param string $log_dir Optional log directory
     */
    public function __construct($log_dir = null) {
        if ($log_dir === null) {
            $log_dir = WPBC_ROOT . '/logs';
        }

        $this->log_dir = $log_dir;
        $this->log_file = $this->log_dir . '/wpbc-' . date('Y-m-d') . '.log';

        $this->initialize();
    }

    /**
     * Initialize logger
     */
    private function initialize() {
        // Create log directory if it doesn't exist
        if (!is_dir($this->log_dir)) {
            if (!mkdir($this->log_dir, 0755, true)) {
                $this->enabled = false;
                return;
            }
        }

        // Create .htaccess to protect logs
        $htaccess = $this->log_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }

        // Rotate log if needed
        $this->rotate_if_needed();
    }

    /**
     * Log a debug message
     *
     * @param string $message Message to log
     * @param array  $context Additional context
     */
    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message Message to log
     * @param array  $context Additional context
     */
    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message Message to log
     * @param array  $context Additional context
     */
    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log an error message
     *
     * @param string $message Message to log
     * @param array  $context Additional context
     */
    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * Log a message
     *
     * @param string $level   Log level
     * @param string $message Message to log
     * @param array  $context Additional context
     */
    private function log($level, $message, $context = []) {
        if (!$this->enabled) {
            return;
        }

        if (!$this->should_log($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' ' . json_encode($context) : '';

        $log_line = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;

        // Write to file
        file_put_contents($this->log_file, $log_line, FILE_APPEND | LOCK_EX);

        // Also write errors to stderr
        if ($level === self::LEVEL_ERROR) {
            fwrite(STDERR, $log_line);
        }
    }

    /**
     * Check if a level should be logged
     *
     * @param string $level Log level
     * @return bool
     */
    private function should_log($level) {
        $levels = [
            self::LEVEL_DEBUG   => 0,
            self::LEVEL_INFO    => 1,
            self::LEVEL_WARNING => 2,
            self::LEVEL_ERROR   => 3,
        ];

        $current = $levels[$level] ?? 1;
        $minimum = $levels[$this->min_level] ?? 1;

        return $current >= $minimum;
    }

    /**
     * Set minimum log level
     *
     * @param string $level Log level
     */
    public function set_min_level($level) {
        $this->min_level = $level;
    }

    /**
     * Enable debug logging
     */
    public function enable_debug() {
        $this->min_level = self::LEVEL_DEBUG;
    }

    /**
     * Rotate log file if needed
     */
    private function rotate_if_needed() {
        if (!file_exists($this->log_file)) {
            return;
        }

        $size = filesize($this->log_file);

        if ($size < $this->max_file_size) {
            return;
        }

        // Rotate existing files
        for ($i = $this->max_files - 1; $i > 0; $i--) {
            $old_file = $this->log_file . '.' . $i;
            $new_file = $this->log_file . '.' . ($i + 1);

            if (file_exists($old_file)) {
                if ($i === $this->max_files - 1) {
                    // Delete oldest file
                    unlink($old_file);
                } else {
                    // Rename file
                    rename($old_file, $new_file);
                }
            }
        }

        // Rotate current log file
        if (file_exists($this->log_file)) {
            rename($this->log_file, $this->log_file . '.1');
        }
    }

    /**
     * Get log file path
     *
     * @return string
     */
    public function get_log_file() {
        return $this->log_file;
    }

    /**
     * Get recent log entries
     *
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function get_recent_entries($lines = 50) {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $content = file_get_contents($this->log_file);
        $all_lines = explode(PHP_EOL, $content);
        $all_lines = array_filter($all_lines); // Remove empty lines

        return array_slice($all_lines, -$lines);
    }

    /**
     * Clear log file
     */
    public function clear() {
        if (file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
        }
    }

    /**
     * Log translation operation
     *
     * @param string $source Source framework
     * @param string $target Target framework
     * @param string $input  Input file
     * @param string $output Output file
     * @param float  $time   Execution time
     * @param bool   $success Whether operation succeeded
     */
    public function log_translation($source, $target, $input, $output, $time, $success = true) {
        $context = [
            'source'  => $source,
            'target'  => $target,
            'input'   => $input,
            'output'  => $output,
            'time'    => round($time, 2),
            'success' => $success,
        ];

        if ($success) {
            $this->info("Translation completed: {$source} → {$target}", $context);
        } else {
            $this->error("Translation failed: {$source} → {$target}", $context);
        }
    }

    /**
     * Log CLI command
     *
     * @param string $command Command name
     * @param array  $args    Command arguments
     */
    public function log_command($command, $args = []) {
        $this->info("CLI command: {$command}", ['args' => $args]);
    }
}
