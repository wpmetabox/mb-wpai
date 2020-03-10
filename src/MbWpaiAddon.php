<?php
namespace MBWPAI;

use MBWPAI\RapidAddon as Addon;

class MbWpaiAddon extends Addon {
    public function __construct() {
		add_action( 'init', [ $this, 'self_setup' ] );
    }
    
    public function self_setup() {
        $this->add_field(
			'field_1',
			'SEO Title',
			'text'
		);

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
		$this->set_import_function( $this->self_import() );

        $this->run();
	}
	
	public function self_import() {

	}
}