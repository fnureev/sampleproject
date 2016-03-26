<?php
namespace Components;

class CURL
{
    public $json = false;
    public $cookieDirectory = 'cookies';

    protected $handler;
    protected $options = [
        'returntransfer' => CURLOPT_RETURNTRANSFER,
        'url' => CURLOPT_URL,
        'referer' => CURLOPT_REFERER,
        'headers' => CURLOPT_HTTPHEADER,
        'header' => CURLOPT_HEADER,
        'followlocation' => CURLOPT_FOLLOWLOCATION,
        'useragent' => CURLOPT_USERAGENT,
        'timeout' => CURLOPT_TIMEOUT,
        'post' => CURLOPT_POST,
        'postfields' => CURLOPT_POSTFIELDS,
        'cookiejar' => CURLOPT_COOKIEJAR,
        'cookiefile' => CURLOPT_COOKIEFILE,
    ];
    protected $defaultOptions = [
        'returntransfer' => 1,
        'url' => '',
        'referer' => '',
        'headers' => [],
        'header' => false,
        'followlocation' => true,
        'useragent' => 'Mozilla/5.0 (Windows NT 6.3; rv:38.0) Gecko/20100101 Firefox/38.0',
        'timeout' => 5,
        'post' => false,
        'postfields' => [],
        'cookiejar' => '',
        'cookiefile' => '',
    ];
    private $values = [];

    public function __construct()
    {
        $this->handler = curl_init();
        $this->resetOptions();
    }

    public function __destruct()
    {
        curl_close($this->handler);
        $this->clearCookie();
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'returnHeaders':
                if ($value == 1) {
                    $this->header = 1;
                    $this->followlocation = 0;
                } else {
                    $this->header = 0;
                    $this->followlocation = 1;
                }
                break;
            case 'postData':
                if (is_array($value)) {
                    if ($this->json) {
                        $value = json_encode($value);
                    } else {
                        $value = http_build_query($value);
                    }
                }

                if (!empty($value)) {
                    $this->post = 1;
                    $this->postfields = $value;
                } else {
                    $this->post = 0;
                    $this->postfields = '';
                }
                break;
            case 'cookie':
                $this->cookiejar = $value;
                $this->cookiefile = $value;
                break;
            default:
                if (isset($this->options[$name])) {
                    curl_setopt($this->handler, $this->options[$name], $value);
                }
                break;
        }
        $this->values[$name] = $value;
    }

    public function __get($name)
    {
        if (!isset($this->values[$name])) {
            return null;
        }

        return $this->values[$name];
    }

    public function setCookie($name)
    {
        if (!empty($this->cookie)) {
            $this->clearCookie();
        }

        $this->cookie = $this->cookieDirectory . DIRECTORY_SEPARATOR. $name . ".txt";
    }

    public function clearCookie()
    {
        if (file_exists($this->cookie)) {
            unlink($this->cookie);
        }

        $this->cookie = '';
    }

    private function resetOptions()
    {
        foreach ($this->defaultOptions as $key => $value) {
            $this->$key = $value;
        }
    }

    public function send()
    {
        return curl_exec($this->handler);
    }

    public function getredirects($url)
    {
        $redirects = [];
        $this->nohead = false;
        $redirects[] = $url;
        $ref = $url;

        while (1) {
            $newurl = '';
            $this->url = $url;
            $this->referal = $ref;

            $page = $this->send();

            if (preg_match('#Location:\s*([^\n]+)\n#im', $page, $nurl)) {
                $newurl = trim($nurl[1]);
            } elseif (preg_match('#location.replace\(["\']([^"\']+)["\']\)#im', $page, $nurl)) {
                $newurl = trim($nurl[1]);
            } elseif (preg_match('#<meta http-equiv=[\'"](location|refresh)[\'"]'
                    . 'content=["\']([^;]+;)?\s*(URL=)?([^"\']+)["\']/?>#im', $page, $nurl)) {
                $newurl = trim($nurl[4]);
            } elseif (preg_match('#Refresh: 0;url=([^\n]+)\n#im', $page, $nurl)) {
                $newurl = trim($nurl[1]);
            } elseif (preg_match('#(document|window)\.location(\.href)?\s*=\s*["\']([^"\']+)["\']#im', $page, $nurl)) {
                $newurl = trim($nurl[3]);
            }

            if ($newurl == '.') {
                $parsed_url = parse_url($url);

                $newurl = $parsed_url['scheme']."://".$parsed_url['host']."/".ltrim($parsed_url['path'], '/');
            }

            if (strpos($newurl, 'http') === false and !empty($newurl)) {
                $parsed_url1 = parse_url($url);
                $parsed_url2 = parse_url($newurl);

                if (!isset($parsed_url2['host']) and isset($parsed_url2['path']) and isset($parsed_url1['scheme'])) {
                    $newurl = $parsed_url1['scheme']."://".$parsed_url1['host']."/".ltrim($parsed_url2['path'], '/');
                } else {
                    $newurl = '';
                }
            }

            $newurl = preg_replace(array('#utm_[^&]+&?#'), '', trim(urldecode($newurl)));

            if (empty($newurl) or in_array($newurl, $redirects)) {
                break;
            }

            $redirects[] = trim($newurl, '/');

            $ref = $url;
            $url = $newurl;
        }
        return $redirects;
    }
}
