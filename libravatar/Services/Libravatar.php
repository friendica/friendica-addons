<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PHP support for the Libravatar.org service.
 *
 * PHP version 5
 *
 * The MIT License
 *
 * Copyright (c) 2011 Services_Libravatar committers.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Services
 * @package   Services_Libravatar
 * @author    Melissa Draper <melissa@meldraweb.com>
 * @copyright 2011 Services_Libravatar committers.
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://pear.php.net/package/Services_Libravatar
 * @since     File available since Release 0.1.0
 */

/**
 * PHP support for the Libravatar.org service.
 *
 * Using this class is easy. After including or requiring
 * Services/Libravatar.php simply do:
 * <code>
 * $libravatar = new Services_Libravatar();
 * $url = $libravatar->getUrl('melissa@meldraweb.com');
 * </code>
 *
 * This would populate $url with the string:
 * <code>
 * http://cdn.libravatar.org/avatar/4db84629c121f2d443d33bdb9fd149bc
 * </code>
 *
 * A complicated lookup using all the options is:
 * <code>
 * $libravatar = new Services_Libravatar();
 * $libravatar->setSize(40);
 * $libravatar->setAlgorithm('sha256');
 * $libravatar->setHttps(true);
 * $libravatar->setDefault(
 *     'http://upload.wikimedia.org/wikipedia/commons/a/af/Tux.png'
 * );
 * $url = $libravatar->getUrl('melissa@meldraweb.com');
 * </code>
 *
 * @category  Services
 * @package   Services_Libravatar
 * @author    Melissa Draper <melissa@meldraweb.com>
 * @copyright 2011 Services_Libravatar committers.
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   Release: 0.2.1
 * @link      http://pear.php.net/package/Services_Libravatar
 * @since     Class available since Release 0.1.0
 */
class Services_Libravatar
{
    /**
     * Hashing algorithm to use
     *
     * @var string
     * @see processAlgorithm()
     * @see setAlgorithm()
     */
    protected $algorithm = 'md5';

    /**
     * Default image URL to use
     *
     * @var string
     * @see processDefault()
     * @see setDefault()
     */
    protected $default;

    /**
     * If HTTPS URLs should be used
     *
     * @var boolean
     * @see detectHttps()
     * @see setHttps()
     */
    protected $https;

    /**
     * Image size in pixels
     *
     * @var integer
     * @see processSize()
     * @see setSize()
     */
    protected $size;


    /**
     * Composes a URL for the identifier and options passed in
     *
     * Compose a full URL as specified by the Libravatar API, based on the
     * email address or openid URL passed in, and the options specified.
     *
     * @param string $identifier a string of either an email address
     *                           or an openid url
     * @param array  $options    an array of (bool) https, (string) algorithm
     *                           (string) size, (string) default.
     *                           See the set* methods.
     *
     * @return string A string of a full URL for an avatar image
     *
     * @since Method available since Release 0.2.0
     * @deprecated Use getUrl() instead
     */
    public function url($identifier, $options = array())
    {
        return $this->getUrl($identifier, $options);
    }

    /**
     * Composes a URL for the identifier and options passed in
     *
     * Compose a full URL as specified by the Libravatar API, based on the
     * email address or openid URL passed in, and the options specified.
     *
     * @param string $identifier a string of either an email address
     *                           or an openid url
     * @param array  $options    an array of (bool) https, (string) algorithm
     *                           (string) size, (string) default.
     *                           See the set* methods.
     *
     * @return string A string of a full URL for an avatar image
     *
     * @since  Method available since Release 0.2.0
     * @throws InvalidArgumentException When an invalid option is passed
     */
    public function getUrl($identifier, $options = array())
    {
        // If no identifier has been passed, set it to a null.
        // This way, there'll always be something returned.
        if (!$identifier) {
            $identifier = null;
        } else {
            $identifier = $this->normalizeIdentifier($identifier);
        }

        // Load all options
        $options = $this->checkOptionsArray($options);
        $https = $this->https;
        if (isset($options['https'])) {
            $https = (bool)$options['https'];
        }

        $algorithm = $this->algorithm;
        if (isset($options['algorithm'])) {
            $algorithm = $this->processAlgorithm($options['algorithm']);
        }

        $default = $this->default;
        if (isset($options['default'])) {
            $default = $this->processDefault($options['default']);
        }
        $size = $this->size;
        if (isset($options['size'])) {
            $size = $this->processSize($options['size']);
        }


        $identifierHash = $this->identifierHash($identifier, $algorithm);

        // Get the domain so we can determine the SRV stuff for federation
        $domain = $this->domainGet($identifier);

        // If https has been specified in $options, make sure we make the
        // correct SRV lookup
        $service  = $this->srvGet($domain, $https);
        $protocol = $https ? 'https' : 'http';

        $params = array();
        if ($size !== null) {
            $params['size'] = $size;
        }
        if ($default !== null) {
            $params['default'] = $default;
        }
        $paramString = '';
        if (count($params) > 0) {
            $paramString = '?' . http_build_query($params);
        }

        // Compose the URL from the pieces we generated
        $url = $protocol . '://' . $service . '/avatar/' . $identifierHash
            . $paramString;

        // Return the URL string
        return $url;
    }

    /**
     * Checks the options array and verify that only allowed options are in it.
     *
     * @param array $options Array of options for getUrl()
     *
     * @return void
     * @throws Exception When an invalid option is used
     */
    protected function checkOptionsArray($options)
    {
        //this short options are deprecated!
        if (isset($options['s'])) {
            $options['size'] = $options['s'];
            unset($options['s']);
        }
        if (isset($options['d'])) {
            $options['default'] = $options['d'];
            unset($options['d']);
        }

        $allowedOptions = array(
            'algorithm' => true,
            'default'   => true,
            'https'     => true,
            'size'      => true,
        );
        foreach ($options as $key => $value) {
            if (!isset($allowedOptions[$key])) {
                throw new InvalidArgumentException(
                    'Invalid option in array: ' . $key
                );
            }
        }

        return $options;
    }

    /**
     * Normalizes the identifier (E-mail address or OpenID)
     *
     * @param string $identifier E-Mail address or OpenID
     *
     * @return string Normalized identifier
     */
    protected function normalizeIdentifier($identifier)
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return strtolower($identifier);
        } else {
            return self::normalizeOpenId($identifier);
        }
    }

    /**
     * Create a hash of the identifier.
     *
     * Create a hash of the email address or openid passed in. Algorithm
     * used for email address ONLY can be varied. Either md5 or sha256
     * are supported by the Libravatar API. Will be ignored for openid.
     *
     * @param string $identifier A string of the email address or openid URL
     * @param string $hash       A string of the hash algorithm type to make
     *                           Uses the php implementation of hash()
     *                           MD5 preferred for Gravatar fallback
     *
     * @return string A string hash of the identifier.
     *
     * @since Method available since Release 0.1.0
     */
    protected function identifierHash($identifier, $hash = 'md5')
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL) || $identifier === null) {
            // If email, we can select our algorithm. Default to md5 for
            // gravatar fallback.
            return hash($hash, $identifier);
        }

        //no email, so the identifier has to be an OpenID
        return hash('sha256', $identifier);
    }

    /**
     * Normalizes an identifier (URI or XRI)
     *
     * @param mixed $identifier URI or XRI to be normalized
     *
     * @return string Normalized Identifier.
     *                Empty string when the OpenID is invalid.
     *
     * @internal Adapted from OpenID::normalizeIdentifier()
     */
    public static function normalizeOpenId($identifier)
    {
        // XRI
        if (preg_match('@^xri://@i', $identifier)) {
            return preg_replace('@^xri://@i', '', $identifier);
        }

        if (in_array($identifier[0], array('=', '@', '+', '$', '!'))) {
            return $identifier;
        }

        // URL
        if (!preg_match('@^http[s]?://@i', $identifier)) {
            $identifier = 'http://' . $identifier;
        }
        if (strpos($identifier, '/', 8) === false) {
            $identifier .= '/';
        }
        if (!filter_var($identifier, FILTER_VALIDATE_URL)) {
            return '';
        }

        $parts = parse_url($identifier);
        $parts['scheme'] = strtolower($parts['scheme']);
        $parts['host']   = strtolower($parts['host']);

        //http://openid.net/specs/openid-authentication-2_0.html#normalization
        return $parts['scheme'] . '://'
            . (isset($parts['user']) ? $parts['user'] : '')
            . (isset($parts['pass']) ? ':' . $parts['pass'] : '')
            . (isset($parts['user']) || isset($parts['pass']) ? '@' : '')
            . $parts['host']
            . (
                (isset($parts['port'])
                && $parts['scheme'] === 'http' && $parts['port'] != 80)
                || (isset($parts['port'])
                && $parts['scheme'] === 'https' && $parts['port'] != 443)
                ? ':' . $parts['port'] : ''
            )
            . $parts['path']
            . (isset($parts['query']) ? '?' . $parts['query'] : '');
            //leave out fragment as requested by the spec
    }

    /**
     * Grab the domain from the identifier.
     *
     * Extract the domain from the Email or OpenID.
     *
     * @param string $identifier A string of the email address or openid URL
     *
     * @return string A string of the domain to use
     *
     * @since Method available since Release 0.1.0
     */
    protected function domainGet($identifier)
    {
        if ($identifier === null) {
            return null;
        }

        // What are we, email or openid? Split ourself up and get the
        // important bit out.
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $email = explode('@', $identifier);
            return $email[1];
        }

        //OpenID
        $url    = parse_url($identifier);
        $domain = $url['host'];
        if (isset($url['port']) && $url['scheme'] === 'http'
            && $url['port'] != 80
            || isset($url['port']) && $url['scheme'] === 'https'
            && $url['port'] != 443
        ) {
            $domain .= ':' . $url['port'];
        }

        return $domain;
    }

    /**
     * Get the target to use.
     *
     * Get the SRV record, filtered by priority and weight. If our domain
     * has no SRV records, fall back to Libravatar.org
     *
     * @param string  $domain A string of the domain we extracted from the
     *                        provided identifier with domainGet()
     * @param boolean $https  Whether or not to look for https records
     *
     * @return string The target URL.
     *
     * @since Method available since Release 0.1.0
     */
    protected function srvGet($domain, $https = false)
    {

        // Are we going secure? Set up a fallback too.
        if (isset($https) && $https === true) {
            $subdomain = '_avatars-sec._tcp.';
            $fallback  = 'seccdn.';
        } else {
            $subdomain = '_avatars._tcp.';
            $fallback  = 'cdn.';
        }

        // Lets try get us some records based on the choice of subdomain
        // and the domain we had passed in.
        $srv = dns_get_record($subdomain . $domain, DNS_SRV);

        // Did we get anything? No?
        if (count($srv) == 0) {
            // Then let's try Libravatar.org.
            return $fallback . 'libravatar.org';
        }

        // Sort by the priority. We must get the lowest.
        usort($srv, array($this, 'comparePriority'));

        $top = $srv[0];
        $sum = 0;

        // Try to adhere to RFC2782's weighting algorithm, page 3
        // "arrange all SRV RRs (that have not been ordered yet) in any order,
        // except that all those with weight 0 are placed at the beginning of
        // the list."
        shuffle($srv);
        $srvs = array();
        foreach ($srv as $s) {
            if ($s['weight'] == 0) {
                array_unshift($srvs, $s);
            } else {
                array_push($srvs, $s);
            }
        }

        foreach ($srvs as $s) {
            if ($s['pri'] == $top['pri']) {
                // "Compute the sum of the weights of those RRs"
                $sum += (int) $s['weight'];
                // "and with each RR associate the running sum in the selected
                // order."
                $pri[$sum] = $s;
            }
        }

        // "Then choose a uniform random number between 0 and the sum computed
        // (inclusive)"
        $random = rand(0, $sum);

        // "and select the RR whose running sum value is the first in the selected
        // order which is greater than or equal to the random number selected"
        foreach ($pri as $k => $v) {
            if ($k >= $random) {
                return $v['target'];
            }
        }
    }

    /**
     * Sorting function for record priorities.
     *
     * @param mixed $a A mixed value passed by usort()
     * @param mixed $b A mixed value passed by usort()
     *
     * @return mixed The result of the comparison
     *
     * @since Method available since Release 0.1.0
     */
    protected function comparePriority($a, $b)
    {
        return $a['pri'] - $b['pri'];
    }

    /**
     * Automatically set the https option depending on the current connection
     * value.
     *
     * If the current connection is HTTPS, the https options is activated.
     * If it is not HTTPS, the https option is deactivated.
     *
     * @return self
     */
    public function detectHttps()
    {
        $this->setHttps(
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
        );

        return $this;
    }

    /**
     * Verify and cast the email address hashing algorithm to use.
     *
     * @param string $algorithm Algorithm to use, "sha256" or "md5".
     *
     * @return string Algorithm
     *
     * @throws InvalidArgumentException When an unsupported algorithm is given
     */
    protected function processAlgorithm($algorithm)
    {
        $algorithm = (string)$algorithm;
        if ($algorithm !== 'md5' && $algorithm !== 'sha256') {
            throw new InvalidArgumentException(
                'Only md5 and sha256 hashing supported'
            );
        }

        return $algorithm;
    }

    /**
     * Verify and cast the default URL to use when no avatar image can be found.
     * If none is set, the libravatar logo is returned.
     *
     * @param string $url Full URL to use OR one of the following:
     *                    - "404" - give a "404 File not found" instead of an image
     *                    - "mm"
     *                    - "identicon"
     *                    - "monsterid"
     *                    - "wavatar"
     *                    - "retro"
     *
     * @return string Default URL
     *
     * @throws InvalidArgumentException When an invalid URL is given
     */
    protected function processDefault($url)
    {
        if ($url === null) {
            return $url;
        }

        $url = (string)$url;

        switch ($url) {
        case '404':
        case 'mm':
        case 'identicon':
        case 'monsterid':
        case 'wavatar':
        case 'retro':
            break;
        default:
            $valid = filter_var($url, FILTER_VALIDATE_URL);
            if (!$valid) {
                throw new InvalidArgumentException('Invalid default avatar URL');
            }
            break;
        }

        return $url;
    }

    /**
     * Verify and cast the required size of the images.
     *
     * @param integer $size Size (width and height in pixels) of the image.
     *                      NULL for the default width.
     *
     * @return integer Size
     *
     * @throws InvalidArgumentException When a size <= 0 is given
     */
    protected function processSize($size)
    {
        if ($size === null) {
            return $size;
        }

        $size = (int)$size;
        if ($size <= 0) {
            throw new InvalidArgumentException('Size has to be larger than 0');
        }

        return (int)$size;
    }


    /**
     * Set the email address hashing algorithm to use.
     * To keep gravatar compatibility, use "md5".
     *
     * @param string $algorithm Algorithm to use, "sha256" or "md5".
     *
     * @return self
     * @throws InvalidArgumentException When an unsupported algorithm is given
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $this->processAlgorithm($algorithm);

        return $this;
    }

    /**
     * Set the default URL to use when no avatar image can be found.
     * If none is set, the gravatar logo is returned.
     *
     * @param string $url Full URL to use OR one of the following:
     *                    - "404" - give a "404 File not found" instead of an image
     *                    - "mm"
     *                    - "identicon"
     *                    - "monsterid"
     *                    - "wavatar"
     *                    - "retro"
     *
     * @return self
     * @throws InvalidArgumentException When an invalid URL is given
     */
    public function setDefault($url)
    {
        $this->default = $this->processDefault($url);

        return $this;
    }

    /**
     * Set if HTTPS URLs shall be returned.
     *
     * @param boolean $useHttps If HTTPS url shall be returned
     *
     * @return self
     *
     * @see detectHttps()
     */
    public function setHttps($useHttps)
    {
        $this->https = (bool)$useHttps;

        return $this;
    }

    /**
     * Set the required size of the images.
     * Every avatar image is square sized, which means you need to set only number.
     *
     * @param integer $size Size (width and height) of the image
     *
     * @return self
     * @throws InvalidArgumentException When a size <= 0 is given
     */
    public function setSize($size)
    {
        $this->size = $this->processSize($size);

        return $this;
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
