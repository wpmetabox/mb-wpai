<?php
// This file isn't included in the code base, but is used for testing purposes.
// Copy contents of this file to functions.php in the theme directory.

add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
    // Movie meta box with cloneable fields
    $meta_boxes[] = [
        'title'      => 'Casts',
        'fields'     => [
            [
                'id' => 'name',
				'name' => 'name',
				'type' => 'text',
				'clone' => true,
			],
            [
                'id' => 'actors',
				'name' => 'actors',
				'type' => 'group',
				'clone' => true,
				'fields' => [
					[
						'id' => 'name',
						'name' => 'name',
						'type' => 'text',
					],
					[
						'id' => 'character',
						'name' => 'character',
						'type' => 'text',
					]
				]
			],
        ],
    ];

    return $meta_boxes;
} );
