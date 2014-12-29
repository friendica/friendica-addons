<?php
/** @package    php-gpg::GPG */

/** require supporting files */
require_once("Expanded_Key.php");

define("PK_TYPE_ELGAMAL", 1);
define("PK_TYPE_RSA", 0);
define("PK_TYPE_UNKNOWN", -1);

/**
 * Pure PHP implementation of PHP/GPG public key
 *
 * @package php-gpg::GPG
 * @link http://www.verysimple.com/
 * @copyright  1997-2011 VerySimple, Inc.
 * @license    http://www.gnu.org/licenses/lgpl.html  LGPL
 * @todo implement decryption
 * @version 1.0
 */
class GPG_Public_Key {
    var $version;
	var $fp;
	var $key_id;
	var $user;
	var $public_key;
	var $type;
	
	function IsValid()
	{
		return $this->version != -1 && $this->GetKeyType() != PK_TYPE_UNKNOWN;
	}
	
	function GetKeyType()
	{
		if (!strcmp($this->type, "ELGAMAL")) return PK_TYPE_ELGAMAL;
		if (!strcmp($this->type, "RSA")) return PK_TYPE_RSA;
		return PK_TYPE_UNKNOWN;
	}

	function GetFingerprint()
	{
		return strtoupper( trim(chunk_split($this->fp, 4, ' ')) );
	}
	
	function GetKeyId()
	{
		return (strlen($this->key_id) == 16) ? strtoupper($this->key_id) : '0000000000000000';
	}
	
	function GetPublicKey()
	{
		return str_replace("\n", "", $this->public_key);
	}
	
	function GPG_Public_Key($asc) {
		$found = 0;
		
		// normalize line breaks
		$asc = str_replace("\r\n", "\n", $asc);
		
		if (strpos($asc, "-----BEGIN PGP PUBLIC KEY BLOCK-----\n") === false)
			throw new Exception("Missing header block in Public Key");

		if (strpos($asc, "\n\n") === false)
			throw new Exception("Missing body delimiter in Public Key");
		
		if (strpos($asc, "\n-----END PGP PUBLIC KEY BLOCK-----") === false)
			throw new Exception("Missing footer block in Public Key");
		
		// get rid of everything except the base64 encoded key
		$headerbody = explode("\n\n", str_replace("\n-----END PGP PUBLIC KEY BLOCK-----", "", $asc), 2);
		$asc = trim($headerbody[1]);
		
		
		$len = 0;
		$s =  base64_decode($asc);
		$sa = str_split($s);
		
		for($i = 0; $i < strlen($s);) {
			$tag = ord($sa[$i++]);
			
			// echo 'TAG=' . $tag . '/';
			
			if(($tag & 128) == 0) break;
			
			if($tag & 64) {
				$tag &= 63;
				$len = ord($sa[$i++]);
				if ($len > 191 && $len < 224) $len = (($len - 192) << 8) + ord($sa[$i++]);
				else if ($len == 255) $len = (ord($sa[$i++]) << 24) + (ord($sa[$i++]) << 16) + (ord($sa[$i++]) << 8) + ord($sa[$i++]);
					else if ($len > 223 && len < 255) $len = (1 << ($len & 0x1f));
			} else {
				$len = $tag & 3;
				$tag = ($tag >> 2) & 15;
				if ($len == 0) $len = ord($sa[$i++]);
				else if($len == 1) $len = (ord($sa[$i++]) << 8) + ord($sa[$i++]);
					else if($len == 2) $len = (ord($sa[$i++]) << 24) + (ord($sa[$i++]) << 16) + (ord($sa[$i++]) << 8) + ord($sa[$i++]);
						else $len = strlen($s) - 1;
			}
			
			// echo $tag . ' ';
			
			if ($tag == 6 || $tag == 14) {
				$k = $i;
				$version = ord($sa[$i++]);
				$found = 1;
				$this->version = $version;
				
				$time = (ord($sa[$i++]) << 24) + (ord($sa[$i++]) << 16) + (ord($sa[$i++]) << 8) + ord($sa[$i++]);
				
				if($version == 2 || $version == 3) $valid = ord($sa[$i++]) << 8 + ord($sa[$i++]);
				
				$algo = ord($sa[$i++]);
				
				if($algo == 1 || $algo == 2) {
					$m = $i;
					$lm = floor((ord($sa[$i]) * 256 + ord($sa[$i + 1]) + 7) / 8);
					$i += $lm + 2;
					
					$mod = substr($s, $m, $lm + 2);
					$le = floor((ord($sa[$i]) * 256 + ord($sa[$i+1]) + 7) / 8);
					$i += $le + 2;
					
					$this->public_key = base64_encode(substr($s, $m, $lm + $le + 4));
					$this->type = "RSA";
					
					if ($version == 3) {
						$this->fp = '';
						$this->key_id = bin2hex(substr($mod, strlen($mod) - 8, 8));
					} else if($version == 4) {
						
						// https://tools.ietf.org/html/rfc4880#section-12
						$headerPos = strpos($s, chr(0x04));  // TODO: is this always the correct starting point for the pulic key packet 'version' field?
						$delim = chr(0x01) . chr(0x00);  // TODO: is this the correct delimiter for the end of the public key packet? 
						$delimPos = strpos($s, $delim) + (3-$headerPos);
						
						// echo "POSITION: $delimPos\n";
						
						$pkt = chr(0x99) . chr($delimPos >> 8) . chr($delimPos & 255) . substr($s, $headerPos, $delimPos);
						
						// this is the original signing string which seems to have only worked for key lengths of 1024 or less
						//$pkt = chr(0x99) . chr($len >> 8) . chr($len & 255) . substr($s, $k, $len);
						
						$fp = sha1($pkt);
						$this->fp = $fp;
						$this->key_id = substr($fp, strlen($fp) - 16, 16);
						
						// uncomment to debug the start point for the signing string
// 						for ($ii = 5; $ii > -1; $ii--) {
// 							$pkt = chr(0x99) . chr($ii >> 8) . chr($ii & 255) . substr($s, $headerPos, $ii);
// 							$fp = sha1($pkt);
// 							echo "LENGTH=" . $headerPos . '->' . $ii . " CHR(" . ord(substr($s,$ii, 1)) . ") = " . substr($fp, strlen($fp) - 16, 16) . "\n";
// 						}
// 						echo "\n";
						
						// uncomment to debug the end point for the signing string
// 						for ($ii = strlen($s); $ii > 1; $ii--) {
// 							$pkt = chr(0x99) . chr($ii >> 8) . chr($ii & 255) . substr($s, $headerPos, $ii);
// 							$fp = sha1($pkt);
// 							echo "LENGTH=" . $headerPos . '->' . $ii . " CHR(" . ord(substr($s,$ii, 1)) . ") = " . substr($fp, strlen($fp) - 16, 16) . "\n";
// 						}
					} else {
						throw new Exception('GPG Key Version ' . $version . ' is not supported');
					}
					$found = 2;
				} else if(($algo == 16 || $algo == 20) && $version == 4) {
						$m = $i;
						
						$lp = floor((ord($sa[$i]) * 256 + ord($sa[$i +1]) + 7) / 8);
						$i += $lp + 2;
						
						$lg = floor((ord($sa[$i]) * 256 + ord($sa[$i + 1]) + 7) / 8);
						$i += $lg + 2;
						
						$ly = floor((ord($sa[$i]) * 256 + ord($sa[$i + 1]) + 7)/8);
						$i += $ly + 2;
						
						$this->public_key = base64_encode(substr($s, $m, $lp + $lg + $ly + 6));
						
						// TODO: should this be adjusted as it was for RSA (above)..?
						
						$pkt = chr(0x99) . chr($len >> 8) . chr($len & 255) . substr($s, $k, $len);
						$fp = sha1($pkt);
						$this->fp = $fp;
						$this->key_id = substr($fp, strlen($fp) - 16, 16);
						$this->type = "ELGAMAL";
						$found = 3;
					} else {
						$i = $k + $len;
					}
			} else if ($tag == 13) {
					$this->user = substr($s, $i, $len);
					$i += $len;
				} else {
					$i += $len;
				}
		}
		
		if($found < 2) {  
			
			throw new Exception("Unable to parse Public Key");
// 			$this->version = "";
// 			$this->fp = "";
// 			$this->key_id = "";
// 			$this->user = ""; 
// 			$this->public_key = "";
		}
	}
	
	function GetExpandedKey()
	{
		$ek = new Expanded_Key($this->public_key);
	}
}

?>