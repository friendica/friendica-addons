<?php
/**
 * Name: JS Uploader
 * Description: JavaScript photo/image uploader. Helpful for uploading multiple files at once. Uses Valum 'qq' Uploader.
 * Version: 1.1
 * Author: Chris Case <http://friendika.openmindspace.org/profile/chris_case>
 * Maintainer: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\Strings;

global $js_upload_jsonresponse;
global $js_upload_result;

function js_upload_install()
{
	Hook::register('photo_upload_form', __FILE__, 'js_upload_form');
	Hook::register('photo_post_init', __FILE__, 'js_upload_post_init');
	Hook::register('photo_post_file', __FILE__, 'js_upload_post_file');
	Hook::register('photo_post_end', __FILE__, 'js_upload_post_end');
}

function js_upload_form(App $a, array &$b)
{
	$b['default_upload'] = false;

	DI::page()->registerStylesheet('addon/js_upload/file-uploader/client/fileuploader.css');
	DI::page()->registerFooterScript('addon/js_upload/file-uploader/client/fileuploader.js');

	$tpl = Renderer::getMarkupTemplate('js_upload.tpl', 'addon/js_upload');
	$b['addon_text'] .= Renderer::replaceMacros($tpl, [
		'$upload_msg' => DI::l10n()->t('Select files for upload'),
		'$drop_msg' => DI::l10n()->t('Drop files here to upload'),
		'$cancel' => DI::l10n()->t('Cancel'),
		'$failed' => DI::l10n()->t('Failed'),
		'$post_url' => $b['post_url'],
		'$maximagesize' => intval(DI::config()->get('system', 'maximagesize')),
	]);
}

function js_upload_post_init(App $a, &$b)
{
	global $js_upload_result, $js_upload_jsonresponse;

	// list of valid extensions
	$allowedExtensions = ['jpeg', 'gif', 'png', 'jpg'];

	// max file size in bytes
	$sizeLimit = DI::config()->get('system', 'maximagesize');

	$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

	$result = $uploader->handleUpload();

	// to pass data through iframe you will need to encode all html tags
	$js_upload_jsonresponse = htmlspecialchars(json_encode($result), ENT_NOQUOTES);

	if (isset($result['error'])) {
		Logger::log('mod/photos.php: photos_post(): error uploading photo: ' . $result['error'], Logger::DEBUG);
		echo json_encode($result);
		exit();
	}

	$js_upload_result = $result;
}

function js_upload_post_file(App $a, &$b)
{
	global $js_upload_result;

	$result = $js_upload_result;

	$b['src'] = $result['path'];
	$b['filename'] = $result['filename'];
	$b['filesize'] = filesize($b['src']);

}

function js_upload_post_end(App $a, &$b)
{
	global $js_upload_jsonresponse;

	Logger::log('upload_post_end');
	if (!empty($js_upload_jsonresponse)) {
		echo $js_upload_jsonresponse;
		exit();
	}
}

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr
{
	private $pathnm = '';

	/**
	 * Save the file in the temp dir.
	 *
	 * @return boolean TRUE on success
	 */
	function save()
	{
		$input = fopen('php://input', 'r');

		$upload_dir = DI::config()->get('system', 'tempdir');
		if (!$upload_dir)
			$upload_dir = sys_get_temp_dir();

		$this->pathnm = tempnam($upload_dir, 'frn');

		$temp = fopen($this->pathnm, 'w');
		$realSize = stream_copy_to_stream($input, $temp);

		fclose($input);
		fclose($temp);

		if ($realSize != $this->getSize()) {
			return false;
		}
		return true;
	}

	function getPath()
	{
		return $this->pathnm;
	}

	function getName()
	{
		return $_GET['qqfile'];
	}

	function getSize()
	{
		if (isset($_SERVER['CONTENT_LENGTH'])) {
			return (int)$_SERVER['CONTENT_LENGTH'];
		} else {
			throw new Exception('Getting content length is not supported.');
		}
	}
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm
{
	/**
	 * Save the file to the specified path
	 *
	 * @return boolean TRUE on success
	 */
	function save()
	{
		return true;
	}

	function getPath()
	{
		return $_FILES['qqfile']['tmp_name'];
	}

	function getName()
	{
		return $_FILES['qqfile']['name'];
	}

	function getSize()
	{
		return $_FILES['qqfile']['size'];
	}
}

class qqFileUploader
{
	private $allowedExtensions = [];
	private $sizeLimit = 10485760;
	private $file;

	function __construct(array $allowedExtensions = [], $sizeLimit = 10485760)
	{
		$allowedExtensions = array_map('strtolower', $allowedExtensions);

		$this->allowedExtensions = $allowedExtensions;
		$this->sizeLimit = $sizeLimit;

		if (isset($_GET['qqfile'])) {
			$this->file = new qqUploadedFileXhr();
		} elseif (isset($_FILES['qqfile'])) {
			$this->file = new qqUploadedFileForm();
		} else {
			$this->file = false;
		}

	}

	private function toBytes($str)
	{
		$val = trim($str);
		$last = strtolower($str[strlen($str) - 1]);
		switch ($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	/**
	 * Returns array('success'=>true) or array('error'=>'error message')
	 */
	function handleUpload()
	{
		if (!$this->file) {
			return ['error' => DI::l10n()->t('No files were uploaded.')];
		}

		$size = $this->file->getSize();

		if ($size == 0) {
			return ['error' => DI::l10n()->t('Uploaded file is empty')];
		}

//		if ($size > $this->sizeLimit) {

//			return array('error' => DI::l10n()->t('Uploaded file is too large'));
//		}


		$maximagesize = DI::config()->get('system', 'maximagesize');

		if (($maximagesize) && ($size > $maximagesize)) {
			return ['error' => DI::l10n()->t('Image exceeds size limit of %s', Strings::formatBytes($maximagesize))];
		}

		$pathinfo = pathinfo($this->file->getName());
		$filename = $pathinfo['filename'];

		if (!isset($pathinfo['extension'])) {
			Logger::warning('extension isn\'t set.', ['filename' => $filename]);
		}
		$ext = $pathinfo['extension'] ?? '';

		if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
			return ['error' => DI::l10n()->t('File has an invalid extension, it should be one of %s.', implode(', ', $this->allowedExtensions))];
		}

		if ($this->file->save()) {
			return [
				'success' => true,
				'path' => $this->file->getPath(),
				'filename' => $filename . '.' . $ext
			];
		} else {
			return [
				'error' => DI::l10n()->t('Upload was cancelled, or server error encountered'),
				'path' => $this->file->getPath(),
				'filename' => $filename . '.' . $ext
			];
		}
	}
}
