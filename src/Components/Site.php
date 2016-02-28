<?php
namespace Components;

class Site
{
    public static function check($url, $domains)
    {
        $curl = new CURL;
        $curl->setCookie('redirect');

        $redirects = $curl->getredirects($url);

        foreach ($redirects as $redirect) {
            foreach ($domains as $domain) {
                if (strpos($redirect, $domain) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
