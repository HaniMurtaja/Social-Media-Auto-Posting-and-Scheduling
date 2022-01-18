<?php

namespace FSPoster\App\Pages\Share\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\ShareService;

trait Ajax
{
	public function share_post ()
	{
		$post_id = Request::post( 'id', 0, 'num' );

		if ( ! ( $post_id && $post_id > 0 ) )
		{
			exit();
		}

		$feedId = (int) $post_id;

		$res = ShareService::post( $feedId );

		Helper::response( TRUE, [ 'result' => $res ] );
	}

	public function share_saved_post ()
	{
		$post_id         = Request::post( 'post_id', '0', 'num' );
		$nodes           = Request::post( 'nodes', [], 'array' );
		$background      = ! ( Request::post( 'background', '0', 'string' ) ) ? 0 : 1;
		$custom_messages = Request::post( 'custom_messages', [], 'array' );
		$retried         = Request::post( 'retried', '0', 'num', [ '1' ] );
		$sharedFrom      = Request::post( 'shared_from', FALSE, 'string',
			[
				'manual_share',
				'direct_share',
				'schedule',
				'auto_post',
				'manual_share_retried',
				'direct_share_retried',
				'schedule_retried',
				'auto_post_retried'
			] );

		if ( $retried === 1 && $sharedFrom && ! strpos( $sharedFrom, 'retried' ) )
		{
			$sharedFrom .= '_retried';
		}

		$nodes = Pages::action('Base', 'groups_to_nodes', ['node_list' => $nodes]);

		if ( empty( $nodes )  )
		{
			Helper::response( FALSE, 'No account is selected, or no account is added to the selected group(s)' );
		}

		if ( empty( $post_id ) || $post_id <= 0 )
		{
			Helper::response( FALSE );
		}

		if ( ! ShareService::insertFeeds( $post_id, get_current_user_id(), $nodes, $custom_messages, FALSE, NULL, $sharedFrom, $background, NULL, TRUE ) )
		{
			Helper::response( FALSE, fsp__( 'There isn\'t any active account or community to share the post!' ) );
		}

		Helper::response( TRUE );
	}

	public function get_feed_details ()
	{
		$feedId = Request::post( 'feed_id', '0', 'num' );

		if ( empty( $feedId ) || $feedId <= 0 )
		{
			Helper::response( FALSE );
		}

		$feed = DB::fetch( 'feeds', [
			'id' => $feedId
		] );

		if ( ! $feed )
		{
			Helper::response( FALSE );
		}

		$result = [
			'post_id'        => $feed[ 'post_id' ],
			'nodes'          => [ $feed[ 'driver' ] . ':' . $feed[ 'node_type' ] . ':' . $feed[ 'node_id' ] ],
			'customMessages' => [],
			'sharedFrom'    => $feed[ 'shared_from' ]
		];

		if ( ! is_null( $feed[ 'custom_post_message' ] ) )
		{
			$result[ 'customMessages' ][ $feed[ 'driver' ] ] = $feed[ 'custom_post_message' ];
		}
		else
		{
			$result[ 'customMessages' ][ $feed[ 'driver' ] ] = Helper::getOption( 'post_text_message_' . $feed[ 'driver' ], "{title}" );
		}

		DB::DB()->delete( DB::table( 'feeds' ), [ 'id' => $feed[ 'id' ] ] );

		Helper::response( TRUE, [ 'result' => $result ] );
	}

	public function manual_share_save ()
	{
		$id      = Request::post( 'id', '0', 'num' );
		$title   = Request::post( 'title', '', 'string' );
		$link    = Request::post( 'link', '', 'string' );
		$message = Request::post( 'message', '', 'array' );
		$image   = Request::post( 'image', '0', 'num' );
		$nodes   = Request::post( 'nodes', [], 'array' );
		$tmp     = Request::post( 'tmp', '0', 'num', [ '0', '1' ] );

		$nodes = Pages::action('Base', 'groups_to_nodes', ['node_list' => $nodes]);

		$sqlData = [
			'post_type'      => 'fs_post' . ( $tmp ? '_tmp' : '' ),
			'post_content'   => addslashes( json_encode( $message ) ),
			'post_status'    => 'publish',
			'post_title'     => $title,
			'comment_status' => 'closed',
			'ping_status'    => 'closed'
		];

		if ( $id > 0 )
		{
			$sqlData[ 'ID' ] = $id;

			wp_insert_post( $sqlData );

			delete_post_meta( $id, '_fs_link' );
			delete_post_meta( $id, '_thumbnail_id' );
		}
		else
		{
			$id = wp_insert_post( $sqlData );
		}

		if ( ! empty( $nodes ) )
		{
			if ( metadata_exists( 'post', $id, '_fs_poster_node_list' ) )
			{
				update_post_meta( $id, '_fs_poster_node_list', $nodes );
			}
			else
			{
				add_post_meta( $id, '_fs_poster_node_list', $nodes, TRUE );
			}
		}
		else
		{
			delete_post_meta( $id, '_fs_poster_node_list' );
		}

		add_post_meta( $id, '_fs_link', $link );

		if ( $image > 0 )
		{
			add_post_meta( $id, '_thumbnail_id', $image );
		}
		else
		{
			delete_post_meta( $id, '_thumbnail_id' );
		}

		Helper::response( TRUE, [ 'id' => $id ] );
	}

	public function manual_share_delete ()
	{
		$id = Request::post( 'id', '0', 'num' );

		if ( ! ( $id > 0 ) )
		{
			Helper::response( FALSE );
		}

		$currentUserId = get_current_user_id();

		$checkPost = DB::DB()->get_row( 'SELECT * FROM ' . DB::WPtable( 'posts', TRUE ) . " WHERE post_type='fs_post' AND post_author='{$currentUserId}' AND ID='{$id}'", ARRAY_A );

		if ( ! $checkPost )
		{
			Helper::response( FALSE, fsp__( 'Post not found!' ) );
		}

		delete_post_meta( $id, '_fs_link' );
		delete_post_meta( $id, '_thumbnail_id' );
		delete_post_meta( $id, '_fs_poster_node_list' );
		wp_delete_post( $id );

		Helper::response( TRUE, [ 'id' => $id ] );
	}

	public function check_post_is_published ()
	{
		$id = Request::post( 'id', '0', 'num' );

		$postStatus = get_post_status( $id );
		$feeds      = DB::DB()->get_row( DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'feeds' ) . ' WHERE blog_id = %d AND is_sended = %d AND post_id = %d AND send_time >= %s  AND share_on_background = %d', [
			Helper::getBlogId(),
			0,
			$id,
			Date::dateTimeSQL( '-30 seconds' ),
			0
		] ), ARRAY_A );

		if ( $postStatus === 'publish' && $feeds != NULL )
		{
			$status = '2';
		}
		else if ( $postStatus === 'publish' )
		{
			$status = '1';
		}
		else
		{
			$status = FALSE;
		}

		Helper::response( TRUE, [
			'post_status' => $status
		] );
	}

	public function share_on_bg_paused_feeds ()
	{
		DB::DB()->update( DB::table( 'feeds' ), [
			'share_on_background' => 1
		], [ 'share_on_background' => 0, 'is_sended' => 0, 'blog_id' => Helper::getBlogId() ] );

		Helper::response( TRUE, [ 'message' => fsp__( 'Posts will be shared on background!' ) ] );
	}

	public function do_not_share_paused_feeds ()
	{
		DB::DB()->delete( DB::table( 'feeds' ), [
			'share_on_background' => 0,
			'is_sended'           => 0,
			'blog_id'             => Helper::getBlogId()
		] );

		Helper::response( TRUE );
	}

	public function fs_clear_saved_posts ()
	{
		$user_id     = get_current_user_id();
		$saved_posts = DB::DB()->get_results( "SELECT ID FROM " . DB::WPtable( 'posts', TRUE ) . " WHERE post_type = 'fs_post' AND post_author=" . $user_id, ARRAY_A );

		foreach ( $saved_posts as $post )
		{
			delete_post_meta( $post[ 'ID' ], '_fs_link' );
			delete_post_meta( $post[ 'ID' ], '_thumbnail_id' );
			wp_delete_post( $post[ 'ID' ] );
		}

		Helper::response( TRUE );
	}

	public function get_fs_posts ()
	{
		$posts = DB::DB()->get_results( 'SELECT * FROM ' . DB::WPtable( 'posts', TRUE ) . " WHERE post_type='fs_post' AND post_author='" . get_current_user_id() . "' ORDER BY ID DESC", ARRAY_A );

		$html = '';
		foreach ( $posts as $post )
		{
			$title = get_the_title( $post[ 'ID' ] );
			$html .= '<div class="fsp-share-post" data-id="' . (int) $post[ 'ID' ] . '">
						<div class="fsp-share-post-id">
							' . (int) $post[ 'ID' ] . '
						</div>
						<div class="fsp-share-post-title">
							<a href="?page=fs-poster-share&post_id=' . $post[ 'ID' ] . '">' . htmlspecialchars( Helper::cutText( $title ) ) . ' </a>
						</div>
						<div class="fsp-share-post-date">
							' . Date::dateTime( $post[ 'post_date' ] ) . '
						</div>
						<div class="fsp-share-post-controls">
							<i class="far fa-trash-alt fsp-tooltip fsp-icon-button delete_post_btn" data-title="' . fsp__( 'Delete the post' ) . '"></i>
						</div>
					</div>';
		}

		Helper::response( TRUE, [
			'status' => 'ok',
			'html'   => $html,
			'count'  => count($posts)
		] );
	}
}