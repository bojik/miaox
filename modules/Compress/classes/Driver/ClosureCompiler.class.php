<?php
class Realty_Compress_Driver_ClosureCompiler
extends Realty_Compress_Driver
implements Realty_Compress_Driver_Interface
{
	private $_jarFilename = array();

	/**
	 *
	 *
	 * @param unknown_type $jarFilename
	 * @param array $options
	 */
	public function __construct( $jarFilename = '', Realty_Log $log = null )
	{
		if ( empty( $jarFilename ) )
		{
			$jarFilename = Realty_Config::Libs( __CLASS__ )->get( 'jar_filename' );
		}
		if ( is_null( $log ) )
		{
			$log = Realty_Log::factory2( '' );
		}
		parent::__construct( $log );
		$this->setJarFilename( $jarFilename );
	}

	public function setJarFilename( $filename )
	{
		$message = '';
		if ( empty( $filename ) )
		{
			$message = 'Invalid param $filename: must be not empty';
		}
		if ( !file_exists( $filename ) )
		{
			$message = sprintf( 'File (%s) not found', $filename );
		}
		$pos = mb_stripos( $filename, '.jar' );
		if ( false === $pos )
		{
			$message = sprintf( 'Invalid file (%s) must be .jar', $filename );
		}

		if ( !empty( $message ) )
		{
			throw new Realty_Compress_Driver_ClosureCompiler_Exception( $message );
		}

		$this->_jarFilename = $filename;
	}

	public function getJarFilename()
	{
		return $this->_jarFilename;
	}

	public function minify( $fileList, $dstFilename, array $options = array() )
	{
		$msg = sprintf( 'Start minify by ClosureCompiler (Google)' );
		$this->getLog()->debug( $msg );

//		$totalFilename = realpath( dirname( $dstFilename ) ) . '/' . 'total_' . rand( 1, 900 ) . '.js';
//		$totalFilename = $this->mergeFiles( $fileList, $totalFilename );

		$command = $this->_makeMinifyCommand( $fileList, $dstFilename );

		$shell = $this->_getShell();
		$returnVal = 0;
		$output = null;
		$shell->exec( $command, $returnVal, false, $output );

//		unlink( $totalFilename );

		return $output;
	}

	private function _makeMinifyCommand( $fileList, $outputFilename )
	{
		$command = sprintf( 'java -Xms56M -Xmx128M -jar "%s"', $this->getJarFilename() );

		foreach ( $fileList as $filename )
		{
			$command .= sprintf( ' --js "%s"', $filename );
		}
		$command .= sprintf( ' --js_output_file "%s"', $outputFilename );

		return $command;
	}
}