<?php

namespace Dvach;

class Dvach extends WebLoader {

    private $url;

    public function __construct($url = "https://2ch.hk") {
        $this->url = $url;
    }

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

    public function getThreads($board, $page = 0, $numsOnly = false) {
        if ($page == 0) {
            $page = 'index';
        }
        $url = "{$this->url}{$board}{$page}.json";
        $result = $this->loadJSON($url);
        if (!$numsOnly) {
            return $result['threads'];
        }
        $threadNums = array();
        foreach ($result['threads'] as $thread) {
            $threadNums[] = $thread['thread_num'];
        }
        return $threadNums;
    }

    public function getThread($board, $thread) {
        $url = "{$this->url}$board/res/$thread.json";
        $result = $this->loadJSON($url);
        if (!isset($result['threads'])) {
            throw new Exception("Thread load failed");
        }

        if (!is_array($result['threads'])) {
            throw new Exception("Bad thread data");
        }

        return array_pop($result['threads']);
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
    
    public function getThreadPosts($board, $thread, $stripTags = true, $br2nl = true) {
        $thread = $this->getThread($board, $thread);
        $result = array();
        foreach ($thread['posts'] as $post) {
            if (!empty($post['comment'])) {
                $comment =$post['comment'];
                
                if($br2nl) {
                    $comment = preg_replace("/(\r\n|\n|\r)/", "", $comment);
                    $comment = preg_replace("=<br ? */?>=i", "\n", $comment);
                }
                
                if($stripTags) {
                    $comment = strip_tags($comment);
                }
                
                $comment = html_entity_decode($comment);
                $result[$post['num']] = $comment;
            }
        }
        return $result;
    }
    
}
