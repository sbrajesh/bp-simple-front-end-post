<?php

/**
 * A Taxonomy Walker class to fix the input name of the taxonomy terms
 * 
 */
class BPSimplePostTermsChecklistWalker extends Walker {

	public $tree_type = 'category';
	public $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' ); //TODO: decouple this

	public function start_lvl ( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children'>\n";
	}

	public function end_lvl ( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	public function start_el ( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		
		extract( $args );
		
		if ( empty( $taxonomy ) ) {
			$taxonomy = 'category';
		}

		$name = 'tax_input[' . $taxonomy . ']';

		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats, false ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
	}

	public function end_el ( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}

}
