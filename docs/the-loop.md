# 📙 The Loop Mastery

Master WordPress's most powerful feature - The Loop - with the WordPress Bootstrap Claude framework's advanced implementation.

## Table of Contents
- [Understanding The Loop](#understanding-the-loop)
- [WPBC_Loop Class](#wpbc_loop-class)
- [Basic Loop Patterns](#basic-loop-patterns)
- [Advanced Queries](#advanced-queries)
- [Custom Loop Templates](#custom-loop-templates)
- [Performance Optimization](#performance-optimization)
- [Real-World Examples](#real-world-examples)

---

## Understanding The Loop

The Loop is WordPress's mechanism for displaying posts. WordPress Bootstrap Claude enhances this with the `WPBC_Loop` class, providing:

- **Cleaner syntax**
- **Built-in optimization**
- **Template integration**
- **Bootstrap components**
- **Caching support**

### Traditional WordPress Loop

```php
<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <!-- Display post content -->
    <?php endwhile; ?>
<?php endif; ?>
```

### WPBC Enhanced Loop

```php
$loop = new WPBC_Loop(['posts_per_page' => 10]);
while ($loop->have_posts()) : $loop->the_post();
    $loop->render_template('card');
endwhile;
```

---

## WPBC_Loop Class

### Basic Usage

```php
// Simple loop
$loop = new WPBC_Loop();

// With arguments
$loop = new WPBC_Loop([
    'post_type' => 'post',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC'
]);
```

### Available Methods

```php
// Check if posts exist
if ($loop->have_posts()) { }

// Get post count
$count = $loop->found_posts();

// Reset the loop
$loop->reset();

// Get current post
$post = $loop->current_post();

// Render with template
$loop->render_template('template-name', $args);

// Get query object
$query = $loop->get_query();
```

### Loop Arguments

All WP_Query arguments work, plus custom ones:

```php
$loop = new WPBC_Loop([
    // Standard WP_Query args
    'post_type' => 'post',
    'posts_per_page' => 10,
    
    // WPBC enhancements
    'cache' => true,              // Enable query caching
    'cache_time' => 3600,         // Cache duration (seconds)
    'template' => 'card',         // Default template
    'container' => 'row',         // Wrapper class
    'item_class' => 'col-md-4'    // Item wrapper class
]);
```

---

## Basic Loop Patterns

### 1. Standard Blog Loop

```php
// Display recent blog posts
$loop = new WPBC_Loop([
    'post_type' => 'post',
    'posts_per_page' => 10
]);

if ($loop->have_posts()) : ?>
    <div class="container">
        <div class="row">
            <?php while ($loop->have_posts()) : $loop->the_post(); ?>
                <div class="col-md-6 mb-4">
                    <article id="post-<?php the_ID(); ?>" <?php post_class('card h-100'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" 
                                 class="card-img-top" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h2 class="card-title h4">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="card-text"><?php the_excerpt(); ?></div>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted"><?php the_date(); ?></small>
                        </div>
                    </article>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endif;
$loop->reset();
```

### 2. Custom Post Type Loop

```php
// Display custom post type
$loop = new WPBC_Loop([
    'post_type' => 'portfolio',
    'posts_per_page' => 12,
    'orderby' => 'menu_order',
    'order' => 'ASC'
]);

if ($loop->have_posts()) : ?>
    <div class="portfolio-grid">
        <?php while ($loop->have_posts()) : $loop->the_post(); ?>
            <?php wpbc_component('portfolio-item', [
                'title' => get_the_title(),
                'image' => get_the_post_thumbnail_url('large'),
                'category' => get_the_terms(get_the_ID(), 'portfolio_category'),
                'link' => get_permalink()
            ]); ?>
        <?php endwhile; ?>
    </div>
<?php endif;
```

### 3. Related Posts Loop

```php
// Get related posts by category
$categories = wp_get_post_categories(get_the_ID());

$loop = new WPBC_Loop([
    'category__in' => $categories,
    'post__not_in' => [get_the_ID()],
    'posts_per_page' => 3,
    'orderby' => 'rand'
]);

if ($loop->have_posts()) : ?>
    <section class="related-posts">
        <h3>Related Articles</h3>
        <div class="row">
            <?php while ($loop->have_posts()) : $loop->the_post();
                $loop->render_template('card-horizontal');
            endwhile; ?>
        </div>
    </section>
<?php endif;
```

---

## Advanced Queries

### 1. Meta Query

```php
// Posts with specific meta values
$loop = new WPBC_Loop([
    'post_type' => 'product',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'featured',
            'value' => 'yes',
            'compare' => '='
        ],
        [
            'key' => 'price',
            'value' => 100,
            'compare' => '<=',
            'type' => 'NUMERIC'
        ]
    ]
]);
```

### 2. Tax Query

```php
// Posts in specific taxonomies
$loop = new WPBC_Loop([
    'post_type' => 'product',
    'tax_query' => [
        'relation' => 'OR',
        [
            'taxonomy' => 'product_category',
            'field' => 'slug',
            'terms' => ['electronics', 'computers']
        ],
        [
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => ['featured'],
            'operator' => 'IN'
        ]
    ]
]);
```

### 3. Date Query

```php
// Posts from last 30 days
$loop = new WPBC_Loop([
    'date_query' => [
        [
            'after' => '30 days ago',
            'inclusive' => true
        ]
    ]
]);

// Posts from specific year/month
$loop = new WPBC_Loop([
    'date_query' => [
        [
            'year' => 2024,
            'month' => 6,
            'day' => 15,
            'compare' => '>='
        ]
    ]
]);
```

### 4. Multiple Post Types

```php
// Query multiple post types
$loop = new WPBC_Loop([
    'post_type' => ['post', 'page', 'portfolio'],
    'posts_per_page' => 20,
    'orderby' => 'modified',
    'order' => 'DESC'
]);
```

### 5. Search Query

```php
// Search posts
$loop = new WPBC_Loop([
    's' => get_search_query(),
    'post_type' => 'any',
    'posts_per_page' => 20
]);

// Enhanced search with meta
$loop = new WPBC_Loop([
    's' => get_search_query(),
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'subtitle',
            'value' => get_search_query(),
            'compare' => 'LIKE'
        ]
    ]
]);
```

---

## Custom Loop Templates

### Creating Templates

Create template files in `/templates/loops/`:

```php
// templates/loops/card-product.php
<div class="col-md-4 mb-4">
    <div class="card h-100 product-card">
        <?php if (has_post_thumbnail()) : ?>
            <div class="card-img-wrapper">
                <img src="<?php the_post_thumbnail_url('medium'); ?>" 
                     class="card-img-top" alt="<?php the_title(); ?>">
                <?php if (get_field('sale_price')) : ?>
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                        Sale
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="card-body">
            <h3 class="card-title h5"><?php the_title(); ?></h3>
            <p class="card-text"><?php the_excerpt(); ?></p>
            <div class="price-wrapper">
                <?php if (get_field('sale_price')) : ?>
                    <span class="text-decoration-line-through text-muted">
                        $<?php the_field('regular_price'); ?>
                    </span>
                    <span class="h5 text-danger ms-2">
                        $<?php the_field('sale_price'); ?>
                    </span>
                <?php else : ?>
                    <span class="h5">$<?php the_field('regular_price'); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            <a href="<?php the_permalink(); ?>" class="btn btn-primary w-100">
                View Details
            </a>
        </div>
    </div>
</div>
```

### Using Templates

```php
$loop = new WPBC_Loop([
    'post_type' => 'product',
    'posts_per_page' => 12
]);

echo '<div class="row">';
while ($loop->have_posts()) : $loop->the_post();
    $loop->render_template('card-product');
endwhile;
echo '</div>';
```

---

## Performance Optimization

### 1. Query Caching

```php
// Enable caching
$loop = new WPBC_Loop([
    'post_type' => 'post',
    'posts_per_page' => 10,
    'cache' => true,
    'cache_time' => 3600 // 1 hour
]);
```

### 2. Optimize Queries

```php
// Only get what you need
$loop = new WPBC_Loop([
    'fields' => 'ids',           // Only get post IDs
    'no_found_rows' => true,     // Skip counting total rows
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false
]);
```

### 3. Pagination

```php
// Paginated queries
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$loop = new WPBC_Loop([
    'posts_per_page' => 10,
    'paged' => $paged
]);

// Display pagination
if ($loop->have_posts()) :
    // Loop through posts
    while ($loop->have_posts()) : $loop->the_post();
        // Display posts
    endwhile;
    
    // Pagination
    wpbc_pagination($loop->get_query());
endif;
```

### 4. Lazy Loading

```php
// Implement lazy loading
$loop = new WPBC_Loop([
    'posts_per_page' => 6,
    'meta_key' => '_thumbnail_id' // Only posts with featured images
]);

while ($loop->have_posts()) : $loop->the_post(); ?>
    <img data-src="<?php the_post_thumbnail_url(); ?>" 
         class="lazyload" 
         alt="<?php the_title(); ?>">
<?php endwhile;
```

---

## Real-World Examples

### 1. Featured Posts Slider

```php
// Get featured posts for slider
$featured_loop = new WPBC_Loop([
    'post_type' => 'post',
    'posts_per_page' => 5,
    'meta_query' => [
        [
            'key' => 'featured',
            'value' => 'yes'
        ]
    ]
]);

if ($featured_loop->have_posts()) : ?>
    <div id="featuredSlider" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php $i = 0; while ($featured_loop->have_posts()) : $featured_loop->the_post(); ?>
                <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                    <img src="<?php the_post_thumbnail_url('full'); ?>" 
                         class="d-block w-100" alt="<?php the_title(); ?>">
                    <div class="carousel-caption">
                        <h2><?php the_title(); ?></h2>
                        <?php the_excerpt(); ?>
                    </div>
                </div>
            <?php $i++; endwhile; ?>
        </div>
        <button class="carousel-control-prev" type="button" 
                data-bs-target="#featuredSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" 
                data-bs-target="#featuredSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
<?php endif;
$featured_loop->reset();
```

### 2. Filterable Portfolio

```php
// Get all portfolio items
$portfolio_loop = new WPBC_Loop([
    'post_type' => 'portfolio',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC'
]);

// Get categories for filters
$categories = get_terms('portfolio_category');
?>

<div class="portfolio-section">
    <!-- Filter Buttons -->
    <div class="filter-buttons text-center mb-4">
        <button class="btn btn-outline-primary active" data-filter="*">All</button>
        <?php foreach ($categories as $cat) : ?>
            <button class="btn btn-outline-primary" 
                    data-filter=".<?php echo $cat->slug; ?>">
                <?php echo $cat->name; ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <!-- Portfolio Grid -->
    <div class="portfolio-grid row">
        <?php while ($portfolio_loop->have_posts()) : $portfolio_loop->the_post();
            $terms = get_the_terms(get_the_ID(), 'portfolio_category');
            $term_classes = wp_list_pluck($terms, 'slug');
            ?>
            <div class="portfolio-item col-md-4 mb-4 <?php echo implode(' ', $term_classes); ?>">
                <?php wpbc_component('portfolio-card'); ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
// Initialize Isotope filtering
jQuery(document).ready(function($) {
    var $grid = $('.portfolio-grid').isotope({
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows'
    });
    
    $('.filter-buttons button').on('click', function() {
        var filterValue = $(this).attr('data-filter');
        $grid.isotope({ filter: filterValue });
        
        $('.filter-buttons button').removeClass('active');
        $(this).addClass('active');
    });
});
</script>
```

### 3. Load More Posts with AJAX

```php
// Initial posts load
$loop = new WPBC_Loop([
    'posts_per_page' => 6,
    'paged' => 1
]);
?>

<div id="posts-container" class="row">
    <?php while ($loop->have_posts()) : $loop->the_post(); ?>
        <div class="col-md-4 mb-4">
            <?php wpbc_component('card'); ?>
        </div>
    <?php endwhile; ?>
</div>

<?php if ($loop->get_query()->max_num_pages > 1) : ?>
    <div class="text-center">
        <button id="load-more" class="btn btn-primary" 
                data-page="1" 
                data-max="<?php echo $loop->get_query()->max_num_pages; ?>">
            Load More Posts
        </button>
    </div>
<?php endif; ?>

<script>
jQuery('#load-more').on('click', function() {
    var button = jQuery(this);
    var page = button.data('page');
    var maxPages = button.data('max');
    
    button.text('Loading...').prop('disabled', true);
    
    jQuery.ajax({
        url: wpbc_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'load_more_posts',
            page: page + 1,
            nonce: wpbc_ajax.nonce
        },
        success: function(response) {
            jQuery('#posts-container').append(response);
            button.data('page', page + 1);
            
            if (page + 1 >= maxPages) {
                button.hide();
            } else {
                button.text('Load More Posts').prop('disabled', false);
            }
        }
    });
});
</script>
```

---

## Loop Helper Functions

### wpbc_loop()

Quick loop helper:

```php
// Shorthand loop
wpbc_loop(['posts_per_page' => 5], 'card');

// Equivalent to:
$loop = new WPBC_Loop(['posts_per_page' => 5]);
while ($loop->have_posts()) : $loop->the_post();
    $loop->render_template('card');
endwhile;
```

### wpbc_query_posts()

Get posts array:

```php
$posts = wpbc_query_posts([
    'post_type' => 'product',
    'posts_per_page' => 10
]);

foreach ($posts as $post) {
    echo $post->post_title;
}
```

---

## Best Practices

1. **Always reset loops** when using custom queries
2. **Use caching** for expensive queries
3. **Optimize meta queries** by indexing meta keys
4. **Limit posts_per_page** to reasonable numbers
5. **Use pagination** for large result sets
6. **Prefetch related data** to avoid N+1 queries
7. **Use transients** for complex queries

---

## Next Steps

1. 📕 **[Bootstrap Components](bootstrap-components.md)** - UI component integration
2. 📓 **[API Development](api-development.md)** - Create REST endpoints
3. 📘 **[Getting Started](getting-started.md)** - Framework basics

---

**Master The Loop and unlock WordPress's full potential! 🔄**
