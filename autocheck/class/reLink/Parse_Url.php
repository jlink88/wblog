<?php
/**
 * Created by PhpStorm.
 * User: rel
 * Date: 22/06/2016
 * Time: 11:53 AM
 */

namespace reLink;


//TODO: perform the additional checks on each url:
// is it a valid url? does the url contain a path? eg http://www.host.com/PATH? if not then this is probably not a valid link.
// does the host redirect somewhere else? curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
// does it use http or https scheme?
// parse_url says there is a 'path' when url ends in '/' so we have to check whether there is anything else after the /
// does the url begin with http or https? if not report it to log



class Parse_Url
{
    public $scheme;
    public $host;
    public $cleanHost;
    public $path = null;
    public $query = null;
    public $redirectsTo;
    public $originalUrl = '';
    public $fixedUrl = null;
    public $isValid = false;
    public $isHome = false;
    public $hasSpaces = null;
    public $errors = array();
    public $default_errors = array();

    public function __construct($url)
    {
        $this->set_default_errors();
        $this->setUp($url);
    }

    /**
     * @param $url
     */

    public function setUp($url) {
        $this->originalUrl = $url;
        $url = strtolower(trim($url));
        $isValid =  $this->isValidUrl($url);

        if ( $isValid === true || is_array($isValid) ) {
            if ( is_array($isValid) ) {
                $parsedUrl = parse_url($isValid[1]);
                $this->fixedUrl = $isValid[1];
                // We are assuming our url becomes valid after we added the missing scheme and try again
                $hasPath = $this->hasPath($this->fixedUrl);
                $this->set_error('added_scheme');
            } else {
                $parsedUrl = parse_url($url);
                $hasPath = $this->hasPath($url);
                $this->isValid = true;
            }

            $this->host = $parsedUrl['host'];
            $this->cleanHost = $this->removeWww($parsedUrl['host']);
            $this->scheme = $parsedUrl['scheme'];

            if ( ($hasPath && $parsedUrl['path'] === "/" && !$this->hasQuery($url)) || (!$hasPath &&  !$this->hasQuery($url)) ) {
                $this->isHome = true;
            }
            if (isset($parsedUrl['path'])) {
                $this->path = $parsedUrl['path'];
            }
        }
        else {
            $this->set_error('invalid_url');
        }
    }

    protected function set_default_errors() {
        $this->default_errors['invalid_url'] = "Not a valid URL";
        $this->default_errors['added_scheme'] = "Link is valid when adding http and/or https protocol";
    }

    private function removeWww($host) {
        $host   = str_replace("www.", "", $host);
        return trim($host);
    }

    /**
     * @param $URL
     * @return mixed
     */

    public function getEffectiveUrl($url)
    {
        //TODO: add support for https
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $code;
    }


    /**
     * @param $url
     * @return bool
     */

    public function hasPath($url) {
        if ( filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) ) {
            return true;
        } else {
            return false;
        }
    }

    public function hasQuery($url) {
        if ( filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $url
     * @param null $fixed
     * @return array|bool
     */

    public function isValidUrl($url,$fixed = null)
    {
        $response = false;
        if ( ! preg_match("#^https?://#",$url) ) {
            $url = "http://" . $url;
            $response = $this->isValidUrl($url, true);
        } elseif ( filter_var($url, FILTER_VALIDATE_URL) ) {
            $host = self::urlToHost($url);
            if ( !strpos($host,".")) {
                $response = false;
            } elseif ( $fixed === true ) {
                $response = array(false, $url);
            } else {
                $response = true;
            }
        } else {
            $response = false;
        }
        return $response;
    }

    /**
     * @param $url
     * @return bool
     */

    public function hasScheme($url) {
        if ( filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $error
     */

    protected function set_error( $error ) {
        //TODO: Use monolog
        if ( in_array($this->default_errors[$error],$this->default_errors)) {
            $this->errors[$error] = $this->default_errors[$error];
        }
    }

    /**
     * @param bool $default
     * @return array
     */

    public function get_errors ($default = true) {
        //TODO: Use monolog
        if ( $default ) {
            $errors =  $this->default_errors;
        } else {
            $errors = $this->errors;
        }
        return $errors;
    }

    /**
     * @param $url
     * @return string
     */
    
   public static function urlToHost($url)
{
    $parsed = parse_url($url);
    $host   = $parsed['host'];
    $host   = preg_replace('~[^a-zA-Z0-9\.]+~', '', $host);
    $host   = str_replace("www.", "", $host);
    return trim($host);
}

}