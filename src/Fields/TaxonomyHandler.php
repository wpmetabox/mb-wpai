<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\Fields\FieldHandler;
use MetaBox\WPAI\MetaBoxService;

class TaxonomyHandler extends FieldHandler {
	public function parse( $xpath, $parsingData, $args = [] ) {
		$xpath = json_decode( $xpath, true );

		$this->xpath       = $xpath;
		$this->parsingData = $parsingData;
		$this->base_xpath  = $parsingData['xpath_prefix'] . $parsingData['import']['xpath'];
	}

	private function build_tree( array $elements, $parentId = null ) {
		$branch = [];

		foreach ( $elements as $element ) {
			if ( $element['parent_id'] == $parentId ) {
				$children = $this->build_tree( $elements, $element['item_id'] );
				if ( $children ) {
					$element['children'] = $children;
				}
				$branch[] = $element;
			}
		}

		return $branch;
	}

	private function get_or_create_term( $s, string $taxonomy, $parent_id = null ) {
		if ( empty( $s ) ) {
			return;
		}

		$term = term_exists( $s, $taxonomy, $parent_id );

		if ( $term ) {
			return $term['term_id'];
		}

		$term = wp_insert_term( $s, $taxonomy, [ 
			'slug' => sanitize_title( $s ),
			'parent' => $parent_id,
		] );

		if ( is_wp_error( $term ) ) {
			return;
		}

		return $term['term_id'];
	}

	private function fill_tree( $tree, $taxonomy, $parent = null ) {
		foreach ( $tree as $index => $node ) {
			$values = $this->get_value_by_xpath( $node['xpath'] );

			foreach ( $values as $i => $value ) {
				$term_id            = $this->get_or_create_term( $value, $taxonomy, $parent['values'][ $i ] ?? null );
				$node['values'][ $i ] = $term_id;
			}

			if ( isset( $node['children'] ) ) {
				$node['children'] = $this->fill_tree( $node['children'], $taxonomy, $node );
			}

			$tree[ $index ] = $node;
		}

		return $tree;
	}

	/**
	 * Traverse the tree and build the output
	 **/
	private function get_tree_values( $tree, $index ): array {
		$values = [];
		foreach ( $tree as $node ) {
			$values[] = $node['values'][ $index ] ?? '';
			if ( isset( $node['children'] ) ) {
				$values = array_merge( $values, $this->get_tree_values( $node['children'], $index ) );
			}
		}

		return $values;
	}

	public function get_value() {
		$output      = [];
		$taxonomy    = $this->field['taxonomy'][0];
		$this->xpath = $this->build_tree( $this->xpath );
		$output      = $this->fill_tree( $this->xpath, $taxonomy );
		$output      = $this->get_tree_values( $output, $this->get_post_index() );

		return implode( ',', $output );
	}


	public function saved_post( $importData ) {
		$taxonomy = $this->field['taxonomy'][0];
		$values   = explode( ',', $this->get_value() );
		$values   = array_filter( $values );
		$values   = array_unique( $values );

		wp_set_post_terms( $this->get_post_id(), $values, $taxonomy );
	}
}