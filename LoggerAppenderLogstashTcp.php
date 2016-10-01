<?php
namespace app\core\log;

class LoggerAppenderLogstashTcp extends \LoggerAppender
{

    private $client;

    protected $host;

    protected $port;

    protected $timeout = 30;

    /**
     * Setup TCP connection.
     */
    function activateOptions()
    {
        $socket = $this->getHost() . ':' . $this->getPort();
        $this->client = @stream_socket_client($socket, $errno, $errorMessage, $this->getTimeout());
        if ($this->client === false) {
            $this->warn(sprintf('Failed to connect to Logstash [%d]: %s', $errno, $errorMessage));
        }
    }

    /**
     * Appends a new event to logstash.
     *
     * @param LoggerLoggingEvent $event
     */
    protected function append(\LoggerLoggingEvent $event)
    {
        $message = [
            'level' => $event->getLevel()->toString(),
            'thread' => (int) $event->getThreadName(),
            'message' => $event->getMessage(),
            'logger_name' => $event->getLoggerName()
        ];

        $locationInfo = $event->getLocationInformation();
        if ($locationInfo != null) {
            $message['filename'] = $locationInfo->getFileName();
            $message['method'] = $locationInfo->getMethodName();
            $message['line_number'] = ($locationInfo->getLineNumber() == 'NA') ? 'NA' : (int) $locationInfo->getLineNumber();
            $message['class_name'] = $locationInfo->getClassName();
        }

        $throwableInfo = $event->getThrowableInformation();
        if ($throwableInfo != null) {
            $ex = $throwableInfo->getThrowable();
            $message['exception'] = [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'stackTrace' => $ex->getTraceAsString()
            ];
        }

        $result = stream_socket_sendto($this->client, json_encode($message));
        if ($result == - 1) {
            $this->warn(sprintf('Error while writing to logstash'));
        }
    }

    /**
     * Closes the socket
     */
    public function close()
    {
        fclose($this->client);
    }

    /**
     * Sets the value of {@link $host} parameter.
     *
     * @param string $host
     */
    public function setHost($host)
    {
        if (! preg_match('/^tcp\:\/\//', $host)) {
            $host = 'tcp://' . $host;
        }
        $this->host = $host;
    }

    /**
     * Returns the value of {@link $host} parameter.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the value of {@link $port} parameter.
     *
     * @param int $port
     */
    public function setPort($port)
    {
        $this->setPositiveInteger('port', $port);
    }

    /**
     * Returns the value of {@link $port} parameter.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the value of {@link $timeout} parameter.
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->setPositiveInteger('timeout', $timeout);
    }

    /**
     * Returns the value of {@link $timeout} parameter.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
