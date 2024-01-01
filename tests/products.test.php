<?php
// This file isn't included in the code base, but is used for testing purposes.
// Copy contents of this file to functions.php in the theme directory.
add_filter( 'rwmb_meta_boxes', function ($meta_boxes) {
	// Movie meta box with cloneable fields
	$meta_boxes[] = [ 
		'id' => 'movie-detail',
		'title' => 'Movie Detail',
		'fields' => [
			[
				'id' => 'description',
				'name' => 'Description',
				'type' => 'textarea',
			],
			[
				'id' => 'variants',
				'name' => 'Variants',
				'type' => 'group',
				'clone' => true,
				'fields' => [
					[
						'id' => 'color',
						'name' => 'Color',
						'type' => 'select',
						'options' => [
							'Red' => 'Red',
							'Blue' => 'Blue',
							'Green' => 'Green',
						],
					],
					[
						'id' => 'sku',
						'name' => 'SKU',
						'type' => 'text',
					],
					[
						'id' => 'photos',
						'name' => 'Photos',
						'type' => 'image_advanced',
						'clone' => true,
					],
					[
						'id' => 'reviews',
						'name' => 'Reviews',
						'type' => 'group',
						'clone' => true,
						'fields' => [
							[
								'id' => 'reviewer',
								'name' => 'Reviewer',
								'type' => 'text',
							],
							[
								'id' => 'review',
								'name' => 'Review',
								'type' => 'textarea',
							],
							[
								'id' => 'published',
								'name' => 'Published',
								'type' => 'checkbox',
							]
						]
					]
				],
			],
		],
	];

	return $meta_boxes;
} );