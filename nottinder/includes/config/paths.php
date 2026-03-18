<?php
// Determine base URL dynamically
if (isset($_SERVER['CONTEXT_PREFIX']) && isset($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
    // On the server with userdir – e.g. /~krogellh/nottinder
    $base_url = $_SERVER['CONTEXT_PREFIX'] . '/nottinder';
} else {
    // Fallback for local development – change this if needed
    $base_url = '';
}
define('BASE_URL', rtrim($base_url, '/'));

// Filesystem root of the project (two levels up from this file)
define('ROOT_PATH', dirname(dirname(__DIR__)));
?>