<?php

namespace FSPoster\App\Pages\Base\Controllers;

use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Ajax
{
	public function save_metabox ()
	{
		$id              = Request::post( 'id', 0, 'int' );
		$share_checked   = Request::post( 'share_checked', 0, 'int', [ 0, 1 ] ) === 1 ? 'on' : 'off';
		$accounts        = Request::post( 'accounts', [], 'array' );
		$custom_messages = Request::post( 'custom_messages', [], 'array' );

		if ( ! ( $id > 0 ) )
		{
			Helper::response( 'ok' );
		}

		update_post_meta( $id, '_fs_is_manual_action', 1 );
		update_post_meta( $id, '_fs_poster_share', $share_checked );
		update_post_meta( $id, '_fs_poster_node_list', $accounts );

		foreach ( $custom_messages as $driver => $cm )
		{
			update_post_meta( $id, '_fs_poster_cm_' . $driver, $cm );
		}

		Helper::response( 'ok' );
	}
}