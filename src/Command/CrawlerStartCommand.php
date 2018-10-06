<?php

namespace Console\Command;

use Crawler\Memory;
use Crawler\Crawler;
use Katzgrau\KLogger\Logger;
use Crawler\Finders\PHPCrawlerSitemapFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CrawlerStartCommand extends SymfonyCommand
{
    private $logger;

    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Configure the command.
     */
    public function configure()
    {
        $this->setName('crawler:start')
            ->setDescription('Start crawling a website given by url: parameter')
            ->setHelp('This command allows you to start crawling of a website given by url as a parameter')
            ->addArgument('url', InputArgument::REQUIRED, 'The url of website to crawling')
            ->addOption(
                'fast',
                'f',
                true,
                'Fast crawling mode based on sitemap'
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @SuppressWarnings("unused")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //echo $this->getApplication()->getName().' '."\033[0;32m".$this->getApplication()->getVersion()."\033[0m"."\n\n";
        $output->writeln($this->getApplication()->getName().' <fg=green>'.$this->getApplication()->getVersion().'</>');
        $output->writeln('');

        // Select crawling type:
        if (!$input->getOption('fast')) {
            $output->writeln('Deep crawling started.');
            $crawler = $this->deepCrawling($input, $output);

            return true;
        }

        $output->writeln('Fast crawling started.');
        $crawler = $this->fastCrawling($input, $output);

        return true;
    }

    /**
     * Start fast crawling method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function fastCrawling(InputInterface $input, OutputInterface $output)
    {
        $userAgent = 'Mozilla/5.0 (Linux; Android 7.0; '.
        'SM-G930VC Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) '.
        'Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36';
        $baseUrl = $input->getArgument('url');

        $data = [];

        // Initialize Sitemap Finder
        PHPCrawlerSitemapFinder::setBaseUrl($baseUrl);
        PHPCrawlerSitemapFinder::setUserAgent($userAgent);
        $links = PHPCrawlerSitemapFinder::find();

        // Load finders:
        $finders = [
            'PHPCrawlerEmailFinder',
            'PHPCrawlerPhoneNumberFinder',
            'PHPCrawlerExternalLinkFinder',
        ];

        if (!empty($links)) {
            // Crawling the links
            foreach ($links as $link) {
                echo "Link: $link \n";
                if ($html = PHPCrawlerSitemapFinder::getUrl($link)) {
                    echo 'Content: '.$html.$link;
                    // Run all finders:
                    foreach ($finders as $finder) {
                        $finder = '\\Crawler\\Finders\\'.$finder;

                        if (class_exists($finder)) {
                            $finder::setBaseUrl($baseUrl);                  // Set up base url for the finders
                            $finder::setUserAgent($userAgent);              // Set User Agent

                            $data[$finder::getFinderName()] = $finder::find($html);
                        }
                    }
                    // Print verbose:
                    if ($output->isVerbose()) {
                        $output->writeln('Page requested: <fg=white>'.$link.'</>');

                        foreach ($data as $key => $value) {
                            $output->writeln($key.':'.Memory::formatOutput($key, 28, ' ').'<fg=green>'.count($value).'</> pc(s)');
                        }

                        $output->writeln('');
                    }
                }
            }

            return true;
        }

        $output->writeln('<fg=red>Page can not be open</>');

        return true;
    }

    /**
     * Start deep crawling process.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function deepCrawling(InputInterface $input, OutputInterface $output)
    {
        $crawler = new Crawler();
        $crawler->setVerbosity($output->isVerbose());                                           // Set the verbosity mode
        $crawler->setURL($input->getArgument('url'));                                           // Setup the target url
        //$crawler->addContentTypeReceiveRule('#text/html#');                                   // Find in html documents
        $crawler->addContentTypeReceiveRule('#text/xml');                                       // Find in php files
        //$crawler->addContentTypeReceiveRule('#text/php');                                     // Find in php files
        $crawler->addURLFilterRule('#\.(jpg|jpeg|gif|png|ico|js|js?.*|'.
        'css|css?.*]?|eot|svg|woff|ttf)$# i');                                                  // Filtering this
        $crawler->enableCookieHandling(true);                                                   // Enable cookie handling
        $crawler->enableAggressiveLinkSearch(true);
        $crawler->setFollowMode(2);                                                             // Cralwer stays in host
        $crawler->setTrafficLimit(0);                                                           // Unlimit trafic limit
        $crawler->setUserAgentString('Mozilla/5.0 (Linux; Android 7.0; '.
        'SM-G930VC Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) '.
        'Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36');
        //$crawler->addURLFollowRule('#(html|htm|php|php3|php4|php5|xml)$# i');                 // Follow only with this endings
        $crawler->setFinder([
                'PHPCrawlerEmailFinder',
                'PHPCrawlerPhoneNumberFinder',
                'PHPCrawlerExternalLinkFinder',
                'PHPCrawlerSitemapFinder',
        ]);
        $crawler->setTrafficLimit(0);                                                           // Switch off trafic limit
        $crawler->go();                                                                         // Start crawling

         // Print report:
        if (isset($crawler->crawlerStatus)) {
            Memory::printData($crawler->getProcessReport(), $crawler->getUserAgentString());
        }
    }
}
