# log4php-logstash-tcp-appender
log4php appender class for the logstash tcp input plugin

# Basic configuration

```php
'log4php' => [
    'rootLogger' => [
        'level' => 'INFO',
        'appenders' => [
            'logstash_tcp'
        ]
    ],
    'appenders' => [
        'logstash_tcp' => [
            'class' => '\app\core\log\LoggerAppenderLogstashTcp',
            'params' => [
                'host' => 'tcp://127.0.0.1',
                'port' => 5555,
                'timeout' => 30
            ]
        ],
    ]
],
```
