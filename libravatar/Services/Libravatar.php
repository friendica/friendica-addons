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
 * @version   SVN: <package_version>
 * @link      http://pear.php.net/package/Services_Libravatar
 * @since     File available since Release 0.1.0
 */

/**
 * PHP support for the Libravatar.org service.
 *
 * Using this class is easy. After including or requiring
 * PEAR/libravatar.php simply do:
 *  <code>
 *   $libravatar = new Services_Libravatar();
 *   $url = $libravatar->url('melissa@meldraweb.com');
 *  </code>
 *
 *  This would populate $url with the string:
 *  http://cdn.libravatar.org/avatar/4db84629c121f2d443d33bdb9fd149bc
 *
 * A complicated lookup using all the options is:
 *  <code>
 *   $libravatar = new Services_Libravatar();
 *   $options = array();
 *   $options['s'] = '40';
 *   $options['algorithm'] = 'sha256';
 *   $options['https'] = true;
 *   $options['d'] = 'http://upload.wikimedia.org/wikipedia/commons/a/af/Tux.png';
 *   $url = $libravatar->url('melissa@meldraweb.com', $options);
 *  </code>
 *
 * @category  Services
 * @package   Services_Libravatar
 * @author    Melissa Draper <melissa@meldraweb.com>
 * @copyright 2011 Services_Libravatar committers.
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   Release: <package_version>
 * @link      http://pear.php.net/package/Services_Libravatar
 * @since     Class available since Release 0.1.0
 */
class Services_Libravatar
{

    /**
     *  Composes a URL for the identifier and options passed in
     *
     *  Compose a full URL as specified by the Libravatar API, based on the
     *  email address or openid URL passed in, and the options specified.
     *
     *  @param string $identifier a string of either an email address
     *                            or an openid url
     *  @param array  $options    an array of (bool) https, (string) algorithm
     *                            (string) s or size, (string) d or default
     *
     *  @return  string  A string of a full URL for an avatar image
     *
     *  @since Method available since Release 0.1.0
     */
    public function url($identifier, $options = array())
    {

        // If no identifier has been passed, set it to a null.
        // This way, there'll always be something returned.
        if (!$identifier) {
            $identifier = null;
        }

        $https = null;
        if (isset($options['https']) && $options['https'] === true) {
            $https = true;
        }

        // If the algorithm has been passed in $options, send it on.
        // This will only affect email functionality.
        if (isset($options['algorithm']) && is_string($options['algorithm'])) {
            $identiferHash = $this->identiferHash(
                $identifier,
                $https,
                $options['algorithm']
            );
        } else {
            $identiferHash = $this->identiferHash($identifier, $https);
        }

        // Get the domain so we can determine the SRV stuff for federation
        $domain = $this->domainGet($identifier, $https);

        // If https has been specified in $options, make sure we make the
        // correct SRV lookup
        if (isset($options['https']) && $options['https'] === true) {
            $service  = $this->srvGet($domain, true);
            $protocol = 'https';
        } else {
            $service  = $this->srvGet($domain);
            $protocol = 'http';
        }

        // We no longer need these, and they will pollute our query string
        unset($options['algorithm']);
        unset($options['https']);

        // If there are any $options left, we want to make those into a query
        $params = null;
        if (count($options) > 0) {
            $params = '?' . http_build_query($options);
        }

        // Compose the URL from the pieces we generated
        $url = $protocol . '://' . $service . '/avatar/' . $identiferHash . $params;

        // Return the URL string
        return $url;

    }

    /**
     *  Create a hash of the identifier.
     *
     *  Create a hash of the email address or openid passed in. Algorithm
     *  used for email address ONLY can be varied. Either md5 or sha256
     *  are supported by the Libravatar API. Will be ignored for openid.
     *
     *  @param string  $identifier A string of the email address or openid URL
     *  @param boolean $https      If this is https, true.
     *  @param string  $hash       A string of the hash algorithm type to make
     *
     *  @return string  A string hash of the identifier.
     *
     *  @since Method available since Release 0.1.0
     */
    protected function identiferHash($identifier, $https = false, $hash = 'md5')
    {

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // If email, we can select our algorithm. Default to md5 for
            // gravatar fallback.
            return hash($hash, $identifier);
        } else {

            // The protocol is important. If we're lacking it this will not be
            // filtered. Add it per our preference in the options.
            if (stripos($identifier, 'http') !== 0) {
                if ($https === true) {
                    $protocol = 'https://';
                } else {
                    $protocol = 'http://';
                }
                $identifier = $protocol . $identifier;
            }

            // Is this an email address or an OpenID account
            $filter = filter_var(
                $identifier,
                FILTER_VALIDATE_URL,
                FILTER_FLAG_PATH_REQUIRED
            );

            if ($filter) {
                // If this is an OpenID, split the string and make sure the
                // formatting is correct. See the Libravatar API for more info.
                // http://wiki.libravatar.org/api/
                $url     = parse_url($identifier);
                $hashurl = strtolower($url['scheme']) . '://' .
                           strtolower($url['host']);
                if (isset($url['port']) && $url['scheme'] === 'http'
                    && $url['port'] != 80
                    || isset($url['port']) && $url['scheme'] === 'https'
                    && $url['port'] != 443
                ) {
                    $hashurl .= ':' . $url['port'];
                }
                $hashurl .= $url['path'];
                return hash('sha256', $hashurl);
            }
        }
    }

    /**
     *  Grab the domain from the identifier.
     *
     *  Extract the domain from the Email or OpenID.
     *
     *  @param string  $identifier A string of the email address or openid URL
     *  @param boolean $https      If this is https, true.
     *
     *  @return string  A string of the domain to use
     *
     *  @since Method available since Release 0.1.0
     */
    protected function domainGet($identifier, $https = false)
    {

        // What are we, email or openid? Split ourself up and get the
        // important bit out.
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $email = explode('@', $identifier);
            return $email[1];
        } else {

            // The protocol is important. If we're lacking it this will not be
            // filtered. Add it per our preference in the options.
            if ( ! strpos($identifier, 'http')) {
                if ($https === true) {
                    $protocol = 'https://';
                } else {
                    $protocol = 'http://';
                }
                $identifier = $protocol . $identifier;
            }

            $filter = filter_var(
                $identifier,
                FILTER_VALIDATE_URL,
                FILTER_FLAG_PATH_REQUIRED
            );

            if ($filter) {
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
        }
    }

    /**
     *  Get the target to use.
     *
     *  Get the SRV record, filtered by priority and weight. If our domain
     *  has no SRV records, fall back to Libravatar.org
     *
     *  @param string  $domain A string of the domain we extracted from the
     *                         provided identifer with domainGet()
     *  @param boolean $https  Whether or not to look for https records
     *
     *  @return string  The target URL.
     *
     *  @since Method available since Release 0.1.0
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
     *  Sorting function for record priorities.
     *
     *  @param mixed $a A mixed value passed by usort()
     *  @param mixed $b A mixed value passed by usort()
     *
     *  @return mixed  The result of the comparison
     *
     *  @since Method available since Release 0.1.0
     */
    protected function comparePriority($a, $b)
    {
        return $a['pri'] - $b['pri'];
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

