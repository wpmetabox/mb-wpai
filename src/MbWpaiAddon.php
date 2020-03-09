<?php
namespace MBWPAI;

use MBWPAI\RapidAddon as Addon;

class MbWpaiAddon {
    public function __construct() {
		add_action( 'init', [ $this, 'run' ] );
    }
    
    public function run() {
        $mb_wpai = new Addon( 'Meta Box Add-On', 'mb_wpai' );

        // $mb_wpai->add_field(
		// 	'field_1',
		// 	'SEO Title',
		// 	'text'
		// );

		// $mb_wpai->add_field(
		// 	'field_2',
		// 	'Meta Description',
		// 	'text'
		// );

		// $mb_wpai->add_field(
		// 	'field_3',
		// 	'Meta Robots Index',
		// 	'radio',
		// 	[ 
		// 		'' => 'default',
		// 		'1' => 'noindex',
		// 		'2' => 'index'
		// 	]
		// );

		// $mb_wpai->add_field(
		// 	'field_4',
		// 	'Facebook Image',
		// 	'image'
		// );
        $mb_wpai->run();
    }
}