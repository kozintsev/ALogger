<?php
namespace kozintsev\ALogger;

use DateTime;
use Exception;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Finally, a light, permissions-checking logging class.
 *
 * Originally written for use with wpSearch
 *
 * Usage:
 * $log = new kozintsev\ALogger\Logger('/var/log/mylog.log', Psr\Log\LogLevel::INFO);
 * $log->info('Returned a million search results'); //Prints to the log file
 * $log->error('Oh dear.'); //Prints to the log file
 * $log->debug('x = 5'); //Prints nothing due to current severity threshhold
 *
 * @author  Oleg  Kozintsev<o.kozintsev@gmail.com>
 * @since   07.11.2017
 * @link    https://github.com/kozintsev/ALogger
 * @version 1.0.1
 */

/**
 * Class documentation
 */
class Logger extends AbstractLogger
{
    /**
     * ALogger options
     * @var array
     */
    protected $options = [
        'max_file_size' => 5120000
    ];

    /**
     * Path to the log file
     * @var string
     */
    private $logFullName;

    /**
     * Current minimum logging threshold
     * @var integer
     */
    protected $logLevelThreshold = LogLevel::DEBUG;


    /**
     * Log Levels
     * @var array
     */
    protected $logLevels = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7
    );


    /**
     * This holds the last line logged to the logger
     *  Used for unit tests
     * @var string
     */
    private $lastLine = '';

    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    private $defaultPermissions = 0777;

    /**
     * @var string
     */
    private $logDirectory;

    /**
     * @var string
     */
    private $filename;

    /**
     * Class constructor
     *
     * @param string $logFullName Full name logging file
     * @param string $logLevelThreshold The LogLevel Threshold
     * @param array $options
     */
    public function __construct($logFullName, $logLevelThreshold = LogLevel::DEBUG, array $options = [])
    {
        $this->logLevelThreshold = $logLevelThreshold;
        $this->options = array_merge($this->options, $options);

        $logDirectory = dirname($logFullName);
        $this->filename = basename(str_replace("\\", "/", $logFullName));

        $logDirectory = rtrim($logDirectory, DIRECTORY_SEPARATOR);
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, $this->defaultPermissions, true);
        }
        $this->logDirectory = $logDirectory;
        $this->logFullName = $logFullName;
    }

    /**
     * Sets the Log Level Threshold
     *
     * @param string $logLevelThreshold The log level threshold
     */
    public function setLogLevelThreshold($logLevelThreshold)
    {
        $this->logLevelThreshold = $logLevelThreshold;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->logLevels[$this->logLevelThreshold] < $this->logLevels[$level]) {
            return;
        }
        $message = $this->formatMessage($level, $message, $context);
        $this->write($message);
    }

    /**
     * Writes a line to the log without prepending a status or timestamp
     *
     * @param string $message Line to write to the log
     * @return void
     */
    protected function write($message)
    {
        if (file_exists($this->logFullName) && $this->options['max_file_size'] !== 0) {
            $max_file_size = $this->options['max_file_size'];
            $file_size = 0;
            try {
                $file_size = filesize($this->logFullName); // byte
            } catch (Exception $e) {
                echo 'Error determining the size of the file. Error text: ', $e->getMessage(), "\n";
            }
            if ($file_size > $max_file_size) {
                try {
                    $number = $this->getLastNumberFile() + 1;
                    $newFullName = $this->logFullName . '.' . $number;
                    if ( ! file_exists($newFullName))
                        rename($this->logFullName, $newFullName);
                } catch (Exception $e) {
                    echo 'Error renaming the file.. Error text: ', $e->getMessage(), "\n";
                }

            }
        }
        try {
            $fileHandle = fopen($this->logFullName, 'ab');
            $this->lastLine = trim($message);
            fwrite($fileHandle, $message);
            fclose($fileHandle);
        } catch (Exception $e) {
            echo 'An error occurred while saving the file. Error text: ', $e->getMessage(), "\n";
            $fileHandle = null;
        }
    }

    /**
     * Get the file path that the log is currently writing to
     *
     * @return string
     */
    public function getLogFullName()
    {
        return $this->logFullName;
    }

    /**
     * Get the last line logged to the log file
     *
     * @return string
     */
    public function getLastLogLine()
    {
        return $this->lastLine;
    }

    /**
     * Formats the message for logging.
     *
     * @param  string $level The Log Level of the message
     * @param  string $message The message to log
     * @return string
     */
    protected function formatMessage($level, $message, $context)
    {
        $message = "[{$this->getTimestamp()}] [{$level}] {$message}";

        if (!empty($context)) {
            $message .= PHP_EOL . $this->indent($this->contextToString($context));
        }

        return $message . PHP_EOL;

    }

    /**
     * Gets the correctly formatted Date/Time for the log entry.
     *
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     *
     * @return string
     */
    private function getTimestamp()
    {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro, $originalTime));

        return $date->format('Y-m-d G:i:s.u');
    }

    /**
     *
     * @return integer
     */
    private function getLastNumberFile() : int
    {
        $files = scandir($this->logDirectory);
        if ($files === false)
            return 0;
        $i = 0;
        $n = 0;
        $t = 0;
        foreach ($files as $item) {
            $pos = strpos($item, $this->filename);
            if (!($pos === false)) {
                $keywords = preg_split("/[.]+/", $item);
                $c = count($keywords);
                if ($c > 1) {
                    $tmp = $keywords[$c - 1];
                    if (is_numeric($tmp)) {
                        $t = (int) $tmp;
                        if ($n < $t) $n = $t;
                    }
                }
                $i++;
            }
        }
        return $n;
    }

    /**
     * Takes the given context and coverts it to a string.
     *
     * @param  array $context The Context
     * @return string
     */
    protected function contextToString($context)
    {
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace(array(
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m'
            ), array(
                '=> $1',
                'array()',
                '    '
            ), str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * Indents the given string with the given indent.
     *
     * @param  string $string The string to indent
     * @param  string $indent What to use as the indent.
     * @return string
     */
    protected function indent($string, $indent = '    ')
    {
        return $indent . str_replace("\n", "\n" . $indent, $string);
    }
}
