<?php

namespace Crawler;

use Traits\VerbosityTrait;
use Katzgrau\KLogger\Logger;
use Symfony\Component\Console\Output\ConsoleOutput;

class Crawler extends \PHPCrawler
{
    use VerbosityTrait;

    /**
     * $logger.
     *
     * @var Logger
     */
    public $logger;

    /**
     * $memory.
     *
     * @var Memory
     */
    private $memory;

    /**
     * Set logger for the crawler.
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Array of additional document parsers.
     *
     * @var array
     */
    private $finder = [];

    /**
     * Constructor of this crawler.
     */
    public function __construct()
    {
        parent::__construct();
        $this->memory = Memory::getInstance();
        $this->memory->setVerbosity($this->getVerbosity());
        //$this->userAgentString = 'Mozilla';
    }

    /**
     * handleDocumentInfo.
     *
     * @param \PHPCrawlerDocumentInfo $pageInfo
     */
    public function handleDocumentInfo(\PHPCrawlerDocumentInfo $pageInfo)
    {
        if ($pageInfo->content) {
            $data = $this->loadFinders($pageInfo);
            $this->memory->addData($data);
            $this->memory::setUrl($this->starting_url);
        }

        if ($this->getVerbosity()) {
            $output = new ConsoleOutput();

            $output->writeln('Page requested: <fg=white>'.$pageInfo->url.'</> (<fg=yellow>'.
                $pageInfo->http_status_code.'</>)');

            $output->writeln('Referer-page: '.$pageInfo->referer_url);

            if ($pageInfo->received == true) {
                $output->writeln('Content received: '.Memory::formatOutput('Content received:', 28, ' ').
                '<fg=blue>'.$pageInfo->bytes_received.'</> bytes');

                foreach ($data as $key => $value) {
                    $output->writeln($key.':'.Memory::formatOutput($key, 28, ' ').'<fg=green>'.count($value).'</> pc(s)');
                }
            } else {
                $output->writeln('<fg=red>Content not received</>');
            }
            $output->writeln('');
        }

        flush();
    }

    /**
     * Get array of additional document parsers.
     *
     * @return array
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Set array of additional document parsers.
     *
     * @param array $finder array of additional document parsers
     *
     * @return self
     */
    public function setFinder(array $finder)
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * Load custom finders and parse document with this finders.
     *
     * @param \PHPCrawlerDocumentInfo $pageInfo
     */
    private function loadFinders(\PHPCrawlerDocumentInfo $pageInfo)
    {
        $data = [];

        foreach ($this->getFinder() as $finder) {
            $finder = '\\Crawler\\Finders\\'.$finder;

            if (class_exists($finder)) {
                $finder::setBaseUrl($this->starting_url);                     // Set up base url for the finders
                $finder::setUserAgent($this->PageRequest->userAgentString);   // Set User Agent

                $data[$finder::getFinderName()] = $finder::find($pageInfo->content);
            }
        }

        return $data;
    }

    /**
     * Get the User Agent String.
     *
     * @return string The User Agent indentifier
     */
    public function getUserAgentString()
    {
        return $this->PageRequest->userAgentString;
    }
}
