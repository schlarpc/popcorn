<?php

class Shoebox {
    function __construct() {
        $this->updateLibrary();
    }
    
    private function getHTTP($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'android-async-http/1.4.1 (http://loopj.com/android-async-http)');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    private function getHTTPJSON($url) {
        return json_decode($this->getHTTP($url), true);
    }
    
    private function getLocalJSON($path) {
        return json_decode(file_get_contents($path), true);
    }
    
    private function getEntryById($arr, $id) {
        foreach ($arr as $entry) {
            if ($entry['id'] == $id) {
                return $entry;
            }
        }
    }
    
    private function decodeEmbedURL($apple, $google, $microsoft, $key) {
        $oid = $apple + $key;
        $id = $google + $key;
        $hash = $microsoft;
        return "http://vk.com/video_ext.php?oid=$oid&id=$id&hash=$hash";
    }
    
    private function getStreamURL($embed) {
        $page = $this->getHTTP($embed);
        preg_match('/<source src="(.+?)"/', $page, $matches);
        return $matches[1];
    }
    
    private function sortByRating($a, $b) {
        return intval($b['rating']) - intval($a['rating']);
    }
    
    private function updateLibrary() {
        if (!is_dir('./shoebox_cache/')) {
            mkdir('./shoebox_cache/', 0777, true);
        }
        if (!file_exists('./shoebox_cache/data_en.zip') || time() - filemtime('./shoebox_cache/data_en.zip') > 24 * 60 * 60) {
            file_put_contents('./shoebox_cache/data_en.zip', $this->getHTTP('http://showsbox.ru/data/data_en.zip'));
            $zip = zip_open('./shoebox_cache/data_en.zip');
            do {
                $entry = zip_read($zip);
                if (is_resource($entry)) {
                    $name = zip_entry_name($entry);
                    if ($name === 'movies_lite.json' || $name === 'tv_lite.json') {
                        file_put_contents("./shoebox_cache/" . $name, zip_entry_read($entry, zip_entry_filesize($entry)));
                    }
                }
            } while ($entry);
        }
    }
    
    public function getMovieList() {
        $list = $this->getLocalJSON('./shoebox_cache/movies_lite.json');
        usort($list, array($this, 'sortByRating'));
        return $list;
    }
    
    public function getTVList() {
        $list = $this->getLocalJSON('./shoebox_cache/tv_lite.json');
        usort($list, array($this, 'sortByRating'));
        return $list;
    }
    
    public function getMovieData($id, $resolve_embed=FALSE) {
        $local = $this->getEntryById($this->getMovieList(), $id);
        $local = array_merge($local, $this->getHTTPJSON("http://mobapps.cc/api/serials/get_movie_data/?id=$id"));
        foreach ($local['langs'] as &$lang) {
            $lang['embed'] = $this->decodeEmbedURL($lang['apple'], $lang['google'], $lang['microsoft'], 537 + $id);
            if ($resolve_embed) {
                $lang['stream'] = $this->getStreamURL($lang['embed']);
            }
        }
        return $local;
    }
    
    public function getTVData($id) {
        $local = $this->getEntryById($this->getTVList(), $id);
        $local['season_info'] = array();
        for ($season = $local['seasons']; $season > ($local['zero_season'] ? -1 : 0); $season--) {
            $local['season_info'][$season] = $this->getHTTPJSON("http://mobapps.cc/api/serials/es?id=$id&season=$season");
            $local['season_info'][$season]['episodes'] = count($local['season_info'][$season]['titles']);
        }
        return $local;
    }
    
    public function getEpisodeData($id, $season, $episode, $resolve_embed=FALSE) {
        $local = $this->getEntryById($this->getTVList(), $id);
        $local = array_merge($local, $this->getHTTPJSON("http://mobapps.cc/api/serials/es?id=$id&season=$season"));
        $local['langs'] = $this->getHTTPJSON("http://mobapps.cc/api/serials/e?h=$id&u=$season&y=$episode");
        foreach ($local['langs'] as &$lang) {
            $lang['embed'] = $this->decodeEmbedURL($lang['apple'], $lang['google'], $lang['microsoft'], $id + $season + $episode);
            if ($resolve_embed) {
                $lang['stream'] = $this->getStreamURL($lang['embed']);
            }
        }
        $local['episode_title'] = $local['titles'][$episode];
        unset($local['titles']);
        $local['thumb'] = $local['thumbs'][$episode];
        unset($local['thumbs']);
        return $local;
    }
}

//$sb = new Shoebox();
//print_r($sb->getMovieData(3432));
//print_r($sb->getTVData(5));
//print_r($sb->getEpisodeData(5, 3, 1));