<?php

namespace Vectorly;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use TusPhp\Tus\Client as TusClient;
use Vectorly\Exceptions\ResponseDecodeException;
use Ahc\Jwt\JWT;

class Client {
	
	/**
	 * @var string
	 */
	protected $api_key;
	/**
	 * @var GuzzleClient
	 */
	protected $guzzle_client;
	/**
	 * @var array
	 */
	protected $guzzle_headers;
	/**
	 * @var TusClient
	 */
	protected $tus_client;
	
	const API_URL = 'https://api.vectorly.io/';
	const TUS_URL = 'https://tus.vectorly.io';
	
	/**
	 * Client constructor.
	 *
	 * @param string $api_key
	 *
	 * @throws \ReflectionException
	 */
	public function __construct( string $api_key ) {
		$this->api_key        = $api_key;
		$this->guzzle_client  = new GuzzleClient();
		$this->guzzle_headers = [
			'headers' => [
				'X-Api-Key' => $api_key,
			],
		];
		$this->tus_client     = new TusClient( self::TUS_URL );
		$this->tus_client->setApiPath( '/files/' );
		$this->tus_client->addMetadata( 'api_key', $api_key );
	}
	
	/**
	 * @param string      $file_path
	 * @param string|null $custom_name
	 * @param string|null $key
	 * @param int         $chunk
	 *
	 * @return int
	 * @throws \TusPhp\Exception\ConnectionException
	 * @throws \TusPhp\Exception\TusException
	 */
	public function upload( string $file_path, string $custom_name = null, string $key = null, int $chunk = - 1 ): int {
		return $this->tus_client->setKey( $key ? $key : md5( microtime() . uniqid() ) )
		                        ->file( $file_path, $custom_name )
		                        ->upload( $chunk );
	}
	
	/**
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function list(): array {
		$http_response = $this->guzzle_client->get( self::API_URL . 'videos/list', $this->guzzle_headers );
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @param string $video_id
	 *
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function details( string $video_id ): array {
		$http_response = $this->guzzle_client->get( self::API_URL . 'videos/get/' . $video_id, $this->guzzle_headers );
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @param string $term
	 *
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function search( string $term ): array {
		$http_response = $this->guzzle_client->get( self::API_URL . 'videos/search/' . urlencode( $term ), $this->guzzle_headers );
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @param string $video_id
	 * @param string $output_path
	 */
	public function download( string $video_id, string $output_path ): void {
		$this->guzzle_client->get( self::API_URL . 'videos/download/' . $video_id, array_merge( $this->guzzle_headers, [ 'sink' => $output_path ] ) );
	}
	
	/**
	 * @param string $video_id
	 * @param array  $tags_to_add
	 * @param array  $tags_to_remove
	 *
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function tags( string $video_id, array $tags_to_add = [], array $tags_to_remove = [] ): array {
		$http_response = $this->guzzle_client->post( self::API_URL . 'videos/tag',
			array_merge( $this->guzzle_headers, [
				'json' => [
					'video_id' => $video_id,
					'tags'     => [
						'add'    => $tags_to_add,
						'remove' => $tags_to_remove,
					],
				],
			] )
		);
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @param string $video_id
	 *
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function archive( string $video_id ): array {
		$http_response = $this->guzzle_client->post( self::API_URL . 'videos/archive',
			array_merge( $this->guzzle_headers, [
				'json' => [
					'video_id' => $video_id,
				],
			] )
		);
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @param string $video_id
	 * @param bool   $is_private
	 *
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function privacy( string $video_id, bool $is_private ): array {
		$http_response = $this->guzzle_client->post( self::API_URL . 'videos/privacy',
			array_merge( $this->guzzle_headers, [
				'json' => [
					'video_id'   => $video_id,
					'id_private' => $is_private,
				],
			] )
		);
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function analytics(): array {
		$http_response = $this->guzzle_client->get( self::API_URL . 'analytics/summary', $this->guzzle_headers );
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @param string $video_id
	 *
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function events( string $video_id ): array {
		$http_response = $this->guzzle_client->get( self::API_URL . 'analytics/events/video/' . $video_id, $this->guzzle_headers );
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @return array
	 * @throws ResponseDecodeException
	 */
	public function account(): array {
		$http_response = $this->guzzle_client->get( self::API_URL . 'account/usage', $this->guzzle_headers );
		
		return $this->getResponse( $http_response );
	}
	
	/**
	 * @param string $video_id
	 * @param int    $duration_minutes
	 *
	 * @return string
	 */
	public function secure( string $video_id, int $duration_minutes ): string {
		$jwt   = new JWT( $this->api_key, 'HS256', $duration_minutes * 60 );
		$token = $jwt->encode( [
			'video_id' => $video_id,
			'expiry'   => round( microtime( true ) * 1000 ) + $duration_minutes * 60000,
		] );
		
		return 'http://stream.vectorly.io/embed/video/' . $video_id . '/token/' . $token;
	}
	
	/**
	 * @param ResponseInterface $http_response
	 *
	 * @return array
	 * @throws ResponseDecodeException
	 */
	protected function getResponse( ResponseInterface $http_response ): array {
		$http_response = $http_response->getBody()->getContents();
		$rs            = json_decode( $http_response, true );
		
		if ( is_null( $rs ) ) {
			throw new ResponseDecodeException( $http_response );
		}
		
		return $rs;
	}
}
