<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace wrxswoole\Core\Http;

use wrxswoole\Core\Trace\Traits\TraceTrait;

/**
 * Message represents a base HTTP message.
 *
 * @property string $content Raw body.
 * @property CookieCollection|Cookie[] $cookies The cookie collection. Note that the type of this property
 *           differs in getter and setter. See [[getCookies()]] and [[setCookies()]] for details.
 * @property mixed $data Content data fields.
 * @property string $format Body format name.
 * @property HeaderCollection $headers The header collection. Note that the type of this property differs in
 *           getter and setter. See [[getHeaders()]] and [[setHeaders()]] for details.
 *          
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Message
{

    use TraceTrait;

    public $client;

    /**
     *
     * @var [] headers.
     */
    public $headers = [
        "user-agent" => 'wrxswoole/1.0.1'
    ];

    /**
     *
     * @var [] cookies.
     */
    public $cookies = [];

    /**
     *
     * @var string|null raw content
     */
    private $content;

    /**
     *
     * @var mixed content data
     */
    private $data;

    /**
     *
     * @var string content format name
     */
    private $format;

    /**
     * Sets the HTTP headers associated with HTTP message.
     *
     * @param array $headers
     *            headers collection or headers list in format: [headerName => headerValue]
     * @return $this self reference.
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Returns the header collection.
     * The header collection contains the HTTP headers associated with HTTP message.
     *
     * @return [] the header collection
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Adds more headers to the already defined ones.
     *
     * @param array $headers
     *            additional headers in format: [headerName => headerValue]
     * @return $this self reference.
     */
    public function addHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Checks of HTTP message contains any header.
     * Using this method you are able to check cookie presence without instantiating [[HeaderCollection]].
     *
     * @return bool whether message contains any header.
     */
    public function hasHeaders()
    {
        return ! empty($this->headers);
    }

    /**
     * Sets the cookies associated with HTTP message.
     *
     * @param array $cookies
     *            cookie collection or cookies list.
     * @return $this self reference.
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * Returns the cookie collection.
     * The cookie collection contains the cookies associated with HTTP message.
     *
     * @return [] the cookie collection.
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Adds more cookies to the already defined ones.
     *
     * @param array $cookies
     *            additional cookies.
     * @return $this self reference.
     */
    public function addCookies(array $cookies)
    {
        $this->cookies = array_merge($this->cookies, $cookies);
        return $this;
    }

    /**
     * Checks of HTTP message contains any cookie.
     * Using this method you are able to check cookie presence without instantiating [[CookieCollection]].
     *
     * @return bool whether message contains any cookie.
     */
    public function hasCookies()
    {
        if (is_object($this->cookies)) {
            return $this->cookies->getCount() > 0;
        }
        return ! empty($this->cookies);
    }

    /**
     * Sets the HTTP message raw content.
     *
     * @param string $content
     *            raw content.
     * @return $this self reference.
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Returns HTTP message raw content.
     *
     * @return string raw body.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Checks if content with provided name exists
     *
     * @param $key string
     *            Name of the content parameter
     * @return bool
     * @since 2.0.10
     */
    public function hasContent($key)
    {
        $content = $this->getContent();
        return is_array($content) && isset($content[$key]);
    }

    /**
     * Sets the data fields, which composes message content.
     *
     * @param mixed $data
     *            content data fields.
     * @return $this self reference.
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns the data fields, parsed from raw content.
     *
     * @return mixed content data fields.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Adds data fields to the existing ones.
     *
     * @param array $data
     *            additional content data fields.
     * @return $this self reference.
     * @since 2.0.1
     */
    public function addData($data)
    {
        if (empty($this->data)) {
            $this->data = $data;
        } else {
            if (! is_array($this->data)) {
                $this->error($this->data, 'Unable to merge existing data with new data. Existing data is not an array.');
            }
            $this->data = array_merge($this->data, $data);
        }
        return $this;
    }

    /**
     * Sets body format.
     *
     * @param string $format
     *            body format name.
     * @return $this self reference.
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Returns body format.
     *
     * @return string body format name.
     */
    public function getFormat()
    {
        if ($this->format === null) {
            $this->format = $this->defaultFormat();
        }
        return $this->format;
    }

    /**
     * Returns default format name.
     *
     * @return string default format name.
     */
    protected function defaultFormat()
    {
        return Request::FORMAT_URL_ENCODED;
    }

    /**
     * Composes raw header lines from [[headers]].
     * Each line will be a string in format: 'header-name: value'.
     *
     * @return array raw header lines.
     */
    public function composeHeaderLines()
    {
        if (! $this->hasHeaders()) {
            return [];
        }
        $headers = [];
        foreach ($this->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }
        return $headers;
    }

    /**
     * Returns string representation of this HTTP message.
     *
     * @return string the string representation of this HTTP message.
     */
    public function toString()
    {
        $result = '';
        if ($this->hasHeaders()) {
            $headers = $this->composeHeaderLines();
            $result .= implode("\n", $headers);
        }

        $content = $this->getContent();
        if ($content !== null) {
            $result .= "\n\n" . $content;
        }

        return $result;
    }

    /**
     * PHP magic method that returns the string representation of this object.
     *
     * @return string the string representation of this object.
     */
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->toString();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}