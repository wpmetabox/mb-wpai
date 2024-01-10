<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\Fields\FieldHandler;

class TaxonomyHandler extends FieldHandler {
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
			'slug'   => sanitize_title( $s ),
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
				$term_id              = $this->get_or_create_term( $value, $taxonomy, $parent['values'][ $i ] ?? null );
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
		$value = parent::get_value();

		$output = [];

		if ( ! is_array( $value ) ) {
			return;
		}

		$taxonomy = $this->field['taxonomy'][0];

		foreach ( $value as $clone_index => $term ) {
			if ( is_string( $term ) ) {
				$term_id     = $this->get_or_create_term( $term, $taxonomy );
				$output[0][] = $term_id;
			} else {
				foreach ( $term as $s ) {
					$term_id = $this->get_or_create_term( $s, $taxonomy );
					if ( ! is_wp_error( $term_id ) ) {
						$output[ $clone_index ][] = $term_id;
					}
				}
			}
		}

		return $output;
	}

	public function saved_post( $importData ) {
		$taxonomy = $this->field['taxonomy'][0];

		$values = $this->get_value();

		foreach ( $values as $clone_index => $term_ids ) {
			if ( ! is_array( $term_ids ) ) {
				wp_set_post_terms( $this->get_post_id(), $term_ids, $taxonomy, true );
				continue;
			}

			foreach ( $term_ids as $term_id ) {
				wp_set_post_terms( $this->get_post_id(), $term_id, $taxonomy, true );
			}
		}
	}
}
