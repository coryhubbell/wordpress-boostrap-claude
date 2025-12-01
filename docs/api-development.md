# 📓 API Development Guide

Build powerful REST APIs with DevelopmentTranslation Bridge framework.

## Table of Contents
- [Introduction](#introduction)
- [REST API Basics](#rest-api-basics)
- [Creating Endpoints](#creating-endpoints)
- [Authentication](#authentication)
- [AJAX Integration](#ajax-integration)
- [Data Validation](#data-validation)
- [Error Handling](#error-handling)
- [Real-World Examples](#real-world-examples)
- [Best Practices](#best-practices)

---

## Introduction

DevelopmentTranslation Bridge provides powerful tools for API development:

- **Native REST API** integration with WordPress
- **AJAX handlers** for dynamic interactions
- **Security built-in** with nonces and permissions
- **Data validation** and sanitization
- **Error handling** and logging
- **Response caching** for performance

---

## REST API Basics

### Registering Routes

```php
// Register custom REST API routes
add_action('rest_api_init', function() {
    // Basic route
    register_rest_route('devtb/v1', '/posts', [
        'methods' => 'GET',
        'callback' => 'devtb_get_posts',
        'permission_callback' => '__return_true' // Public endpoint
    ]);
    
    // Route with parameters
    register_rest_route('devtb/v1', '/post/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'devtb_get_single_post',
        'args' => [
            'id' => [
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ]
        ],
        'permission_callback' => '__return_true'
    ]);
    
    // Protected route
    register_rest_route('devtb/v1', '/user/profile', [
        'methods' => 'GET',
        'callback' => 'devtb_get_user_profile',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ]);
});
```

### Using Framework Helper

```php
// Using devtb_api_route helper
devtb_api_route('/products', 'get_products_handler', 'GET');
devtb_api_route('/product/create', 'create_product_handler', 'POST', 'edit_posts');
devtb_api_route('/product/delete/{id}', 'delete_product_handler', 'DELETE', 'delete_posts');
```

---

## Creating Endpoints

### GET Endpoint

```php
// Fetch custom posts with filtering
function devtb_get_products($request) {
    $params = $request->get_params();
    
    $args = [
        'post_type' => 'product',
        'posts_per_page' => isset($params['per_page']) ? $params['per_page'] : 10,
        'paged' => isset($params['page']) ? $params['page'] : 1,
        'orderby' => isset($params['orderby']) ? $params['orderby'] : 'date',
        'order' => isset($params['order']) ? $params['order'] : 'DESC'
    ];
    
    // Add category filter
    if (isset($params['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_category',
                'field' => 'slug',
                'terms' => $params['category']
            ]
        ];
    }
    
    // Add price filter
    if (isset($params['min_price']) || isset($params['max_price'])) {
        $meta_query = [];
        
        if (isset($params['min_price'])) {
            $meta_query[] = [
                'key' => 'price',
                'value' => $params['min_price'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }
        
        if (isset($params['max_price'])) {
            $meta_query[] = [
                'key' => 'price',
                'value' => $params['max_price'],
                'compare' => '<=',
                'type' => 'NUMERIC'
            ];
        }
        
        $args['meta_query'] = $meta_query;
    }
    
    $query = new WP_Query($args);
    $products = [];
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $products[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'slug' => get_post_field('post_name'),
                'excerpt' => get_the_excerpt(),
                'price' => get_field('price'),
                'image' => get_the_post_thumbnail_url('large'),
                'categories' => wp_get_post_terms(get_the_ID(), 'product_category', ['fields' => 'names']),
                'link' => get_permalink()
            ];
        }
    }
    
    wp_reset_postdata();
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $products,
        'total' => $query->found_posts,
        'pages' => $query->max_num_pages
    ], 200);
}

// Register the endpoint
register_rest_route('devtb/v1', '/products', [
    'methods' => 'GET',
    'callback' => 'devtb_get_products',
    'args' => [
        'per_page' => [
            'default' => 10,
            'sanitize_callback' => 'absint'
        ],
        'page' => [
            'default' => 1,
            'sanitize_callback' => 'absint'
        ],
        'category' => [
            'sanitize_callback' => 'sanitize_text_field'
        ],
        'min_price' => [
            'sanitize_callback' => 'absint'
        ],
        'max_price' => [
            'sanitize_callback' => 'absint'
        ]
    ],
    'permission_callback' => '__return_true'
]);
```

### POST Endpoint

```php
// Create new resource
function devtb_create_contact($request) {
    $params = $request->get_json_params();
    
    // Validate required fields
    $required = ['name', 'email', 'message'];
    foreach ($required as $field) {
        if (empty($params[$field])) {
            return new WP_Error(
                'missing_field',
                sprintf('Field %s is required', $field),
                ['status' => 400]
            );
        }
    }
    
    // Validate email
    if (!is_email($params['email'])) {
        return new WP_Error(
            'invalid_email',
            'Please provide a valid email address',
            ['status' => 400]
        );
    }
    
    // Create post
    $post_id = wp_insert_post([
        'post_title' => sanitize_text_field($params['name']),
        'post_content' => sanitize_textarea_field($params['message']),
        'post_type' => 'contact_submission',
        'post_status' => 'private'
    ]);
    
    if (is_wp_error($post_id)) {
        return new WP_Error(
            'creation_failed',
            'Failed to save submission',
            ['status' => 500]
        );
    }
    
    // Save meta data
    update_post_meta($post_id, 'email', sanitize_email($params['email']));
    update_post_meta($post_id, 'phone', sanitize_text_field($params['phone'] ?? ''));
    update_post_meta($post_id, 'submission_date', current_time('mysql'));
    update_post_meta($post_id, 'ip_address', $_SERVER['REMOTE_ADDR']);
    
    // Send email notification
    $admin_email = get_option('admin_email');
    $subject = 'New Contact Form Submission';
    $message = sprintf(
        "New submission from %s (%s)\n\nMessage:\n%s",
        $params['name'],
        $params['email'],
        $params['message']
    );
    
    wp_mail($admin_email, $subject, $message);
    
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Thank you for your message. We\'ll get back to you soon!',
        'submission_id' => $post_id
    ], 201);
}

// Register the endpoint
register_rest_route('devtb/v1', '/contact', [
    'methods' => 'POST',
    'callback' => 'devtb_create_contact',
    'permission_callback' => function() {
        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'];
        $transient_key = 'contact_limit_' . md5($ip);
        $attempts = get_transient($transient_key);
        
        if ($attempts >= 5) {
            return new WP_Error(
                'rate_limit',
                'Too many submissions. Please try again later.',
                ['status' => 429]
            );
        }
        
        set_transient($transient_key, $attempts + 1, HOUR_IN_SECONDS);
        return true;
    }
]);
```

### PUT/UPDATE Endpoint

```php
// Update resource
function devtb_update_profile($request) {
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    
    // Update user data
    $user_data = [
        'ID' => $user_id
    ];
    
    if (isset($params['first_name'])) {
        $user_data['first_name'] = sanitize_text_field($params['first_name']);
    }
    
    if (isset($params['last_name'])) {
        $user_data['last_name'] = sanitize_text_field($params['last_name']);
    }
    
    if (isset($params['email'])) {
        if (!is_email($params['email'])) {
            return new WP_Error('invalid_email', 'Invalid email address', ['status' => 400]);
        }
        $user_data['user_email'] = $params['email'];
    }
    
    $result = wp_update_user($user_data);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    // Update user meta
    if (isset($params['bio'])) {
        update_user_meta($user_id, 'description', sanitize_textarea_field($params['bio']));
    }
    
    if (isset($params['phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($params['phone']));
    }
    
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => [
            'id' => $user_id,
            'email' => wp_get_current_user()->user_email,
            'first_name' => get_user_meta($user_id, 'first_name', true),
            'last_name' => get_user_meta($user_id, 'last_name', true),
            'bio' => get_user_meta($user_id, 'description', true)
        ]
    ], 200);
}

register_rest_route('devtb/v1', '/user/profile', [
    'methods' => 'PUT',
    'callback' => 'devtb_update_profile',
    'permission_callback' => 'is_user_logged_in'
]);
```

### DELETE Endpoint

```php
// Delete resource
function devtb_delete_item($request) {
    $post_id = $request->get_param('id');
    
    // Check if post exists
    if (!get_post($post_id)) {
        return new WP_Error(
            'not_found',
            'Item not found',
            ['status' => 404]
        );
    }
    
    // Check permissions
    if (!current_user_can('delete_post', $post_id)) {
        return new WP_Error(
            'forbidden',
            'You do not have permission to delete this item',
            ['status' => 403]
        );
    }
    
    // Delete the post
    $result = wp_delete_post($post_id, true); // true = force delete
    
    if (!$result) {
        return new WP_Error(
            'deletion_failed',
            'Failed to delete item',
            ['status' => 500]
        );
    }
    
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Item deleted successfully',
        'deleted_id' => $post_id
    ], 200);
}

register_rest_route('devtb/v1', '/item/(?P<id>\d+)', [
    'methods' => 'DELETE',
    'callback' => 'devtb_delete_item',
    'permission_callback' => 'is_user_logged_in',
    'args' => [
        'id' => [
            'validate_callback' => function($param) {
                return is_numeric($param);
            }
        ]
    ]
]);
```

---

## Authentication

### Basic Authentication

```php
// Check if user is logged in
register_rest_route('devtb/v1', '/private', [
    'methods' => 'GET',
    'callback' => 'private_endpoint',
    'permission_callback' => 'is_user_logged_in'
]);

// Check specific capability
register_rest_route('devtb/v1', '/admin', [
    'methods' => 'GET',
    'callback' => 'admin_endpoint',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    }
]);
```

### Nonce Authentication

```php
// Generate nonce in frontend
wp_localize_script('devtb-api', 'devtb_api', [
    'nonce' => wp_create_nonce('wp_rest'),
    'api_url' => rest_url('devtb/v1/')
]);

// JavaScript request with nonce
fetch(devtb_api.api_url + 'endpoint', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': devtb_api.nonce
    },
    body: JSON.stringify(data)
});
```

### Custom Authentication

```php
// Custom token authentication
function devtb_custom_auth($request) {
    $token = $request->get_header('X-API-Token');
    
    if (!$token) {
        return new WP_Error(
            'no_token',
            'API token required',
            ['status' => 401]
        );
    }
    
    // Validate token
    $user_id = get_option('api_token_' . md5($token));
    
    if (!$user_id) {
        return new WP_Error(
            'invalid_token',
            'Invalid API token',
            ['status' => 401]
        );
    }
    
    // Set current user
    wp_set_current_user($user_id);
    
    return true;
}

register_rest_route('devtb/v1', '/protected', [
    'methods' => 'GET',
    'callback' => 'protected_endpoint',
    'permission_callback' => 'devtb_custom_auth'
]);
```

---

## AJAX Integration

### AJAX Handler Setup

```php
// Register AJAX actions
add_action('wp_ajax_load_more_posts', 'devtb_load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'devtb_load_more_posts');

function devtb_load_more_posts() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'devtb_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 10;
    
    $loop = new DEVTB_Loop([
        'posts_per_page' => $per_page,
        'paged' => $page
    ]);
    
    ob_start();
    
    if ($loop->have_posts()) {
        while ($loop->have_posts()) {
            $loop->the_post();
            devtb_component('card');
        }
    }
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'has_more' => $page < $loop->get_query()->max_num_pages
    ]);
}
```

### Frontend AJAX Implementation

```javascript
// Localize script
wp_localize_script('devtb-main', 'devtb_ajax', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('devtb_ajax_nonce')
]);

// JavaScript AJAX call
jQuery(document).ready(function($) {
    let page = 1;
    
    $('#load-more').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).text('Loading...');
        
        $.ajax({
            url: devtb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_posts',
                page: ++page,
                nonce: devtb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#posts-container').append(response.data.html);
                    
                    if (!response.data.has_more) {
                        button.hide();
                    } else {
                        button.prop('disabled', false).text('Load More');
                    }
                }
            },
            error: function() {
                button.prop('disabled', false).text('Error - Try Again');
            }
        });
    });
});
```

### Using Framework Helper

```php
// Register AJAX action using framework helper
devtb_ajax('filter_products', function() {
    $category = sanitize_text_field($_POST['category']);
    $price_range = sanitize_text_field($_POST['price_range']);
    
    $args = [
        'post_type' => 'product',
        'posts_per_page' => 12
    ];
    
    if ($category) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_category',
                'field' => 'slug',
                'terms' => $category
            ]
        ];
    }
    
    if ($price_range) {
        list($min, $max) = explode('-', $price_range);
        $args['meta_query'] = [
            [
                'key' => 'price',
                'value' => [$min, $max],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            ]
        ];
    }
    
    $products = get_posts($args);
    
    ob_start();
    foreach ($products as $product) {
        setup_postdata($product);
        devtb_component('product-card');
    }
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success(['html' => $html]);
}, true); // true = public action
```

---

## Data Validation

### Input Validation

```php
// Comprehensive validation
function validate_product_data($data) {
    $errors = [];
    
    // Required fields
    if (empty($data['title'])) {
        $errors[] = 'Title is required';
    } elseif (strlen($data['title']) > 200) {
        $errors[] = 'Title must be less than 200 characters';
    }
    
    // Price validation
    if (!isset($data['price'])) {
        $errors[] = 'Price is required';
    } elseif (!is_numeric($data['price'])) {
        $errors[] = 'Price must be a number';
    } elseif ($data['price'] < 0) {
        $errors[] = 'Price cannot be negative';
    }
    
    // Email validation
    if (isset($data['email']) && !is_email($data['email'])) {
        $errors[] = 'Invalid email address';
    }
    
    // URL validation
    if (isset($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Invalid website URL';
    }
    
    // Date validation
    if (isset($data['date'])) {
        $date = DateTime::createFromFormat('Y-m-d', $data['date']);
        if (!$date || $date->format('Y-m-d') !== $data['date']) {
            $errors[] = 'Invalid date format (use YYYY-MM-DD)';
        }
    }
    
    return $errors;
}

// Use in endpoint
function create_product($request) {
    $data = $request->get_json_params();
    
    $errors = validate_product_data($data);
    if (!empty($errors)) {
        return new WP_Error(
            'validation_failed',
            'Validation errors',
            [
                'status' => 400,
                'errors' => $errors
            ]
        );
    }
    
    // Process validated data...
}
```

### Sanitization

```php
// Sanitize all input
function sanitize_product_data($data) {
    return [
        'title' => sanitize_text_field($data['title'] ?? ''),
        'content' => wp_kses_post($data['content'] ?? ''),
        'price' => floatval($data['price'] ?? 0),
        'sku' => sanitize_text_field($data['sku'] ?? ''),
        'email' => sanitize_email($data['email'] ?? ''),
        'website' => esc_url_raw($data['website'] ?? ''),
        'status' => in_array($data['status'], ['active', 'inactive']) 
            ? $data['status'] 
            : 'active'
    ];
}
```

---

## Error Handling

### Structured Error Responses

```php
// Error handler class
class DEVTB_API_Error {
    public static function response($code, $message, $data = null, $status = 400) {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
        
        if ($data) {
            $response['error']['data'] = $data;
        }
        
        return new WP_REST_Response($response, $status);
    }
    
    public static function not_found($message = 'Resource not found') {
        return self::response('not_found', $message, null, 404);
    }
    
    public static function unauthorized($message = 'Unauthorized access') {
        return self::response('unauthorized', $message, null, 401);
    }
    
    public static function forbidden($message = 'Access forbidden') {
        return self::response('forbidden', $message, null, 403);
    }
    
    public static function validation($errors) {
        return self::response('validation_failed', 'Validation errors', $errors, 400);
    }
    
    public static function server_error($message = 'Internal server error') {
        return self::response('server_error', $message, null, 500);
    }
}

// Usage in endpoints
function get_product($request) {
    $id = $request->get_param('id');
    $product = get_post($id);
    
    if (!$product || $product->post_type !== 'product') {
        return DEVTB_API_Error::not_found('Product not found');
    }
    
    if (!current_user_can('read_private_posts') && $product->post_status === 'private') {
        return DEVTB_API_Error::forbidden('You cannot view this product');
    }
    
    // Return product data...
}
```

### Try-Catch Error Handling

```php
function process_payment($request) {
    try {
        $data = $request->get_json_params();
        
        // Validate
        if (empty($data['amount'])) {
            throw new Exception('Amount is required');
        }
        
        // Process payment
        $payment_gateway = new Payment_Gateway();
        $result = $payment_gateway->charge($data['amount'], $data['token']);
        
        if (!$result->success) {
            throw new Exception($result->error_message);
        }
        
        // Save order
        $order_id = wp_insert_post([
            'post_type' => 'order',
            'post_title' => 'Order #' . time(),
            'post_status' => 'publish'
        ]);
        
        if (is_wp_error($order_id)) {
            throw new Exception('Failed to create order');
        }
        
        return new WP_REST_Response([
            'success' => true,
            'order_id' => $order_id,
            'transaction_id' => $result->transaction_id
        ], 200);
        
    } catch (Exception $e) {
        // Log error
        error_log('Payment error: ' . $e->getMessage());
        
        return DEVTB_API_Error::server_error($e->getMessage());
    }
}
```

---

## Real-World Examples

### 1. Search Autocomplete API

```php
// Autocomplete endpoint
function devtb_search_autocomplete($request) {
    $term = $request->get_param('term');
    
    if (strlen($term) < 2) {
        return new WP_REST_Response([], 200);
    }
    
    global $wpdb;
    
    // Search in post titles
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, post_title, post_type 
         FROM {$wpdb->posts} 
         WHERE post_title LIKE %s 
         AND post_status = 'publish' 
         LIMIT 10",
        '%' . $wpdb->esc_like($term) . '%'
    ));
    
    $suggestions = array_map(function($post) {
        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'url' => get_permalink($post->ID)
        ];
    }, $results);
    
    // Cache results
    set_transient('search_' . md5($term), $suggestions, HOUR_IN_SECONDS);
    
    return new WP_REST_Response($suggestions, 200);
}

register_rest_route('devtb/v1', '/search/autocomplete', [
    'methods' => 'GET',
    'callback' => 'devtb_search_autocomplete',
    'args' => [
        'term' => [
            'required' => true,
            'sanitize_callback' => 'sanitize_text_field'
        ]
    ],
    'permission_callback' => '__return_true'
]);
```

### 2. File Upload API

```php
// Handle file uploads
function devtb_upload_file($request) {
    $files = $request->get_file_params();
    
    if (empty($files['file'])) {
        return DEVTB_API_Error::validation(['file' => 'No file uploaded']);
    }
    
    $file = $files['file'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($file['type'], $allowed_types)) {
        return DEVTB_API_Error::validation(['file' => 'Invalid file type']);
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return DEVTB_API_Error::validation(['file' => 'File too large (5MB max)']);
    }
    
    // Upload file
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $upload = wp_handle_upload($file, ['test_form' => false]);
    
    if (isset($upload['error'])) {
        return DEVTB_API_Error::server_error($upload['error']);
    }
    
    // Create attachment
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => $upload['type'],
        'post_title' => sanitize_file_name($file['name']),
        'post_content' => '',
        'post_status' => 'inherit'
    ], $upload['file']);
    
    // Generate metadata
    $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    wp_update_attachment_metadata($attachment_id, $metadata);
    
    return new WP_REST_Response([
        'success' => true,
        'attachment_id' => $attachment_id,
        'url' => wp_get_attachment_url($attachment_id),
        'sizes' => wp_get_attachment_image_sizes($attachment_id)
    ], 201);
}

register_rest_route('devtb/v1', '/upload', [
    'methods' => 'POST',
    'callback' => 'devtb_upload_file',
    'permission_callback' => 'is_user_logged_in'
]);
```

### 3. Batch Operations API

```php
// Batch update posts
function devtb_batch_update($request) {
    $operations = $request->get_json_params();
    
    if (!is_array($operations)) {
        return DEVTB_API_Error::validation(['operations' => 'Must be an array']);
    }
    
    $results = [];
    $errors = [];
    
    foreach ($operations as $op) {
        try {
            switch ($op['action']) {
                case 'update':
                    $result = wp_update_post([
                        'ID' => $op['id'],
                        'post_status' => $op['status']
                    ]);
                    
                    if (is_wp_error($result)) {
                        throw new Exception($result->get_error_message());
                    }
                    
                    $results[] = [
                        'id' => $op['id'],
                        'action' => 'update',
                        'success' => true
                    ];
                    break;
                    
                case 'delete':
                    $result = wp_delete_post($op['id'], true);
                    
                    if (!$result) {
                        throw new Exception('Failed to delete post');
                    }
                    
                    $results[] = [
                        'id' => $op['id'],
                        'action' => 'delete',
                        'success' => true
                    ];
                    break;
                    
                default:
                    throw new Exception('Unknown action: ' . $op['action']);
            }
        } catch (Exception $e) {
            $errors[] = [
                'id' => $op['id'],
                'action' => $op['action'],
                'error' => $e->getMessage()
            ];
        }
    }
    
    return new WP_REST_Response([
        'success' => empty($errors),
        'results' => $results,
        'errors' => $errors,
        'summary' => [
            'total' => count($operations),
            'succeeded' => count($results),
            'failed' => count($errors)
        ]
    ], 200);
}

register_rest_route('devtb/v1', '/batch', [
    'methods' => 'POST',
    'callback' => 'devtb_batch_update',
    'permission_callback' => function() {
        return current_user_can('edit_posts');
    }
]);
```

---

## Best Practices

### 1. Security

```php
// Always validate permissions
'permission_callback' => function() {
    return current_user_can('required_capability');
}

// Always sanitize input
$clean_data = sanitize_text_field($raw_data);

// Always escape output
echo esc_html($data);

// Use nonces for state-changing operations
wp_verify_nonce($_POST['nonce'], 'action_name');
```

### 2. Performance

```php
// Cache expensive queries
$cache_key = 'api_products_' . md5(serialize($args));
$products = get_transient($cache_key);

if (false === $products) {
    $products = get_posts($args);
    set_transient($cache_key, $products, HOUR_IN_SECONDS);
}

// Limit query results
'posts_per_page' => 50, // Never -1 for public APIs

// Only get needed fields
'fields' => 'ids', // When you only need IDs
```

### 3. Documentation

```php
// Document your endpoints
register_rest_route('devtb/v1', '/endpoint', [
    'methods' => 'GET',
    'callback' => 'callback_function',
    'description' => 'Retrieves list of products',
    'args' => [
        'category' => [
            'description' => 'Filter by category slug',
            'type' => 'string',
            'required' => false,
            'sanitize_callback' => 'sanitize_text_field'
        ]
    ]
]);
```

### 4. Versioning

```php
// Use versioned namespaces
register_rest_route('devtb/v1', '/posts', $args);  // Version 1
register_rest_route('devtb/v2', '/posts', $args);  // Version 2

// Deprecation notices
function deprecated_endpoint($request) {
    header('X-API-Deprecated: true');
    header('X-API-Deprecation-Date: 2025-01-01');
    header('X-API-Alternative: /devtb/v2/endpoint');
    
    // Still return data for backward compatibility
    return get_data($request);
}
```

---

## Testing Your API

### Using WordPress REST API Tester

```php
// Test in browser
// GET: https://yoursite.com/wp-json/devtb/v1/posts

// Test with curl
curl -X GET https://yoursite.com/wp-json/devtb/v1/posts

// Test POST with curl
curl -X POST https://yoursite.com/wp-json/devtb/v1/contact \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","message":"Test"}'

// Test with authentication
curl -X GET https://yoursite.com/wp-json/devtb/v1/private \
  -H "X-WP-Nonce: your_nonce_here"
```

### Unit Testing

```php
class Test_API extends WP_UnitTestCase {
    
    public function test_products_endpoint() {
        $request = new WP_REST_Request('GET', '/devtb/v1/products');
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $this->assertTrue($response->get_data()['success']);
    }
    
    public function test_authentication_required() {
        $request = new WP_REST_Request('GET', '/devtb/v1/private');
        $response = rest_do_request($request);
        
        $this->assertEquals(401, $response->get_status());
    }
}
```

---

## Next Steps

1. 📘 **[Getting Started](getting-started.md)** - Framework basics
2. 📗 **[Claude Integration](claude-integration.md)** - AI development
3. 📙 **[The Loop Mastery](the-loop.md)** - WordPress queries

---

**Build powerful, secure, and scalable APIs with DevelopmentTranslation Bridge! 🚀**
