<?php

namespace FSPoster\App\Libraries\instagram;

use Exception;
use RuntimeException;
use Symfony\Component\Process\Process;

class FFmpeg
{
	const BINARIES         = [
		'ffmpeg',
		'avconv',
	];

	const WINDOWS_BINARIES = [
		'ffmpeg.exe',
		'avconv.exe',
	];

	public static    $defaultBinary;
	public static    $defaultTimeout;
	public static    $ffprobeBin;
	private static   $videoDetails = [];
	protected static $_instances   = [];
	protected        $_ffmpegBinary;
	protected        $_hasNoAutorotate;
	protected        $_hasLibFdkAac;

	protected function __construct ( $ffmpegBinary )
	{
		$this->_ffmpegBinary = $ffmpegBinary;

		try
		{
			$this->version();
		}
		catch ( Exception $e )
		{
			throw new RuntimeException( sprintf( 'It seems that the path to ffmpeg binary is invalid. Please check your path to ensure that it is correct.' ) );
		}
	}

	public function run ( $command )
	{
		$process = $this->runAsync( $command );

		try
		{
			$exitCode = $process->wait();
		}
		catch ( Exception $e )
		{
			throw new RuntimeException( sprintf( 'Failed to run the ffmpeg binary: %s', $e->getMessage() ) );
		}
		if ( $exitCode )
		{
			$errors   = preg_replace( '#[\r\n]+#', '"], ["', trim( $process->getErrorOutput() ) );
			$errorMsg = sprintf( 'FFmpeg Errors: ["%s"], Command: "%s".', $errors, $command );

			throw new RuntimeException( $errorMsg, $exitCode );
		}

		return preg_split( '#[\r\n]+#', $process->getOutput(), NULL, PREG_SPLIT_NO_EMPTY );
	}

	public function runAsync ( $command )
	{
		$fullCommand = sprintf( '%s -v error %s', static::escape( $this->_ffmpegBinary ), $command );

		$process = new Process( $fullCommand );
		if ( is_int( self::$defaultTimeout ) && self::$defaultTimeout > 60 )
		{
			$process->setTimeout( self::$defaultTimeout );
		}
		$process->start();

		return $process;
	}

	public function version ()
	{
		return $this->run( '-version' )[ 0 ];
	}

	public function getFFmpegBinary ()
	{
		return $this->_ffmpegBinary;
	}

	public function hasNoAutorotate ()
	{
		if ( $this->_hasNoAutorotate === NULL )
		{
			try
			{
				$this->run( '-noautorotate -f lavfi -i color=color=red -t 1 -f null -' );
				$this->_hasNoAutorotate = TRUE;
			}
			catch ( RuntimeException $e )
			{
				$this->_hasNoAutorotate = FALSE;
			}
		}

		return $this->_hasNoAutorotate;
	}

	public function hasLibFdkAac ()
	{
		if ( $this->_hasLibFdkAac === NULL )
		{
			$this->_hasLibFdkAac = $this->_hasAudioEncoder( 'libfdk_aac' );
		}

		return $this->_hasLibFdkAac;
	}

	protected function _hasAudioEncoder ( $encoder )
	{
		try
		{
			$this->run( sprintf( '-f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100 -c:a %s -t 1 -f null -', static::escape( $encoder ) ) );

			return TRUE;
		}
		catch ( RuntimeException $e )
		{
			return FALSE;
		}
	}

	public static function factory ( $ffmpegBinary = NULL )
	{
		if ( $ffmpegBinary === NULL )
		{
			return static::_autoDetectBinary();
		}

		if ( isset( self::$_instances[ $ffmpegBinary ] ) )
		{
			return self::$_instances[ $ffmpegBinary ];
		}

		$instance                          = new static( $ffmpegBinary );
		self::$_instances[ $ffmpegBinary ] = $instance;

		return $instance;
	}

	protected static function _autoDetectBinary ()
	{
		$binaries = defined( 'PHP_WINDOWS_VERSION_MAJOR' ) ? self::WINDOWS_BINARIES : self::BINARIES;
		if ( self::$defaultBinary !== NULL )
		{
			array_unshift( $binaries, self::$defaultBinary );
		}

		$instance = NULL;
		foreach ( $binaries as $binary )
		{
			if ( isset( self::$_instances[ $binary ] ) )
			{
				return self::$_instances[ $binary ];
			}

			try
			{
				$instance = new static( $binary );
			}
			catch ( Exception $e )
			{
				continue;
			}
			self::$defaultBinary         = $binary;
			self::$_instances[ $binary ] = $instance;

			return $instance;
		}

		throw new RuntimeException( fsp__( 'For sharing videos on Instagram, you have to install the FFmpeg library on your server and configure executables\' path. <a href=\'https://www.fs-poster.com/documentation/how-to-install-ffmpeg\' target=\'_blank\'>How to?</a>', [], FALSE ) );
	}

	public static function escape ( $arg, $meta = TRUE )
	{
		if ( ! defined( 'PHP_WINDOWS_VERSION_BUILD' ) )
		{
			return escapeshellarg( $arg );
		}

		$quote = strpbrk( $arg, " \t" ) !== FALSE || $arg === '';
		$arg   = preg_replace( '/(\\\\*)"/', '$1$1\\"', $arg, -1, $dquotes );

		if ( $meta )
		{
			$meta = $dquotes || preg_match( '/%[^%]+%/', $arg );

			if ( ! $meta && ! $quote )
			{
				$quote = strpbrk( $arg, '^&|<>()' ) !== FALSE;
			}
		}

		if ( $quote )
		{
			$arg = preg_replace( '/(\\\\*)$/', '$1$1', $arg );
			$arg = '"' . $arg . '"';
		}

		if ( $meta )
		{
			$arg = preg_replace( '/(["^&|<>()%])/', '^$1', $arg );
		}

		return $arg;
	}

	public static function checkFFPROBE ()
	{
		// We only resolve this once per session and then cache the result.
		if ( self::$ffprobeBin === NULL )
		{
			@exec( 'ffprobe -version 2>&1', $output, $statusCode );

			if ( $statusCode === 0 )
			{
				self::$ffprobeBin = 'ffprobe';
			}
			else
			{
				self::$ffprobeBin = FALSE; // Nothing found!
			}
		}

		return self::$ffprobeBin;
	}

	public static function videoDetails ( $filename )
	{
		if ( ! isset( self::$videoDetails[ md5( $filename ) ] ) )
		{
			$ffprobe = self::checkFFPROBE();

			if ( $ffprobe === FALSE )
			{
				throw new RuntimeException( fsp__( 'For sharing videos on Instagram, you have to install the FFmpeg library on your server and configure executables\' path <a href=\'https://www.fs-poster.com/documentation/how-to-install-ffmpeg\' target=\'_blank\'>How to?</a>', [], FALSE ) );
			}

			$command = sprintf( '%s -v quiet -print_format json -show_format -show_streams %s', self::escape( $ffprobe ), self::escape( $filename ) );

			$jsonInfo    = @shell_exec( $command );
			$probeResult = @json_decode( $jsonInfo, TRUE );

			if ( ! is_array( $probeResult ) || ! isset( $probeResult[ 'streams' ] ) || ! is_array( $probeResult[ 'streams' ] ) )
			{
				throw new RuntimeException( sprintf( 'FFprobe failed to detect any stream. Is "%s" a valid media file?', $filename ) );
			}

			$videoCodec = NULL;
			$width      = 0;
			$height     = 0;
			$duration   = 0;
			$audioCodec = NULL;

			foreach ( $probeResult[ 'streams' ] as $streamIdx => $streamInfo )
			{
				if ( ! isset( $streamInfo[ 'codec_type' ] ) )
				{
					continue;
				}

				switch ( $streamInfo[ 'codec_type' ] )
				{
					case 'video':
						$videoCodec = (string) $streamInfo[ 'codec_name' ];
						$width      = (int) $streamInfo[ 'width' ];
						$height     = (int) $streamInfo[ 'height' ];

						if ( isset( $streamInfo[ 'duration' ] ) )
						{
							$duration = (int) $streamInfo[ 'duration' ];
						}
						break;
					case 'audio':
						$audioCodec = (string) $streamInfo[ 'codec_name' ];
						break;
				}
			}

			if ( is_null( $duration ) && isset( $probeResult[ 'format' ][ 'duration' ] ) )
			{
				$duration = (int) $probeResult[ 'format' ][ 'duration' ];
			}

			if ( is_null( $duration ) )
			{
				throw new RuntimeException( sprintf( 'FFprobe failed to detect video duration. Is "%s" a valid video file?', $filename ) );
			}

			self::$videoDetails[ md5( $filename ) ] = array_merge( $probeResult, [
				'video_codec' => $videoCodec,
				'width'       => $width,
				'height'      => $height,
				'duration'    => $duration,
				'audio_codec' => $audioCodec
			] );
		}

		return self::$videoDetails[ md5( $filename ) ];
	}
}
