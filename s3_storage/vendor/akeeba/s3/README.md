# Akeeba Amazon S3 Connector

A compact, dependency-less Amazon S3 API client implementing the most commonly used features.

This library is designed to work with Amazon S3 proper, as well as S3-compatible services such as but not limited to Wasabi, Google Storage, Synology C2, ExoScale etc.

## Why reinvent the wheel

After having a lot of impossible to debug problems with Amazon's Guzzle-based AWS SDK we decided to roll our own connector for Amazon S3. This is by no means a complete implementation, just a small subset of S3's features which are required by our software. The design goals are simplicity, no external dependencies and a low memory footprint.

This code was originally based on [S3.php written by Donovan Schonknecht](http://undesigned.org.za/2007/10/22/amazon-s3-php-class) which is available under a BSD-like license. This repository no longer reflects the original author's work and should not be confused with it.

This software is distributed under the GNU General Public License version 3 or, at your option, any later version published by the Free Software Foundation (FSF). In short, it's GPL-3.0-or-later, as noted in composer.json.

## Important notes about version 2

### PHP version support since 2.0

Akeeba Amazon S3 Connector version 2 has dropped support for PHP 5.3 to 7.0 inclusive.

The most significant change in this version is that all methods use scalar type hints for parameters and return values. This _may_ break existing consumers which relied on implicit type conversion.

### Namespace change since 2.3

Up to and including version 2.2 of the library, the namespace was `\Akeeba\Engine\Postproc\Connector\S3v4`. From version 2.3 of the library the namespace has changed to `\Akeeba\S3`.

The library automatically registers aliases of the old classes to the new ones, thus ensuring updating the library will not introduce backwards incompatible changes. This is why it's not a major version update. Aliases will remain in place until at least version 3.0 of the library.

## Using the connector

You need to define a constant before using or referencing any class in the library:

```php
defined('AKEEBAENGINE') or define('AKEEBAENGINE', 1);
```

All library files have a line similar to

```php
defined('AKEEBAENGINE') or die();
```

to prevent direct access to the libraries files. This is intentional. The primary use case for this library is mass-distributed software which gets installed in a publicly accessible subdirectory of the web root. This line prevents any accidental path disclosure from PHP error messages if someone were to access these files directly on misconfigured servers.

If you are writing a Joomla extension, especially a plugin or module, please _always_ check if the constant has already been defined before defining it yourself. Thank you! 

### Get a connector object

```php
$configuration = new \Akeeba\S3\Configuration(
	'YourAmazonAccessKey',
	'YourAmazonSecretKey'
);

$connector = new \Akeeba\S3\Connector($configuration);
```

If you are running inside an Amazon EC2 instance you can fetch temporary credentials from the instance's metadata
server using the IAM Role attached to the EC2 instance. In this case you need to do this (169.254.169.254 is a fixed
IP hosting the instance's metadata cache service):

```php
$role = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/');
$jsonCredentials = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/' . $role);
$credentials = json_decode($jsonCredentials, true);
$configuration = new \Akeeba\S3\Configuration(
	$credentials['AccessKeyId'],
	$credentials['SecretAccessKey'],
	'v4',
	$yourRegion
);
$configuration->setToken($credentials['Token']);

$connector = new \Akeeba\S3\Connector($configuration);
```

where `$yourRegion` is the AWS region of your bucket, e.g. `us-east-1`. Please note that we are passing the security
token (`$credentials['Token']`) to the Configuration object. This is REQUIRED. The temporary credentials returned by
the metadata service won't work without it.

Another point worth noting is that the temporary credentials don't last forever. Check the `$credentials['Expiration']` to see
when they are about to expire. Amazon recommends that you retry fetching new credentials from the metadata service
10 minutes before your cached credentials are set to expire. The metadata service is guaranteed to provision fresh
temporary credentials by that time. 

### Listing buckets

```php
$listing = $connector->listBuckets(true);
```

Returns an array like this:

```
array(2) {
  'owner' =>
  array(2) {
    'id' =>
    string(64) "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef"
    'name' =>
    string(8) "someUserName"
  }
  'buckets' =>
  array(3) {
    [0] =>
    array(2) {
      'name' =>
      string(10) "mybucket"
      'time' =>
      int(1267730711)
    }
    [1] =>
    array(2) {
      'name' =>
      string(10) "anotherbucket"
      'time' =>
      int(1269516249)
    }
    [2] =>
    array(2) {
      'name' =>
      string(11) "differentbucket"
      'time' =>
      int(1354458048)
    }
  }
}
```

### Listing bucket contents

```php
$listing = $connector->getBucket('mybucket', 'path/to/list/');
```

If you want to list "subdirectories" you need to do
 
```php
$listing = $connector->getBucket('mybucket', 'path/to/list/', null, null, '/', true);
```

The last parameter (common prefixes) controls the listing of "subdirectories"

### Uploading (small) files

From a file:

```php
$input = \Akeeba\S3\Input::createFromFile($sourceFile);   
$connector->putObject($input, 'mybucket', 'path/to/myfile.txt');
```

From a string:

```php
$input = \Akeeba\S3\Input::createFromData($sourceString);   
$connector->putObject($input, 'mybucket', 'path/to/myfile.txt');
```

From a stream resource:

```php
$input = \Akeeba\S3\Input::createFromResource($streamHandle, false);   
$connector->putObject($input, 'mybucket', 'path/to/myfile.txt');
```

In all cases the entirety of the file has to be loaded in memory.

### Uploading large file with multipart (chunked) uploads

Files are uploaded in 5Mb chunks.

```php
$input = \Akeeba\S3\Input::createFromFile($sourceFile);
$uploadId = $connector->startMultipart($input, 'mybucket', 'mypath/movie.mov');

$eTags = array();
$eTag = null;
$partNumber = 0;

do
{
	// IMPORTANT: You MUST create the input afresh before each uploadMultipart call
	$input = \Akeeba\S3\Input::createFromFile($sourceFile);
	$input->setUploadID($uploadId);
	$input->setPartNumber(++$partNumber);
	
	$eTag = $connector->uploadMultipart($input, 'mybucket', 'mypath/movie.mov');

	if (!is_null($eTag))
	{
		$eTags[] = $eTag;
	}
}
while (!is_null($eTag));

// IMPORTANT: You MUST create the input afresh before finalising the multipart upload
$input = \Akeeba\S3\Input::createFromFile($sourceFile);
$input->setUploadID($uploadId);
$input->setEtags($eTags);

$connector->finalizeMultipart($input, 'mybucket', 'mypath/movie.mov');
```

As long as you keep track of the UploadId, PartNumber and ETags you can have each uploadMultipart call in a separate
page load to prevent timeouts.

### Get presigned URLs

Allows browsers to download files directly without exposing your credentials and without going through your server:

```php
$preSignedURL = $connector->getAuthenticatedURL('mybucket', 'path/to/file.jpg', 60);
```

The last parameter controls how many seconds into the future this URL will be valid.

### Download

To a file with absolute path `$targetFile`

```php
$connector->getObject('mybucket', 'path/to/file.jpg', $targetFile);
```

To a string

```php
$content = $connector->getObject('mybucket', 'path/to/file.jpg', false);
```

### Delete an object

```php
$connector->deleteObject('mybucket', 'path/to/file.jpg');
```

### Test if an object exists

```php
try
{
    $headers = $connector->headObject('mybucket', 'path/to/file.jpg');
    $exists  = true;
}
catch (\Akeeba\S3\Exception\CannotGetFile $e)
{
    $headers = [];
    $exists  = false;
}
```

The `$headers` variable contains an array with the S3 headers returned by the [HeadObject(https://docs.aws.amazon.com/AmazonS3/latest/API/API_HeadObject.html) API call. The header keys are always in lowercase. Please note that _not all_ of the headers Amazon describes in their documentation are returned in every request.

## Configuration options

The Configuration option has optional methods which can be used to enable some useful features in the connector.

You need to execute these methods against the Configuration object before passing it to the Connector's constructor. For example:

```php
$configuration = new \Akeeba\S3\Configuration(
	'YourAmazonAccessKey',
	'YourAmazonSecretKey'
);

// Use v4 signatures and Dualstack URLs
$configuration->setSignatureMethod('v4');
$configuration->setUseDualstackUrl(true);

$connector = new \Akeeba\S3\Connector($configuration);
```

### HTTPS vs plain HTTP

**It is not recommended to use plain HTTP connections to Amazon S3**. If, however, you have no other option you can tell the Configuration object to use plain HTTP URLs:

```php
$configuration->setSSL(false);
```

Caveat: HTTPS will only work if PHP can verify the TLS certificate of your endpoint. This may not be the case when using a local testing service (e.g. LocalStack), or for some buckets with dots in their names. Moreover, if you are on Windows, do note that neither PHP comes with a Certification Authority cache, nor is there a system-wide CA cache; you'll have to [download](https://curl.se/docs/caextract.html) it and configure PHP, or use [composer/ca-bundle](https://packagist.org/packages/composer/ca-bundle) in your `composer.json` file.

### Custom endpoint

You can use the Akeeba Amazon S3 Connector library with S3-compatible APIs such as DigitalOcean's Spaces by changing the endpoint URL.

Please note that if the S3-compatible APi uses v4 signatures you need to enter the region-specific endpoint domain name and the region when initializing the object, e.g.:

```php
// DigitalOcean Spaces using v4 signatures
// The access credentials are those used in the example at https://developers.digitalocean.com/documentation/spaces/
$configuration = new \Akeeba\S3\Configuration(
	'532SZONTQ6ALKBCU94OU',
	'zCkY83KVDXD8u83RouEYPKEm/dhPSPB45XsfnWj8fxQ',
    'v4',
    'nyc3'
);
$configuration->setEndpoint('nyc3.digitaloceanspaces.com');
$configuration->setRegion('nyc3');
$configuration->setSignatureMethod('v4');

$connector = new \Akeeba\S3\Connector($configuration);
```

If your S3-compatible API uses v2 signatures you do not need to specify a region.

```php
// DigitalOcean Spaces using v2 signatures
// The access credentials are those used in the example at https://developers.digitalocean.com/documentation/spaces/
$configuration = new \Akeeba\S3\Configuration(
	'532SZONTQ6ALKBCU94OU',
	'zCkY83KVDXD8u83RouEYPKEm/dhPSPB45XsfnWj8fxQ',
    'v2'
);
$configuration->setEndpoint('nyc3.digitaloceanspaces.com');

$connector = new \Akeeba\S3\Connector($configuration);
```

Caveat: Setting the endpoint resets the signature version and region. This is why you need to set them _a second time_, after setting the endpoint, as seen in the first example above.

### Legacy path-style access

The S3 API calls made by this library will use by default the subdomain-style access. That is to say, the endpoint will be prefixed with the name of the bucket. For example, a bucket called `example` in the `eu-west-1` region will be accessed using the endpoint URL `example.s3.eu-west-1.amazonaws.com`.

If you have buckets with characters that are invalid in the context of DNS (most notably dots and uppercase characters) this will fail. You will need to use the legacy path style instead. In this case the endpoint used is the generic region specific one (`s3.eu-west-1.amazonaws.com` in our example above), and the API URL will be prefixed with the bucket name.

You need to do:
```php
$configuration->setUseLegacyPathStyle(true);
```

Caveats:
* This will not work with v2 signatures if you are using Amazon AWS S3 proper. It will very likely work with the v2 signatures if you are using a custom endpoint, though.
* This option has no effect on pre-authorised (pre-signed) URLs. Legacy path-style access is used for these URLs by default.

### Dualstack (IPv4 and IPv6) support

Amazon S3 supports dual-stack URLs which resolve to both IPv4 and IPv6 addresses. By default they are _not_ used. If you want to enable this feature you need to do:

```php
$connector->setUseDualstackUrl(true);
```

Caveat: This option only takes effect if you are using Amazon S3 proper. It will _not_ have any effect with custom endpoints. DualStack support is deprecated by Amazon S3. We strongly advise you NOT to use it anymore.

### Alternate Date Format

By default, this library uses the standard date format `D, d M Y H:i:s O` which Amazon _incorrectly_ documents as "ISO 8601" (it's not, see the [ISO 8601 Wikipedia entry](https://en.wikipedia.org/wiki/ISO_8601) for reference). Most third party, Amazon S3-compatible services use the same and understand it just fine.

A minority of services don't understand the GMT offset at the end of the date format, and instead need the format `D, d M Y H:i:s T`. You can set a flag to enable this behaviour like so:
```php
$configuration->setAlternateDateHeaderFormat(true);
```

Caveat: Enabling this flag breaks compatibility with S3 proper.

### Using The HTTP Date Header Instead Of X-Amz-Date

Amazon documents that you should be using the standard HTTP `Date` header, and only resort to using the `X-Amz-Date` header when using the standard header is impossible, e.g. when creating pre-authorised (signed) URLs, or when your HTTP library does not let you set the standard header.

Unfortunately, some third party S3-compatible services such as Wasabi and ExoScale do _NOT_ support the standard `Date` header at all. Using it makes them falsely spit out a message about the signature being wrong. They are the reason why, by default, we are passing the request date and time using the `X-Amz-Date` header.

If you are using a third party service which for any reason does not understand the `X-Amz-Date` header you need to set a flag which forces the use of the standard `Date` header like so:
```php
$configuration->setUseHTTPDateHeader(true);
```