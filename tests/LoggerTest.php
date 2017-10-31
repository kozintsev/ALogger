<?php
include '../vendor/autoload.php';

use kozintsev\ALogger;
use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private $logPath;

    private $logger;


    public function testImplementsPsr3LoggerInterface()
    {
        $this->logPath = __DIR__.'/logs/test.log';
        $this->logger = new ALogger\Logger($this->logPath, LogLevel::DEBUG);
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $this->logger);
    }

    public function testAcceptsExtension()
    {
        $this->logPath = __DIR__.'/logs/test.log';
        $this->logger = new ALogger\Logger($this->logPath, LogLevel::DEBUG);

        $this->assertStringEndsWith('.log', $this->logger->getLogFilePath());
    }


    public function testWritesBasicLogs()
    {
        $this->logPath = __DIR__.'/logs/test.log';
        $this->logger = new ALogger\Logger($this->logPath, LogLevel::DEBUG);

        $this->logger->log(LogLevel::DEBUG, 'This is a test');

        $this->assertTrue(file_exists($this->logger->getLogFilePath()));

        $this->assertLastLineEquals($this->logger);
    }

    public function assertLastLineEquals(ALogger\Logger $logr)
    {
        $this->assertEquals($logr->getLastLogLine(), $this->getLastLine($logr->getLogFilePath()));
    }

    private function getLastLine($filename)
    {
        $size = filesize($filename);
        $fp = fopen($filename, 'r');
        $pos = -2; // start from second to last char
        $t = ' ';

        while ($t != "\n") {
            fseek($fp, $pos, SEEK_END);
            $t = fgetc($fp);
            $pos = $pos - 1;
            if ($size + $pos < -1) {
                rewind($fp);
                break;
            }
        }

        $t = fgets($fp);
        fclose($fp);

        return trim($t);
    }

}
