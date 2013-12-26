<?php
/*
 * Plugin Name: Glossario
 * Plugin URI: http://github.com/wpbrasil/glossario
 * Description: A glossary for managing terms in collaborative translations
 * Version: 0.01
 * Author: Brazilian and Portuguese WordPress Communities
 * Author URI: http://github.com/wpbrasil/glossario
 */

include( dirname( __FILE__ ) . '/glossario-metabox.php' );

class Glossario {

	public static $slug           = 'glossario';
	public static $post_term      = 'glossario_term';
	public static $post_po_file   = 'glossario_po_file';
	public static $tax_language   = 'glossario_term_language';
	public static $tax_class      = 'glossario_term_class';
	public static $tax_status     = 'glossario_term_status';

	function Glossario() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'the_posts', array( $this, 'the_posts' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_action( 'wp_ajax_glossario', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_glossario', array( $this, 'wp_ajax' ) );
	}

	function activate() {
		flush_rewrite_rules();
	}

	function deactivate() {
		flush_rewrite_rules();
	}

	function uninstall() {
		// @TODO: remove plugin options data
	}

	function init() {
		$this->register_custom_post_types();
		$this->register_custom_taxonomies();
	}

	function admin_init() {
		$this->add_meta_boxes();
	}

	/**
	 * Add Glossary term info meta box
	 */
	function add_meta_boxes() {
		$meta_box = array(
			'id' => Glossario::$slug . '_term_info',
			'title' => __( 'Glossary term info', 'glossario' ),
			'post_types' => array( Glossario::$post_term ),
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
				array(
					'type' => 'text',
					'id' => Glossario::$slug . '_original_term_singular',
					'name' => __( 'Original term singular', 'glossario' ),
					'desc' => __( 'Non-translated term in its singular form.', 'glossario' ),
					'std' => ''
				), array(
					'type' => 'text',
					'id' => Glossario::$slug . '_original_term_plural',
					'name' => __( 'Original term plural', 'glossario' ),
					'desc' => __( 'Non-translated term in its plural form.', 'glossario' ),
					'std' => ''
				), array(
					'type' => 'text',
					'id' => Glossario::$slug . '_term_singular',
					'name' => __( 'Singular translation', 'glossario' ),
					'desc' => __( 'Translated term in its singular form.', 'glossario' ),
					'std' => ''
				), array(
					'type' => 'text',
					'id' => Glossario::$slug . '_term_plural',
					'name' => __( 'Plural translation', 'glossario' ),
					'desc' => __( 'Translated term in its plural form.', 'glossario' ),
					'std' => ''
				), array(
					'type' => 'textarea',
					'id' => Glossario::$slug . '_translation_notes',
					'name' => __( 'Translation notes', 'glossario' ),
					'desc' => __( 'Optional notes or explanation about the translation above.' ),
					'std' => '',
				),
			),
		);
		new Glossario_Metabox( $meta_box );
	}

	function register_custom_post_types() {
		$post_types = array(
			Glossario::$post_term => array(
				'labels' => array(
					'name' => __( 'Glossary', 'glossario' ),
					'singular_name' => __( 'Glossary', 'glossario' ),
					'add_new' => __( 'Add new glossary term', 'glossario' ),
					'add_new_item' => __( 'Add new glossary term', 'glossario' ),
					'edit_item' => __( 'Edit glossary term', 'glossario' ),
					'view_item' => __( 'View glossary term', 'glossario' ),
					'search_items' => __( 'Search for glossary terms', 'glossario' ),
				),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'rewrite' => array(
					'slug' => 'glossario/term',
					'with_front' => false
				),
				'has_archive' => true,
				'supports' => array( 'comments' )
			),
			Glossario::$post_po_file => array(
				'labels' => array(
					'name' => __( 'PO files', 'glossario' ),
					'singular_name' => __( 'PO file', 'glossario' ),
					'menu_name' => __( 'PO files', 'glossario' ),
					'add_new' => __( 'Add new PO file', 'glossario' ),
					'add_new_item' => __( 'Add new PO file', 'glossario' ),
					'edit_item' => __( 'Edit PO file', 'glossario' ),
					'view_item' => __( 'View PO file', 'glossario' ),
					'search_items' => __( 'Search for PO files', 'glossario' ),
				),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => 'edit.php?post_type=' . Glossario::$post_term,
				'rewrite' => array(
					'slug' => 'glossario/po-file',
					'with_front' => false,
				),
				'has_archive' => true,
				'hierarchical' => false,
				'supports' => array( 'title', 'comments' )
			)
		);
		foreach ( $post_types as $type => $args ) {
			register_post_type( $type, $args );
		}
	}

	function register_custom_taxonomies() {
		$taxonomies = array(
			Glossario::$tax_language => array(
				'object_types' => array( 'glossario_term', 'glossario_po_file' ),
				'labels' => array(
					'name' => __( 'Glossary languages', 'glossario' ),
					'singular_name' => __( 'Glossary language', 'glossario' ),
					'all_items' => __( 'All glossary languages', 'glossario' ),
					'edit_item' => __( 'Edit glossary language', 'glossario' ),
					'view_item' => __( 'View glossary language', 'glossario' ),
					'update_item' => __( 'Update glossary language', 'glossario' ),
					'add_new_item' => __( 'Add new glossary language', 'glossario' ),
					'new_item_name' => __( 'New glossary language', 'glossario' ),
				),
				'hierarchical' => true,
				'show_ui' => true,
				'rewrite' => array( 'slug' => 'glossario/language' ),
			),
			Glossario::$tax_class => array(
				'object_types' => array( 'glossario_term' ),
				'labels' => array(
					'name' => __( 'Morphology classes', 'glossario' ),
					'singular_name' => __( 'Morphology class', 'glossario' ),
					'all_items' => __( 'All morphology classes', 'glossario' ),
					'edit_item' => __( 'Edit morphology class', 'glossario' ),
					'view_item' => __( 'View morphology class', 'glossario' ),
					'update_item' => __( 'Update morphology class', 'glossario' ),
					'add_new_item' => __( 'Add new morphology class', 'glossario' ),
					'new_item_name' => __( 'New morphology class', 'glossario' ),
				),
				'hierarchical' => true,
				'show_ui' => true,
				'rewrite' => array( 'slug' => 'glossario/class' ),
			),
			Glossario::$tax_status => array(
				'object_types' => array( 'glossario_term' ),
				'labels' => array(
					'name' => __( 'Translation status', 'glossario' ),
					'singular_name' => __( 'Translation status', 'glossario' ),
					'all_items' => __( 'All glossary term status', 'glossario' ),
					'edit_item' => __( 'Edit glossary term status', 'glossario' ),
					'view_item' => __( 'View glossary term status', 'glossario' ),
					'update_item' => __( 'Update glossary term status', 'glossario' ),
					'add_new_item' => __( 'Add new glossary term status', 'glossario' ),
					'new_item_name' => __( 'New glossary term status', 'glossario' ),
				),
				'hierarchical' => true,
				'show_ui' => true,
				'rewrite' => array( 'slug' => 'glossario/status' ),
			),
		);
		foreach ( $taxonomies as $taxonomy => $args ) {
			register_taxonomy( $taxonomy, $args['object_types'], $args );
		}
	}

	function get_terms( $args = false ) {

		global $wpdb;

		$defaults = array(
			'iDisplayLength' => 50,
			'iDisplayStart' => 0,
			'iSearch' => false,
			'orderby' => 'post_title'

		);
		$args = wp_parse_args( $args, $defaults );

		$select_from = "
			SELECT
				p.ID          AS 'term_id',
				os.meta_value AS 'original_term_singular',
				op.meta_value AS 'original_term_plural',
				ts.meta_value AS 'term_singular',
				tp.meta_value AS 'term_plural'
			FROM
				{$wpdb->posts} p ";

		$join = "
			LEFT JOIN {$wpdb->postmeta} os ON 1=1
				AND p.ID = os.post_id
				AND os.meta_key = '" . Glossario::$slug . "_original_term_singular'
			LEFT JOIN {$wpdb->postmeta} op ON 1=1
				AND p.ID = op.post_id
				AND op.meta_key = '" . Glossario::$slug . "_original_term_plural'
			LEFT JOIN {$wpdb->postmeta} ts ON 1=1
				AND p.ID = ts.post_id
				AND ts.meta_key = '" . Glossario::$slug . "_term_singular'
			LEFT JOIN {$wpdb->postmeta} tp ON 1=1
				AND p.ID = tp.post_id
				AND tp.meta_key = '" . Glossario::$slug . "_term_plural' ";

		$where = "
			WHERE 1=1
				AND post_type = '" . Glossario::$post_term . "'
				AND post_status = 'publish' ";

		if ( !empty( $args['iSearch'] ) )
			$where .= $wpdb->prepare( "AND (
				original_term_singular LIKE '%%%s%'
				OR original_term_plural LIKE '%%%s%'
				OR term_singular LIKE '%%%s%'
				OR term_plural LIKE '%%%s%' ) ",
				$args['iSearch'], $args['iSearch'],
				$args['iSearch'], $args['iSearch'] );

		$orderby_limit = $wpdb->prepare( "
			ORDER BY %s
			LIMIT %d, %d ",
			$args['orderby'],
			$args['iDisplayStart'],
			$args['iDisplayLength'] );

		$sql = $select_from . $join . $where . $orderby_limit;
		$query = $wpdb->get_results( $sql );
		return $query;
	}

	function shortcode_term_table() {
		$id = Glossario::$slug . '-' . uniqid() . '-term-table';
		ob_start();
		?>
		<table id="<?php echo $id; ?>" class="<?php echo  Glossario::$slug . '-term-table'; ?>">

			<thead>
			<tr clss="term-titles">
				<th class="term-original-singular"><?php _e( 'Original singular', 'glossario' ); ?></th>
				<th class="term-original-plural"><?php _e( 'Original plural', 'glossario' ); ?></th>
				<th class="term-singular"><?php _e( 'Translated singular', 'glossario' ); ?></th>
				<th class="term-plural"><?php _e( 'Translated plural', 'glossario' ); ?></th>
			</tr>
			</thead>

		</table>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#<?php echo $id; ?>').dataTable({
				    'bProcessing': true,
					'bServerSide': true,
					'sPaginationType': 'full_numbers',
					'sAjaxSource': '<?php echo admin_url('admin-ajax.php') . '?action=glossario&call=term_list'; ?>'
				});
			});
		</script>
		<?php
		return ob_get_clean();
	}

	function wp_ajax() {

		if ( empty( $_GET['call'] ) )
			return false;

		if ( 'term_list' == $_GET['call'] ) {

			$response = array(
				'iTotalRecords' => wp_count_posts( Glossario::$post_term )->publish,
				'iTotalDisplayRecords' => wp_count_posts( Glossario::$post_term )->publish,
			);

			foreach ( $this->get_terms( $_GET ) as $term ) {
				$permalink = get_permalink( $term->term_id );
				$link = '<a href="' . $permalink . '">%s</a>';
				$terms[] = array(
					sprintf( $link, $term->original_term_singular ),
					sprintf( $link, $term->original_term_plural ),
					sprintf( $link, $term->term_singular ),
					sprintf( $link, $term->term_plural )
				);
			}

			$response['aaData'] = $terms;
			echo json_encode( $response );
			exit();

		}

	}

	/**
	 * Check if some of the plugin's shortcodes are being used just after the
	 * posts are rewtrieved from the database, and if so, enqueue the scripts
	 * needed.
	 */
	function the_posts( $posts ) {

		if ( empty( $posts ) )
			return $posts;

		$found = false;
		foreach ( $posts as $post ) {
			if ( preg_match( '#\[' . Glossario::$slug . '_#', $post->post_content ) )
				$found = true;
				break;
		}

		if ( $found )
			wp_enqueue_script( 'jquery-data-tables',  plugins_url() . '/'. Glossario::$slug . '/js/jquery-data-tables.min.js', array( 'jquery' ) );

		return $posts;
	}

	/**
	 * Custom content using the plugin fields for the Glossario::$post_term
	 * type
	 */
	function the_content( $content ) {

		global $post;

		if ( Glossario::$post_term != $post->post_type )
			return $content;

		$fields = array(
			'original_term_singular',
			'original_term_plural',
			'term_singular',
			'term_plural',
			'translation_notes',
		);
		foreach ( $fields as $f ) {
			${$f} = get_post_meta( $post->ID, Glossario::$slug . '_' . $f, true );
		}

		ob_start();
		?>
		<h3><?php _e( 'Term translation', 'glossario' ); ?></h3>
		<table class="term-single">

			<?php if ( $original_term_singular && $term_singular ) : ?>
				<tr class="singular">
				<td class="title"><?php _e( 'Singular', 'glossario' ); ?></td>
				<td><?php echo $original_term_singular; ?></td>
				<td><?php echo $term_singular; ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $original_term_plural && $term_plural ) : ?>
				<tr class="plural">
				<td class="title"><?php _e( 'Plural', 'glossario' ); ?></td>
				<td><?php echo $original_term_plural; ?></td>
				<td><?php echo $term_plural; ?></td>
				</tr>
			<?php endif; ?>

		</table>

		<?php if ( $translation_notes ) : ?>
			<h3><?php _e( 'Translation notes', 'glossario' ); ?></h3>
			<div class="translation-notes"><?php echo wpautop( $translation_notes ); ?></div>
		<?php endif; ?>

		<?php // @TODO: List occurences in the PO files ?>

		<?php
		return ob_get_clean();

	}

}

function glossario_plugins_loaded() {
	new Glossario();
}
add_action( 'plugins_loaded', 'glossario_plugins_loaded' );

register_activation_hook( __FILE__, array( 'Glossario', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Glossario', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Glossario', 'uninstall' ) );

add_shortcode( 'glossario_term_table', array( 'Glossario', 'shortcode_term_table' ) );
