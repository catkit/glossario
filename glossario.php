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
include( dirname( __FILE__ ) . '/inc/poparser.php' );

class Glossario {

	public static $slug         = 'glossario';
	public static $post_term    = 'glossario_term';
	public static $post_po_file = 'glossario_po_file';
	public static $tax_language = 'glossario_term_language';
	public static $tax_class    = 'glossario_term_class';
	public static $tax_status   = 'glossario_term_status';

	function Glossario() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'the_posts', array( $this, 'the_posts' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_action( 'wp_ajax_glossario', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_glossario', array( $this, 'wp_ajax' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
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
		$term_meta_box = array(
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
		new Glossario_Metabox( $term_meta_box );

		$po_file_meta_box = array(
			'id' => Glossario::$slug . '_po_file_info',
			'title' => __( 'PO file info', 'glossario' ),
			'post_types' => array( Glossario::$post_po_file ),
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
				array(
					'type' => 'text',
					'id' => Glossario::$slug . '_po_file_url',
					'name' => __( 'PO file URL', 'glossario' ),
					'desc' => __( 'The URL where this PO file can be fetched.', 'glossario' ),
					'std' => ''
				),
			),
		);
		new Glossario_Metabox( $po_file_meta_box );
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

	/**
	 * Get glossary terms based of SQL joins for each post_meta
	 *
	 * Refer to $defaults variable for default values
	 *
	 * @param iDisplayLength query limit
	 * @param iDisplayStart  query offset
	 * @param sSearch        term search string across all values
	 * @param orderby        query order by field
	 * @param count          return number of found terms instead of objects
	 * @param term_id        get specific term
	 *
	 * @return int           number of posts found if 'count' is set
	 * @return array         objects found if 'count' is not set
	 */
	function get_terms( $args = false ) {

		global $wpdb;

		$defaults = array(
			'iDisplayLength' => 100,
			'iDisplayStart' => 0,
			'sSearch' => false,
			'orderby' => 'post_title',
			'count' => false,
			'term_id' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		$select = "
			SELECT
				p.ID          AS 'term_id',
				os.meta_value AS 'original_term_singular',
				op.meta_value AS 'original_term_plural',
				ts.meta_value AS 'term_singular',
				tp.meta_value AS 'term_plural' ";

		$from = "
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

		if ( !empty( $args['sSearch'] ) )
			$where .= $wpdb->prepare( " AND (
				os.meta_value LIKE '%%%s%%'
				OR op.meta_value LIKE '%%%s%%'
				OR ts.meta_value LIKE '%%%s%%'
				OR tp.meta_value LIKE '%%%s%%' ) ",
				$args['sSearch'], $args['sSearch'],
				$args['sSearch'], $args['sSearch'] );

		if ( !empty( $args['term_id'] ) )
			$where .= $wpdb->prepare( " AND p.ID = '%d' ", $args['term_id'] );

		$orderby = $wpdb->prepare( " ORDER BY %s ", $args['orderby'] );

		if ( $args['iDisplayLength'] == -1 )
			$limit = '';
		else
			$limit = $wpdb->prepare( " LIMIT %d, %d ",
				$args['iDisplayStart'],
				$args['iDisplayLength'] );

		if ( $args['count'] ) {
			$sql = "SELECT COUNT(p.ID) " . $from . $join . $where;
			return $wpdb->get_var( $sql );
		}

		$sql = $select . $from . $join . $where . $orderby . $limit;
		return $wpdb->get_results( $sql );
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
				<th class="term-plural"><?php _e( 'Permalink', 'glossario' ); ?></th>
			</tr>
			</thead>

		</table>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#<?php echo $id; ?>').dataTable({
					'bProcessing': true,
					'bServerSide': true,
					'iDisplayLength': 100,
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

			$terms = $this->get_terms( $_GET );
			$_GET['count'] = true;
			$count = $this->get_terms( $_GET );

			$response = array(
				'iTotalRecords' => $count,
				'iTotalDisplayRecords' => $count,
			);

			foreach ( $terms as $term ) {
				$permalink = get_permalink( $term->term_id );
				$link = '<a href="' . $permalink . '">%s</a>';
				$response['aaData'][] = array(
					$term->original_term_singular,
					$term->original_term_plural,
					$term->term_singular,
					$term->term_plural,
					sprintf( $link, __( 'Permalink', 'glossario' ) )
				);
			}

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

		if ( $found ) {
			wp_enqueue_script( 'jquery-data-tables',  plugins_url() . '/'. Glossario::$slug . '/js/jquery-data-tables.min.js', array( 'jquery' ) );
			wp_enqueue_style( 'glossario', plugins_url() . '/' . Glossario::$slug . '/css/glossario.css' );
		}

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

		$occurrences = get_post_meta( $post->ID, Glossario::$slug . '_po_files_entries', true );

		$po_files = array();
		foreach( $occurrences as $k => $v ) {
			if ( ! $p = get_post( $k ) )
				continue;
			$po_files[ $k ] = $p;
		}

		$po_line_keys = array( 'tcomment', 'ccomment' );

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

		<?php if ( !empty( $po_files ) ) : ?>

			<h3><?php _e( 'Project occurrences', 'glossario' ); ?></h3>

			<?php foreach ( $po_files as $po_file ) : ?>

				<div class="<?php echo Glossario::$slug; ?>-project-ccurrence">

				<h4><a href="<?php echo get_post_meta( $po_file->ID, Glossario::$slug . '_po_file_url', true ); ?>"><?php echo $po_file->post_title; ?></a></h4>

				<?php foreach( $occurrences[ $po_file->ID ] as $occurrence ) : ?>

					<table class="<?php echo Glossario::$slug; ?>-msgid-msgstr">
					<?php for ($i = 0; $i < count( $occurrence['msgid'] ); $i++ ) : ?>
						<tr>
						<td width="50%"><?php echo $occurrence['msgid'][ $i ]; ?></td>
						<td width="50%"><?php echo $occurrence['msgstr'][ $i ]; ?></td>
						</tr>
					<?php endfor; ?>
					</table>

					<?php if ( ! empty( $occurrence['fuzzy'] ) || !empty( $occurrence['obsolete'] ) ) : ?>
						<p class="<?php echo Glossario::$slug; ?>-status">
							<?php if ( ! empty( $occurrence['fuzzy'] ) ) : ?><strong class="fuzzy"><?php _e( 'Fuzzy', 'glossario' ); ?></strong><?php endif; ?>
							<?php if ( ! empty( $occurrence['obsolete'] ) ) : ?><strong class="obsolete"><?php _e( 'Obsolete', 'glossario' ); ?></strong><?php endif; ?>
						</p>
					<?php endif; ?>

					<?php if ( ! empty( $occurrence['msgctxt'] ) ) : ?>
						<p class="<?php echo Glossario::$slug; ?>-msgctxt"><?php _e( 'Disambiguation context', 'glossario' ); ?>: <?php echo implode( ', ', $occurrence['msgctxt'] ); ?></p>
					<?php endif; ?>

					<?php foreach( $po_line_keys as $key ) : ?>
						<?php if ( ! empty( $occurrence[$key] ) ) : ?>
							<?php foreach ( $occurrence[$key] as $value ) : ?>
								<p class="<?php echo Glossario::$slug . '-' . $key; ?>"><?php echo $value; ?></p>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php if ( ! empty( $occurrence['reference'] ) ) : ?>
						<pre><?php echo implode( "\n", $occurrence['reference'] ); ?></pre>
					<?php endif; ?>

				<?php endforeach; ?>

			<?php endforeach; ?>

			</div>

		<?php endif; ?>


		<?php
		return ob_get_clean();
	}

	function parse_po_file( $post_id ) {

		$po_file = get_post_meta( $post_id, Glossario::$slug . '_po_file_url', true );
		if ( ! $f = wp_remote_get( $po_file ) )
			return false;

		if ( is_wp_error( $f ) || $f['response']['code'] != 200 )
			return false;

		$po_tmp_file = tempnam( sys_get_temp_dir(), Glossario::$slug . '-po-file' );
		if ( ! @file_put_contents( $po_tmp_file, $f['body'], FILE_TEXT ) )
			return false;

		$po_parser = new Sepia\PoParser();
		if ( ! $po_entries = $po_parser->read( $po_tmp_file ) )
			return false;

		update_post_meta( $post_id, Glossario::$slug . '_po_file_entries', $po_entries );
		return count( $po_entries );

	}

	/**
	 * Look for term occurrences in the PO files and save results to database.
	 *
	 * @param term_id      Search occurrences for only this term_id. Will
	 *                     search in all terms if not provided
	 *
	 * @return int         Number of occurrences found
	 */
	function update_term_occurrences( $term_id = false ) {

		$args = array( 'iDisplayLength' => -1 );
		if ( $term_id )
			$args['term_id'] = $term_id;

		if ( ! $terms = Glossario::get_terms( $args ) )
			return false;

		$search_relation = array(
			'original_term_singular' => 'msgid',
			'original_term_plural' => 'msgid',
			'term_singular' => 'msgstr',
			'term_plural' => 'msgstr',
		);

		$replace = '<strong class="glossario-match">\1</strong>';
		$replace_count = 0;

		foreach( $terms as $term ) {

			$term = get_object_vars( $term );
			$matches = array();

			// Examine only the PO files of the same language
			if ( ! $languages = wp_get_post_terms( $term['term_id'], Glossario::$tax_language ) )
				continue;

			$languages_ids = array();
			foreach ( $languages as $language ) {
				$languages_ids[] = $language->term_id;
			}

			$po_files = new WP_Query( array(
				'post_type' => Glossario::$post_po_file,
				'nopaging' => true,
				'tax_query' => array(
					array(
						'taxonomy' => Glossario::$tax_language,
						'field' => 'id',
						'terms' => $languages_ids,
						'operator' => 'IN'
					),
				),
			) );

			foreach( $po_files->posts as $po_file ) {

				// No messages in this PO file
				$entries = get_post_meta( $po_file->ID, Glossario::$slug . '_po_file_entries', true );
				if ( empty( $entries ) || ! is_array( $entries ) )
					continue;

				// Search for the original terms in all entries
				foreach ( $entries as $entry ) {

					$found = false;
					$replaced = $entry;

					foreach( $search_relation as $term_attr => $entry_attr ) {

						// Term not set for this attribute
						if ( empty( $term[ $term_attr ] ) )
							continue;

						// Replace patterns in each entry
						$pattern = '/\b(' . $term[ $term_attr ] . ')\b/i';
						for ( $i = 0; $i < count( $replaced[ $entry_attr ] ); $i++ ) {
							$subject = $replaced[ $entry_attr ][ $i ];
							$replaced[ $entry_attr ][ $i ] = preg_replace( $pattern, $replace, $subject, -1, $count );
							if ( $count ) {
								$found = true;
								$replace_count++;
							}
						}

					}

					// Store replacements if any was made
					if ( $found ) {
						$matches[ $po_file->ID ][] = $replaced;
					}

				}
			}

			update_post_meta( $term['term_id'], Glossario::$slug . '_po_files_entries', $matches );

		}

		return $replace_count;

	}

	/**
	 * Check if there is any 'PO file' registered under the 'PO File' post type
	 * 
	 * @return bool Whether PO files exist
	 */
	function po_files_exist() {
		return (bool) get_posts( array( 'post_type' => Glossario::$post_po_file ) );
	}

	function admin_notices() {
		if ( ! $notice = get_transient( Glossario::$slug . '_admin_notices' ) )
			return false;
		delete_transient( Glossario::$slug . '_admin_notices' );
		?>
		<div class="<?php echo $notice['class']; ?>">
			<p><?php echo $notice['message']; ?></p>
		</div>
		<?php

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
