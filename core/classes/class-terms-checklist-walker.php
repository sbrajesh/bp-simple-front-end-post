<?php
/**
 * Taxonomy Walker.
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * A Taxonomy Walker class to fix the input name of the taxonomy terms
 */
class BPSimplePostTermsChecklistWalker extends Walker {

	/**
	 * Tree type.
	 *
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Table fields.
	 *
	 * @var array
	 */
	public $db_fields = array(
		'parent' => 'parent',
		'id'     => 'term_id',
	); // TODO: decouple this.

	/**
	 * Start level override.
	 *
	 * @param string $output output.
	 * @param int    $depth level depth.
	 * @param array  $args other args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * End level override.
	 *
	 * @param string $output output.
	 * @param int    $depth level depth.
	 * @param array  $args other args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start level override.
	 *
	 * @param string   $output output.
	 * @param stdClass $category category object.
	 * @param int      $depth level depth.
	 * @param array    $args other args.
	 * @param int      $id category id.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

		$popular_cats  = isset( $args['popular_cats'] ) ? $args['popular_cats'] : '';
		$selected_cats = isset( $args['selected_cats'] ) ? $args['selected_cats'] : '';
		$taxonomy      = isset( $args['taxonomy'] ) ? $args['taxonomy'] : '';

		if ( empty( $taxonomy ) ) {
			$taxonomy = 'category';
		}

		$name = 'tax_input[' . $taxonomy . ']';

		$class  = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats, false ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
	}

	/**
	 * End Element override.
	 *
	 * @param string   $output output.
	 * @param stdClass $category category object.
	 * @param int      $depth level depth.
	 * @param array    $args other args.
	 */
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}
