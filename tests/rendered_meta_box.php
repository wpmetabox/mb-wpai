<?php

require_once __DIR__ . '../../../wp-load.php';

$mb = [
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

$meta_box = MetaBox\WPAI\MetaBoxes\MetaBoxFactory::create(
    new RW_Meta_Box(
        $mb
    )
);

$meta_box->view();