#!/usr/bin/php
<?php

/**
 * ****************************************************************
 * Script Purpose: Download Manga Reader Images From Specific Manga
 * Scripted By: Alex Culango
 * Email: wizardoncouch@gmail.com
 * Class Mangareader
 * ****************************************************************
 */
Class Mangareader
{
    protected $argv;
    protected $logFile = 'mangareader.log';
    protected $title;
    protected $episode = 0;
    protected $page = 1;
    protected $only = 0;

    public function __construct($argv)
    {
        $this->argv = $argv;
        if (isset($this->argv[1])) {
            $this->title = $this->argv[1];
        }
        if (isset($this->argv[2])) {
            $episode = $this->argv[2];
            if ((int)$episode > 0) {
                $this->episode = $episode;
            }
        }
        if (isset($this->argv[3])) {
            $only = $this->argv[3];
            if ($only == 'only') {
                $this->only = $this->episode;
            }
        }
        if (empty($this->title)) {
            echo 'Empty Manga Name' . PHP_EOL;

            exit(1);
        }
        if (!is_dir($this->title)) {
            mkdir($this->title);
        }
    }


    public function fire()
    {
        if ($this->validate()) {
            $this->readlog();
            $this->process();
        } else {
            var_dump('not found');
        }
    }

    protected function process()
    {
        $episode = 1;
        if ($this->episode > 0) {
            $episode = $this->episode;
        }
        $page = 1;
        if ($this->page > 0) {
            $page = $this->page;
        }
        $episodeRetry = 0;
        while (true) {
            if ($this->only > 0 && $this->only != $episode) {
                break;
            }
            $this->episode = $episode;
            $pageRetry = 0;
            while (true) {
                $this->page = $page;
                $this->updatelog();
                $url = 'http://www.mangareader.net/' . $this->title . '/' . $episode . '/' . $page;
                echo $url . PHP_EOL;
                $content = @file_get_contents($url);
                if (empty($content)) {
                    if ($pageRetry <= 3) {
                        $pageRetry++;
                        continue;
                    } else {
                        $page = 1;
                        $pageRetry = 0;
                        break;
                    }
                }
                $pageRetry = 0;
                $this->parse($content);
                $page++;
            }
            if ($pageRetry == 0) {
                $episode++;
            } else {
                if ($episodeRetry <= 3) {
                    $episodeRetry++;
                } else {
                    break;
                }
            }
            $episodeRetry = 0;
        }
    }

    protected function parse($content)
    {
        preg_match("'<div id=\"imgholder\">(.*?)</div>'", $content, $match);
        $element = $match[1];
        preg_match("'src=\"(.*?)\"'", $element, $match);
        $src = $match[1];
        $image_content = '';
        $retry = 0;
        while (empty($image_content)) {
            $image_content = @file_get_contents($src);
            if (empty($image_content)) {
                if ($retry <= 3) {
                    $retry++;
                    continue;
                } else {
                    break;
                }
            }
        }

        $extension = explode('.', $src);
        $extension = end($extension);
        if (!empty($image_content)) {
            if (@file_put_contents($this->title . '/' . $this->episode . '-' . $this->page . '.' . $extension,
                $image_content)
            ) {
                echo 'Saved to local.' . PHP_EOL;
            } else {
                echo 'Error saving' . PHP_EOL;
            }
        }

    }

    protected function validate()
    {
        $content = @file_get_contents('http://www.mangareader.net/' . $this->title);
        $content = strtolower($content);
        if (!empty($content)) {
            return true;
        }

        return false;

    }

    protected function readlog()
    {
        if (file_exists($this->logFile)) {
            $log = file_get_contents($this->logFile);
            $log = json_decode($log, true);
            if (isset($log[$this->title])) {
                $log = $log[$this->title];
                if ($this->episode == 0) {
                    $this->episode = $log['episode'];
                }
                $this->page = $log['page'];
            }
        }
    }

    protected function updatelog()
    {
        $array = [
            $this->title => [
                'episode' => $this->episode,
                'page'    => $this->page
            ]
        ];
        file_put_contents($this->logFile, json_encode($array));

    }

}

$reader = new Mangareader($argv);
$reader->fire();

