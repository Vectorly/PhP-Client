<?php

namespace Tests;


use PHPUnit\Framework\TestCase;

use Vectorly\Client;

class ClientTest extends TestCase {
	
	protected $config;
	protected $client;
	
	public function __construct( ?string $name = null, array $data = [], string $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );
		$this->config = include __DIR__ . '/config.php';
		$this->client = new Client( $this->config['api_key'] );
	}
	
	public function testList() {
		$this->assertIsArray( $this->client->list() );
	}
	
	public function testUpload() {
		$this->assertIsInt( $this->client->upload( $this->config['upload_video_path'] ) );
	}
	
	public function testDetails() {
		$this->assertIsArray( $this->client->details( $this->config['video_id'] ) );
	}
	
	public function testSearch() {
		$this->assertIsArray( $this->client->search( 'sample' ) );
	}
	
	public function testDownload() {
		if ( file_exists( $this->config['download_video_path'] ) ) {
			unlink( $this->config['download_video_path'] );
		}
		
		$this->client->download( $this->config['video_id'], $this->config['download_video_path'] );
		$this->assertFileExists( $this->config['download_video_path'] );
	}
	
	public function testAnalytics() {
		$this->assertIsArray( $this->client->analytics() );
	}
	
	public function testEvents() {
		$this->assertIsArray( $this->client->events( $this->config['video_id'] ) );
	}
	
	public function testAccount() {
		$this->assertIsArray( $this->client->account() );
	}
	
	public function testSecure() {
		$this->assertIsString( $this->client->secure( $this->config['video_id'], 1 ) );
	}
}