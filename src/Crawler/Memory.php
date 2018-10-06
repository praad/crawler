<?php

namespace Crawler;

use Traits\VerbosityTrait;
use Symfony\Component\Console\Output\ConsoleOutput;

class Memory
{
    use VerbosityTrait;

    protected static $instance = null;
    protected static $data = [];
    protected static $url;

    /**
     * $verbosity.
     *
     * @var bool
     */
    private $verbosity = true;

    /**
     * Call this method to get singleton.
     *
     * @return Memory
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private constructor so nobody else can instanceantiate it.
     */
    private function __construct()
    {
    }

    /**
     * Add data to the storage.
     *
     * @param array $data
     */
    public function addData(array $data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $values) {
                if (!empty($values)) {
                    foreach ($values as $value) {
                        self::$data[$key][] = $value;
                    }
                }
            }
        }
    }

    /**
     * Get the data from storage.
     *
     * @param array $data
     */
    public static function getData()
    {
        foreach (self::$data as $key => $values) {
            self::$data[$key] = array_unique($values);
        }

        return self::$data;
    }

    /**
     * Destructor of the memory class.
     */
    public function __destruct()
    {
        foreach (self::getData() as $key => $values) {
            $filename = $this->createFilename($key);

            // Save data:
            if (getenv('SAVEDATA')) {
                file_put_contents($filename, implode(PHP_EOL, self::$data[$key]));
            }
        }
    }

    /**
     * Create the data file name.
     *
     * @param string $key
     *
     * @return string the generated file name
     */
    private function createFilename(string $key)
    {
        $dir = getenv('APPDIR').DIRECTORY_SEPARATOR.getenv('DATADIR').DIRECTORY_SEPARATOR;

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $date = date('Y-m-d-H-i-s');
        $filenamePart = explode('-', str_replace(' ', '-', $key));

        return $dir.$filenamePart[0].'-'.$date.'.txt';
    }

    /**
     * Print data report.
     *
     * @param mixed \PHPCrawlerProcessReport
     */
    public static function printData(\PHPCrawlerProcessReport $report = null)
    {
        $output = new ConsoleOutput();

        // Decode error messages:
        $errorCode = '<fg=green>No error</>';
        $errorCode = $report->traffic_limit_reached ? '<fg=red>Traffic limit reached</>' : $errorCode;
        $errorCode = $report->file_limit_reached ? '<fg=red>File limit reached</>' : $errorCode;
        $errorCode = $report->user_abort ? '<fg=red>User abort</>' : $errorCode;

        // Print the report:
        $output->writeln('<fg=yellow>Crawler statistics:</>');
        $output->writeln('Error: '.$errorCode);
        $output->writeln('Url to crawled: <fg=white>'.self::getUrl().'</>');
        $output->writeln('Links followed: <fg=white>'.$report->links_followed.'</> pc(s)');
        $output->writeln('Total runtume: <fg=white>'.gmdate('H:i:s', $report->process_runtime).'</>');
        $output->writeln('');

        foreach (self::getData() as $key => $values) {
            $output->writeln(' - '.$key.':'.self::formatOutput($key, 22, ' ').' <fg=green>'.count($values).'</> pc(s)');
        }
    }

    /**
     * Get the value of url.
     */
    public static function getUrl()
    {
        return self::$url;
    }

    /**
     * Set the value of url.
     *
     * @param string $url
     */
    public static function setUrl(string $url)
    {
        self::$url = $url;

        return self::$url;
    }

    /**
     * Format output helper.
     *
     * @param string $str
     * @param inr    $maxLength
     * @param mixed string
     */
    public static function formatOutput(string $str, int $maxLength, string $formater = ' ')
    {
        return str_repeat($formater, $maxLength - strlen($str));
    }
}
