<?php

namespace FSPoster\App\Libraries\google;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Cookie\CookieJar;
use FSP_GuzzleHttp\Exception\GuzzleException;

class GoogleMyBusiness
{
	private $at;
	private $cookies;
	private $client;
	private $main_page_html;

	public function __construct ( $sid, $hsid, $ssid, $proxy = '' )
	{
		$this->cookies = [
			[
				"Name"     => "SID",
				"Value"    => $sid,
				"Domain"   => ".google.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			],
			[
				"Name"     => "HSID",
				"Value"    => $hsid,
				"Domain"   => ".google.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE
			],
			[
				"Name"     => "SSID",
				"Value"    => $ssid,
				"Domain"   => ".google.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE
			]
		];

		$cookieJar = new CookieJar( FALSE, $this->cookies );

		$this->client = new Client( [
			'cookies'         => $cookieJar,
			'allow_redirects' => [ 'max' => 20 ],
			'proxy'           => empty( $proxy ) ? NULL : $proxy,
			'verify'          => FALSE,
			'http_errors'     => FALSE,
			'headers'         => [ 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0' ]
		] );
	}

	public function getUserInfo ( $html = NULL )
	{
		if ( is_null( $html ) )
		{
			$html = $this->getMainPageHTML();
		}

		$html = str_replace( "\n", "", $html );

		preg_match( '/window\.WIZ_global_data \= (\{.*?\})\;/mi', $html, $matches );
		if ( isset( $matches[ 1 ] ) )
		{
			$jsonInf = json_decode( str_replace( [ '\x', "'", ',]' ], [ '', '"', ']' ], $matches[ 1 ] ), TRUE );

			preg_match( '/url\((https?\:\/\/.+googleusercontent\.com.+)\)/Ui', $html, $profilePhoto );

			$accountId    = isset( $jsonInf[ 'S06Grb' ] ) ? $jsonInf[ 'S06Grb' ] : NULL;
			$accountEmail = isset( $jsonInf[ 'oPEP7c' ] ) ? $jsonInf[ 'oPEP7c' ] : NULL;

			$userInfo = [
				'id'            => $accountId,
				'name'          => $accountEmail,
				'email'         => $accountEmail,
				'profile_image' => isset( $profilePhoto[ 1 ] ) ? $profilePhoto[ 1 ] : NULL
			];

		}
		else
		{
			$userInfo = [
				'id'            => NULL,
				'name'          => NULL,
				'email'         => NULL,
				'profile_image' => NULL
			];
		}

		return $userInfo;
	}

	public function sendPost ( $postTo, $text, $link = NULL, $linkButton = 'LEARN_MORE', $imageURL = '', $productName = NULL, $productPrice = NULL, $productCurrency = NULL, $productCategory = NULL )
	{
		if ( ! $this->getAT() )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'The account is disconnected from the FS Poster plugin. Please update your account cookies to connect your account to the plugin again. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue8\' target=\'_blank\'>How to?</a>.', [], FALSE )
			];
		}

		if ( Helper::getOption( 'gmb_autocut', '1' ) == 1 && mb_strlen( $text ) > 1500 )
		{
			$text = mb_substr( $text, 0, 1497 ) . '...';
		}

		$isProduct = ! is_null( $productName );

		if ( $isProduct )
		{
			$productPrice  = explode( '.', (string) $productPrice );
			$productPrice1 = (int) $productPrice[ 0 ];
			$productPrice2 = isset( $productPrice[ 1 ] ) ? (int) substr( $productPrice[ 1 ] . '000000000', 0, 9 ) : NULL;

			$fReqParam = [
				[
					[
						"u72bYd",
						json_encode( [
							$postTo,
							[
								NULL,
								$productName,
								$text,
								$productCategory,
								[
									[ $productCurrency, $productPrice1, $productPrice2 ],
									[ $productCurrency, $productPrice1, $productPrice2 ]
								],
								$this->uploadPhoto( $postTo, $imageURL, TRUE ),
								( ! empty( $link ) && $linkButton != '-' ? [ $link, $linkButton ] : NULL )
							]
						] ),
						NULL,
						"generic"
					]
				]
			];
		}
		else
		{
			$fReqParam = [
				[
					[
						'h6IfIc',
						json_encode( [
							$postTo,
							[
								NULL,
								$text,
								NULL,
								NULL,
								( ! empty( $link ) && $linkButton !== '-' ? [
									NULL,
									$link,
									$linkButton,
									$linkButton
								] : [] ),
								[],
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								$this->uploadPhoto( $postTo, $imageURL ),
								1,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								[]
							],
							NULL,
							[]
						], JSON_UNESCAPED_SLASHES ),
						NULL,
						'generic',
					],
				],
			];

		}

		try
		{
			$post = (string) $this->client->request( 'POST', 'https://business.google.com/_/GeoMerchantFrontendUi/data/batchexecute', [
				'form_params' => [
					'f.req' => json_encode( $fReqParam, JSON_UNESCAPED_SLASHES ),
					'at'    => $this->getAT()
				],
				'headers'     => [
					'Content-Type'     => 'application/x-www-form-urlencoded;charset=UTF-8',
					'sec-ch-ua-mobile' => '?0',
					'sec-ch-ua'        => '"Google Chrome";v="87", " Not;A Brand";v="99", "Chromium";v="87"',
					'Referer'          => 'https://business.google.com/'
				]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => 'Error! ' . $e->getMessage()
			];
		}

		if ( ! $isProduct )
		{
			preg_match( '/localPosts\/([0-9]+)/', $post, $post_id );

			if ( ! isset( $post_id[ 1 ] ) )
			{
				$getErrorMessage = json_decode( str_replace( [ ")]}'", "\n", '\n' ], '', $post ), TRUE );
				$errorMessage    = isset( $getErrorMessage[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 0 ][ 0 ][ 2 ] ) ? 'Error: ' . $getErrorMessage[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 0 ][ 0 ][ 2 ] : fsp__( 'Error! It couldn\'t share the post!' );

				return [
					'status'    => 'error',
					'error_msg' => $errorMessage
				];
			}
		}

		return [
			'status' => 'ok',
			'id'     => $postTo . ( $isProduct ? ':product' : ':post' )
		];
	}

	private function getAT ()
	{
		if ( is_null( $this->at ) )
		{
			$plusMainPage = $this->getMainPageHTML();

			preg_match( '/\"SNlM0e\":\"([^\"]+)/', $plusMainPage, $at );
			$this->at = isset( $at[ 1 ] ) ? $at[ 1 ] : NULL;
		}

		return $this->at;
	}

	public function uploadPhoto ( $location, $image = '', $for_product = FALSE )
	{
		if ( empty( $image ) )
		{
			return NULL;
		}

		if ( ! $for_product )
		{
			$url = 'https://docs.google.com/upload/gmb/dragonfly';
		}
		else
		{
			$url = 'https://business.google.com/local/business/_/upload/dragonfly';
		}

		// first get the content id from the upload page
		$content_id = 0;

		try
		{
			$request = ( string ) $this->client->request( 'GET', 'https://business.google.com/products/l/' . $location )->getBody();

			preg_match( '/data-mid="([0-9]+)"/', $request, $content_id );

			if ( $content_id )
			{
				$content_id = $content_id[ 1 ];
			}
		}
		catch ( GuzzleException $e )
		{
		}

		if ( $content_id === 0 )
		{
			return NULL;
		}

		// now get the upload url
		$upload_url = '';

		try
		{
			$request = ( string ) $this->client->request( 'POST', $url, [
				'headers' => [
					'X-Goog-Upload-Command' => 'start',
					'Content-Type'          => 'application/x-www-form-urlencoded'
				],
				'json'    => [
					'protocolVersion'      => '0.8',
					'createSessionRequest' => [
						'fields' => [
							[
								'external' => [
									'name'     => 'file',
									'filename' => 'filename_' . Date::epoch() . '.jpg'
								],
							],
							[
								'inlined' => [
									'name'        => 'effective_id',
									'content'     => $content_id,
									'contentType' => 'text/plain',
								],
							],
							[
								'inlined' => [
									'name'        => 'listing_id',
									'content'     => $location,
									'contentType' => 'text/plain',
								],
							],
							[
								'inlined' => [
									'name'        => 'upload_source',
									'content'     => ( ! $for_product ? 'GMB_POSTS_WEB' : 'GMB_PRODUCTS_WEB' ),
									'contentType' => 'text/plain',
								],
							],
							[
								'inlined' => [
									'name'        => 'silo_id',
									'content'     => '7',
									'contentType' => 'text/plain',
								],
							],
						],
					],
				]
			] )->getBody();

			$body       = json_decode( $request, TRUE );
			$upload_url = isset( $body[ 'sessionStatus' ], $body[ 'sessionStatus' ][ 'externalFieldTransfers' ], $body[ 'sessionStatus' ][ 'externalFieldTransfers' ][ 0 ][ 'putInfo' ], $body[ 'sessionStatus' ][ 'externalFieldTransfers' ][ 0 ][ 'putInfo' ][ 'url' ] ) ? $body[ 'sessionStatus' ][ 'externalFieldTransfers' ][ 0 ][ 'putInfo' ][ 'url' ] : '';
		}
		catch ( GuzzleException $e )
		{
		}

		if ( empty( $upload_url ) )
		{
			return NULL;
		}

		// now lets upload
		$media = '';

		try
		{
			$request = ( string ) $this->client->request( 'POST', $upload_url, [
				'headers' => [
					'X-Goog-Upload-Command' => 'upload, finalize',
					'Content-Type'          => 'application/x-www-form-urlencoded'
				],
				'body'    => file_get_contents( $image )
			] )->getBody();

			$body  = json_decode( $request, TRUE );
			$media = isset( $body[ 'sessionStatus' ], $body[ 'sessionStatus' ][ 'additionalInfo' ], $body[ 'sessionStatus' ][ 'additionalInfo' ][ 'uploader_service.GoogleRupioAdditionalInfo' ], $body[ 'sessionStatus' ][ 'additionalInfo' ][ 'uploader_service.GoogleRupioAdditionalInfo' ][ 'completionInfo' ], $body[ 'sessionStatus' ][ 'additionalInfo' ][ 'uploader_service.GoogleRupioAdditionalInfo' ][ 'completionInfo' ][ 'customerSpecificInfo' ] ) ? $body[ 'sessionStatus' ][ 'additionalInfo' ][ 'uploader_service.GoogleRupioAdditionalInfo' ][ 'completionInfo' ][ 'customerSpecificInfo' ] : '';

		}
		catch ( GuzzleException $e )
		{
		}

		if ( empty( $media ) )
		{
			return NULL;
		}

		if ( ! $for_product )
		{
			$json = '';

			try
			{
				$req_params = [
					[
						[
							"iWixD",
							json_encode( [
								$location,
								$media[ 'image_url' ],
								[
									NULL,
									$media[ 'image_url' ],
									NULL,
									NULL,
									1
								]
							], JSON_UNESCAPED_SLASHES ),
							NULL,
							"generic"
						]
					]
				];

				$request = ( string ) $this->client->request( 'POST', 'https://business.google.com/_/GeoMerchantFrontendUi/data/batchexecute', [
					'form_params' => [
						'f.req' => json_encode( $req_params, JSON_UNESCAPED_SLASHES ),
						'at'    => $this->getAT()
					],
					'headers'     => [
						'Content-Type'     => 'application/x-www-form-urlencoded;charset=UTF-8',
						'sec-ch-ua-mobile' => '?0',
						'sec-ch-ua'        => '"Google Chrome";v="87", " Not;A Brand";v="99", "Chromium";v="87"',
						'Referer'          => 'https://business.google.com/'
					]
				] )->getBody();

				preg_match( '/,\[\\\\\"(.*?)]\\\\n]\\\\n/', $request, $localpost );

				$json = stripslashes( str_replace( '\n', '', '[\"' . $localpost[ 1 ] . ']' ) );
			}
			catch ( Guzzle_Exception $e )
			{
			}

			if ( empty( $json ) )
			{
				return NULL;
			}

			// $location_url, $image_url, null, null, 1, [ sizex, sizey ], $id, 1
			return [
				json_decode( $json, TRUE )
			];
		}
		else
		{
			// [ $imageurl, null, $mediakey, $imageurl, $imageurl ]
			return [
				$media[ 'image_url' ],
				NULL,
				$media[ 'media_key' ],
				$media[ 'image_url' ],
				$media[ 'image_url' ]
			];
		}
	}

	public function getMyLocations ()
	{
		$locations_arr = [];

		foreach ( $this->getLocationGroups() as $groupInf )
		{
			$locationsInGroup = $this->getLocationsByGroup( $groupInf[ 0 ], $groupInf[ 1 ] );

			$locations_arr = array_merge( $locations_arr, $locationsInGroup );
		}

		return $locations_arr;
	}

	private function getLocationGroups ()
	{
		$html = str_replace( "\n", "", $this->getMainPageHTML() );

		if ( ! preg_match( '/AF_initDataCallback\((\{key: \'ds:4.+\})\);/Umi', $html, $locationGroups ) )
		{
			return [];
		}

		$locationGroups = preg_replace( '/([\{\, ])([a-zA-Z0-9\_]+)\:/i', '$1"$2":', $locationGroups[ 1 ] );
		$locationGroups = str_replace( [ '\x', "'", ',]' ], [ '', '"', ']' ], $locationGroups );

		$jsonInf = json_decode( $locationGroups, TRUE );

		if ( ! isset( $jsonInf[ 'data' ][ 0 ] ) || ! is_array( $jsonInf[ 'data' ][ 0 ] ) )
		{
			return [];
		}

		$groups = $jsonInf[ 'data' ][ 0 ];

		$groupsArr = [];
		foreach ( $groups as $group )
		{
			if ( isset( $group[ 0 ][ 0 ] ) && isset( $group[ 0 ][ 1 ] ) )
			{
				$groupsArr[] = [ $group[ 0 ][ 0 ], $group[ 0 ][ 1 ] ];
			}
		}

		return $groupsArr;
	}

	private function getLocationsByGroup ( $groupId, $groupName, $nextPageToken = NULL )
	{
		if ( is_null( $nextPageToken ) )
		{
			$fReqParam = [
				[
					[
						"VlgRab",
						"[[null,[],\"" . $groupId . "\",null,null,[],null,[],null,[]],100]",
						NULL,
						"1"
					]
				]
			];
		}
		else
		{
			$fReqParam = [ [ [ "VlgRab", "[null,100,\"" . $nextPageToken . "\"]", NULL, "1" ] ] ];
		}

		try
		{
			$getLocationsJson = (string) $this->client->request( 'POST', 'https://business.google.com/_/GeoMerchantFrontendUi/data/batchexecute', [
				'form_params' => [ 'f.req' => json_encode( $fReqParam ), 'at' => $this->getAT() ],
				'headers'     => [ 'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8' ]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			$getLocationsJson = '';
		}

		$getLocationsJson = str_replace( [ "\n", '\n' ], '', $getLocationsJson );

		$getLocationsJson = preg_replace( '/^.+\[/iU', '[', $getLocationsJson );
		$getLocationsJson = preg_replace( '/[0-9]+\[.+$/iU', '', $getLocationsJson );
		$getLocationsJson = json_decode( $getLocationsJson, TRUE );

		if ( ! is_array( $getLocationsJson ) || ! isset( $getLocationsJson[ 0 ][ 2 ] ) )
		{
			return [];
		}

		$locationsJson = json_decode( $getLocationsJson[ 0 ][ 2 ], TRUE );

		if ( ! is_array( $locationsJson ) || ! isset( $locationsJson[ 0 ] ) || ! is_array( $locationsJson[ 0 ] ) )
		{
			return [];
		}

		$locationsArr = [];

		foreach ( $locationsJson[ 0 ] as $locationInf )
		{
			$locationsArr[] = [
				'id'       => $locationInf[ 1 ],
				'name'     => $locationInf[ 3 ],
				'category' => $groupName
			];
		}

		if ( isset( $locationsJson[ 1 ] ) && ! empty( $locationsJson[ 1 ] ) )
		{
			$locationsArr = array_merge( $locationsArr, $this->getLocationsByGroup( $groupId, $groupName, $locationsJson[ 1 ] ) );
		}

		return $locationsArr;
	}

	private function getMainPageHTML ()
	{
		if ( is_null( $this->main_page_html ) )
		{
			try
			{
				$this->main_page_html = (string) $this->client->request( 'GET', 'https://business.google.com/locations' )->getBody();
			}
			catch ( Exception $e )
			{
				$this->main_page_html = '';
			}
		}

		return $this->main_page_html;
	}

	/**
	 * @return array
	 */
	public function checkAccount ()
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];

		if ( $this->getAT() )
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public function refetch_account ( $account_id )
	{
		return [];
	}
}
