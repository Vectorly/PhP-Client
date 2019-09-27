<?php


namespace Vectorly\Exceptions;

class ResponseDecodeException extends \Exception {
	/**
	 * ResponseDecodeException constructor.
	 *
	 * @param string $message
	 */
	public function __construct( string $message = "" ) {
		parent::__construct( 'Could not decode response: ' . $message );
	}
}