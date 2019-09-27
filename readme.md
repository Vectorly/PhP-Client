# Vectorly REST API PHP Client

A php wrapper for Vectorly rest api, that offers a number of methods to interact with Vectorly's Rest service.

## Vectorly Rest Api PHP Client is using:

- `ankitpokhrel/tus-php` as a Tus Client
- `guzzlehttp/guzzle` for http calls

## Installing Vectorly Rest PHP Client:

```bash
composer require vectorly/php-client
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## Examples:

#### List videos:

```php
$client      = new Vectorly\Client( 'API_KEY_GOES_HERE' );
$videos_list = $client->list();
print_r( $videos_list );
```

#### Upload video:

```php
$bytes_uploaded = $client->upload( '/path/to/file' );
echo $bytes_uploaded;
```

or

```php
/**
 * Upload the file
 * With a custom name (optional),
 * Set the unique id (optional),
 * And upload a chunk of 1MB (optional)
 */
$bytes_uploaded = $client->upload( '/path/to/file', 'my_custom_name.mp4', 'my_unique_id', 1000000 );
echo $bytes_uploaded;
```

#### Get video details:

```php
$video_details = $client->details( 'video_id' );
print_r( $video_details );
```

#### Search for videos by term presence:

```php
$videos_list = $client->search( 'search_term' );
print_r( $videos_list );
```

#### Download video:

```php
$client->download( 'video_id', '/path/to/output/file.mp4' );
```

#### Analytics:

Overall summary of video playback over the last 30 days

```php
$analytics = $client->analytics();
print_r($analytics);
```

Retrieve all events from the last 90 days for a particular video

```php
$events = $client->events('video_id');
print_r($events);
```

#### Check account usage and billing details:

```php
$account_details = $client->account();
print_r($account_details);
```

#### Secure a video url:

```php
/**
 * Get a temporary url that grants access to the video for ten minutes
 * If the duration is omitted, the default value is one minute
 */
$temporary_url = $client->secure('video_id', 10);
print_r($temporary_url);
```
