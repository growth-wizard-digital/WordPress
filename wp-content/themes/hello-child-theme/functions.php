<?php

/* Function to enqueue stylesheet from parent theme */

function child_enqueue__parent_scripts() {

    wp_enqueue_style( 'parent', get_template_directory_uri().'/style.css' );

}
add_action( 'wp_enqueue_scripts', 'child_enqueue__parent_scripts' );


// Reorder and set columns for all post types
function customize_all_columns($columns) {
    $new_columns = array();
    
    // Always keep checkbox for bulk actions if it exists
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }

    // Set our desired order
    $new_columns['title'] = 'Title';
    $new_columns['post_id'] = 'Post ID';
    $new_columns['post_name'] = 'Slug';
    $new_columns['author'] = 'Author';
    $new_columns['date'] = 'Date';
    
    // Add language columns if they exist
    if (isset($columns['language_en'])) {
        $new_columns['language_en'] = 'English';
    }
    if (isset($columns['language_es'])) {
        $new_columns['language_es'] = 'Español';
    }

    return $new_columns;
}

// Populate the Post ID column
function populate_custom_columns($column_name, $post_id) {
    if ($column_name === 'post_name') {
        // Always get the post slug
        $slug = get_post_field('post_name', $post_id);

        // Only do Polylang-specific code if pll_get_post_language() exists
        if ( function_exists( 'pll_get_post_language' ) ) {
            $lang = pll_get_post_language($post_id, 'slug');
            if ($lang && $lang !== 'en') {
                echo '/' . $lang . '/' . $slug;
            } else {
                echo '/' . $slug;
            }
        } else {
            // Fallback if Polylang is not active
            echo '/' . $slug;
        }
    } elseif ($column_name === 'post_id') {
        echo $post_id;
    }
}

// Make columns sortable
function make_custom_columns_sortable($sortable_columns) {
    $sortable_columns['post_name'] = 'post_name';
    $sortable_columns['post_id'] = 'ID';
    return $sortable_columns;
}

// Initialize columns for all post types
function initialize_custom_columns() {
    $post_types = array_merge(['post', 'page'], get_post_types(['_builtin' => false]));
    
    foreach ($post_types as $post_type) {
        // Add and reorder columns
        add_filter("manage_{$post_type}_posts_columns", 'customize_all_columns');
        // Make columns sortable
        add_filter("manage_edit-{$post_type}_sortable_columns", 'make_custom_columns_sortable');
        // Populate custom columns
        add_action("manage_{$post_type}_posts_custom_column", 'populate_custom_columns', 10, 2);
    }
    
    // Special case for pages
    add_filter('manage_pages_columns', 'customize_all_columns');
    add_filter('manage_edit-page_sortable_columns', 'make_custom_columns_sortable');
}
add_action('admin_init', 'initialize_custom_columns');

// Handle the sorting
add_action('pre_get_posts', 'handle_custom_columns_sorting');
function handle_custom_columns_sorting($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');
    
    if ('post_name' === $orderby) {
        $query->set('orderby', 'post_name');
    } elseif ('ID' === $orderby) {
        $query->set('orderby', 'ID');
    }
}
function add_column_styles() {
    echo '<style>
        .column-post_id {
            width: 80px !important;
            max-width: 80px !important;
        }
        /* Optional: center the ID in the column */
        .column-post_id {
            text-align: center;
        }
    </style>';
}
add_action('admin_head-edit.php', 'add_column_styles');




