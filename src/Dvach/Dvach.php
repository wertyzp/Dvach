<?php

namespace Dvach;

class Dvach extends WebLoader {

    private $url = "https://2ch.hk";

    public function getBoards($flat = true) {
        $doc = $this->loadHTML($this->url);
        $xpath = $xpath = new \DOMXPath($doc);
        $elements = $xpath->query("//*[@id='LakeNavForm']/optgroup");
        $result = array();
        foreach ($elements as $element) {
            $label = $element->getAttribute('label');
            /* @var $element \DOMElement */
            if (!$flat) {
                $result[$label] = array();
            }
            foreach ($element->childNodes as $node) {
                /* @var $node \DOMElement */
                if (!$flat) {
                    $result[$label][] = $node->getAttribute("value");
                } else {
                    $result[] = $node->getAttribute("value");
                }
            }
        }
        return $result;
    }

    public function getPages($board) {
        $url = "{$this->url}{$board}index.json";
        $json = $this->loadJSON($url);
        return $json['pages'];
    }

    public function getThreads($board) {
        // https://2ch.hk/b/catalog_num.json
        $url = "$this->url/$board/catalog_num.json";
        return $this->loadJSON($url);
   }

    public function getThread($board, $thread) {
        $url = "{$this->url}$board/res/$thread.json";
        $result = $this->loadJSON($url);
        if (!is_array($result['threads'])) {
            throw new Exception("Bad thread data");
        }
        return $result;
    }
    
    public function getThreadFiles($board, $thread, $flat = true, $absoluteUrl = true) {
        $thread = $this->getThread($board, $thread);
        $result = array();
        foreach ($thread['posts'] as $post) {
            if (isset($post['files'])) {
                foreach ($post['files'] as $file) {
                    if(!$flat) {
                        $result[] = $file;
                    } else {
                        if(!$absoluteUrl) {
                            $result[] = $file['path'];
                        } else {
                            $result[] = "{$this->url}$board{$file['path']}";
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    public function getThreadPosts($board, $threadNum) {
        return $this->getThreadPostsByIndex($board, $threadNum, 1);
    }
    
    public function getThreadPostsByNum($board, $threadNum, $postNum) {
        $b = trim($board, "/");
        $url = "{$this->url}/makaba/mobile.fcgi?task=get_thread&board=$b&thread=$threadNum&num=$postNum";
        $result = $this->loadJSON($url);
        return $result;
    }
    
    public function getThreadPostsByIndex($board, $threadNum, $postIndex = 1) {
        $b = trim($board, "/");
        $url = "{$this->url}/makaba/mobile.fcgi?task=get_thread&board=$b&thread=$threadNum&post=$postIndex";
        $result = $this->loadJSON($url);
        return $result;
        //https://2ch.hk/makaba/mobile.fcgi?task=get_thread&board=b&thread=137975456&post=1
    }
    
    public function getFullLink($filepath, $board) {
        return "$this->url/$board/$filepath";
    }
}
