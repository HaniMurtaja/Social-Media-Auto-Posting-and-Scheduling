<?php

namespace FSPoster\App\Pages\Base\Controllers;

use FSPoster\App\Providers\Pages;

class App
{
	public function install ()
	{
		wp_register_script( 'fsp-install', Pages::asset( 'Base', 'js/fsp-install.js' ), [ 'jquery', 'fsp' ], NULL );
		wp_enqueue_script( 'fsp-install' );

		Pages::view( 'Base', 'install' );
	}

	public function update ()
	{
		wp_register_script( 'fsp-update', Pages::asset( 'Base', 'js/fsp-update.js' ), [ 'jquery', 'fsp' ], NULL );
		wp_enqueue_script( 'fsp-update' );

		Pages::view( 'Base', 'update' );
	}

	public function disable ()
	{
		wp_register_script( 'fsp-disable', Pages::asset( 'Base', 'js/fsp-disable.js' ), [ 'jquery', 'fsp' ], NULL );
		wp_enqueue_script( 'fsp-disable' );

		Pages::view( 'Base', 'disable' );
	}
}