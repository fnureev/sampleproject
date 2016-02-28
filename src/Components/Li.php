<?php
namespace Components;

class Li
{
    private $url;
    private $curl;
    private $page = 1;
    private $minHits;

    public function __construct($url, $minHits)
    {
        $this->url = $url;
        $this->minHits = $minHits;

        $this->curl = new CURL;
        $this->curl->setCookie('li');
    }

    private function getPageUrl()
    {
        return $this->url."/today.tsv?page=".$this->page;
    }

    private function parseSites()
    {
        $this->curl->url = $this->getPageUrl();
        $content = $this->curl->send();
        $sites = explode("\n", $content);
        array_shift($sites);
        $this->page++;

        $return = [];

        foreach ($sites as $line) {
            $opts = explode("\t", $line);
            if (empty($opts[1])) {
                continue;
            }

            $url = $opts[1];
            $hits = str_replace([' ', '.', ','], '', $opts[3]);

            if ($hits < $this->minHits) {
                return $return;
            }

            $return[] = $url;
        }

        return $return;
    }

    private function sitesGenerator()
    {
        while ($sites = $this->parseSites()) {
            foreach ($sites as $site) {
                yield $site;
            }
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'sites':
                return $this->sitesGenerator();
            default:
                return null;
        }
    }
}
