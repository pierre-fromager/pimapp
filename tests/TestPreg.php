<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use App1\Tools\Mail\Sender as mailSender;

class TestCase extends PFT
{

    const APP_PATH = '/../src/App1/';
    const APP_CONFIG_PATH = '/../src/App1/config/';
    const CSV_FILE_PATH = '/datas/csv/code-insee-code-postal.csv';

    protected $config = null;
    protected $app = null;
    protected $appPath = '';
    protected $uri;
    protected $method = 'GET';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->createConfig(\Pimvc\Config::ENV_TEST);
        $this->createApp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->config = null;
        $this->app = null;
        $this->appPath = '';
    }

    /**
     * createConfig
     *
     * create \Pimvc\Config instance for a given env
     *
     * @param string $env
     */
    private function createConfig($env)
    {
        $path = __DIR__ . self::APP_CONFIG_PATH;
        $this->config = (new \Pimvc\Config())->setPath($path)->setEnv($env)->load();
    }

    /**
     * createApp
     *
     * create \Pimvc\App instance for a given config
     *
     */
    private function createApp()
    {
        $this->appPath = __DIR__ . self::APP_PATH;
        $this->app = (new \App1\App($this->config))
                ->setPath($this->appPath)
                ->setLogger()
                ->setTranslator();
        $this->app->getRequest()->setUri($this->uri);
        $this->app->getRequest()->setMethod($this->method);
        //$this->app->getRequest()->setServer($this->mockedServer($this->uri, $this->method));
        $this->app->setMiddleware();
        $this->app->setRouter();
        $this->app->getController()->setDefault();
    }

    /**
     * testConfig
     *
     */
    public function testConfig()
    {
        $this->assertTrue($this->config instanceof \Pimvc\Config);
    }

    /**
     * multiPatternProvider
     *
     * @return array
     */
    public function multiPatternProvider()
    {
        return [
            ['gencode4d', true],
            ['validate', true],
            ['edit', true],
            ['detail', true],
            ['delete', true],
            ['ping', true],
            ['pong', true],
            ['criteriajson', true],
            ['jsoncriteria', false],
            ['criteriaj_so_n', false],
            ['toto', false],
            [false, false],
            [true, false],
            [0, false],
            [2387940, false],
            [null, false],
        ];
    }

    /**
     * testPregMultiPattern
     *
     * @param mixed $test
     * @param bool $expected
     * @covers \Pimvc\App
     * @dataProvider multiPatternProvider
     */
    public function testPregMultiPattern($test, bool $expected)
    {
        $patterns = [
            '4d$', 'ng$', 'gle$', 'json$',
            'ate$', 'i(l|t)$', 'es$', 'at$',
            'ex$', 'te$'
        ];
        $innerPattern = implode($patterns, ')|(');
        $masterPattern = '/(' . $innerPattern . ')/';
        $result = preg_match($masterPattern, $test, $matches, PREG_OFFSET_CAPTURE);
        $this->assertEquals($result, $expected);
    }
}
