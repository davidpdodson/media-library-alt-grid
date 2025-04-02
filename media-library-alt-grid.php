<?php
/**
 * Plugin Name: Media Library Alt Table
 * Plugin URI: https://example.com
 * Description: Adds an admin page that shows a paginated table of media library images with their title and alt tags.
 * Version: 1.2
 * Author: Dave Dodson
 * Author URI: https://example.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a new admin menu page.
 */
function mla_table_add_admin_page() {
	add_menu_page(
		'Media Alt Table',           // Page title.
		'Media Alt Table',           // Menu title.
		'manage_options',            // Capability.
		'media-alt-table',           // Menu slug.
		'mla_table_display_page',    // Callback function.
		'dashicons-format-gallery',  // Icon.
		20                           // Position.
	);
}
add_action( 'admin_menu', 'mla_table_add_admin_page' );

/**
 * Display the admin page content.
 */
function mla_table_display_page() {
	// Number of images per page.
	$per_page = 12;
	// Get the current page number.
	$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

	// Query for image attachments.
	$args = array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		'post_status'    => 'inherit',
	);

	$query = new WP_Query( $args );

	echo '<div class="wrap">';
	echo '<h1>Media Library Alt Table</h1>';

	if ( $query->have_posts() ) {
		// Start table.
		echo '<table class="widefat fixed striped">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Image</th>';
		echo '<th>Title</th>';
		echo '<th>Alt Tag</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id  = get_the_ID();
			$title    = get_the_title();
			$alt_text = get_post_meta( $post_id, '_wp_attachment_image_alt', true );
			$image    = wp_get_attachment_image( $post_id, 'thumbnail' );

			echo '<tr>';
			echo '<td>' . $image . '</td>';
			echo '<td>' . esc_html( $title ) . '</td>';
			echo '<td>' . esc_html( $alt_text ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';

		// Pagination.
		// Build the base URL using the admin URL and current page.
		$page_url = admin_url( 'admin.php?page=media-alt-table' );
		$pagination = paginate_links( array(
			'base'    => add_query_arg( 'paged', '%#%', $page_url ),
			'format'  => '',
			'current' => $paged,
			'total'   => $query->max_num_pages,
			'type'    => 'list',
		) );
		echo $pagination;
	} else {
		echo '<p>No images found.</p>';
	}

	echo '</div>';

	// Restore original post data.
	wp_reset_postdata();
}
