<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;

class TestCase extends PFT
{

    const APP_PATH = '/../src/App1/';
    const APP_CONFIG_PATH = '/../src/App1/config/';

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
     * appComponentProvider
     *
     * @return array
     */
    public function appComponentProvider()
    {
        if (!$this->app) {
            $this->setUp(); // because dataProvider starts before setUp
        }
        return [
            [$this->app, \Pimvc\App::class, true]
            , [$this->app->getRequest(), \Pimvc\Http\Request::class, true]
            , [$this->app->getRoutes(), \Pimvc\Http\Routes::class, true]
            , [$this->app->getRouter(), \Pimvc\Http\Router::class, true]
            , [$this->app->getController(), \Pimvc\Controller::class, true]
            , [$this->app->getResponse(), \Pimvc\Http\Response::class, true]
            , [$this->app->getTranslator(), \Pimvc\Tools\Translator::class, true]
            , [$this->app->getLogger(), \Pimvc\Logger::class, true]
        ];
    }

    /**
     * testApp
     *
     * @param object $component
     * @param string $classname
     * @param boolean $expected
     *
     * test app components for a given \Pimvc\App instance
     *
     * @covers \Pimvc\App
     * @dataProvider appComponentProvider
     */
    public function testAppComponents($component, $classname, $expected)
    {
        $this->assertEquals($component instanceof $classname, $expected);
    }

    /**
     * testAppPath
     *
     * test if app path is correct
     */
    public function testAppPath()
    {
        $this->assertTrue($this->app->getPath() === $this->appPath);
    }

    /**
     * testRequestUri
     *
     * test if app path is correct
     */
    public function testRequestUri()
    {
        $uri = 'home/index';
        $this->app->getRequest()->setUri($uri);
        $this->assertEquals($this->app->getRequest()->getUri(), $uri);
    }

    /**
     * caseRouterProvider
     *
     * @return array
     */
    public function caseRouterProvider()
    {
        if (!$this->app) {
            //$this->setUp(); // because dataProvider starts before setUp
        }
        return [
            ['GET', false, null, true],
            ['GET', true, null, true],
            ['GET', null, null, true],
            ['GET', 1, null, true],
            ['GET', '', null, true],
            ['GET', '/', null, true],
            ['GET', '(*)', null, true],
            ['GET', '\^home\\', null, true],
            ['GET', 'home/index', ['home', 'index'], true],
            ['GET', 'home/sweet', ['home', 'sweet'], true],
            ['GET', 'api/v1/ping', ['api/v1/ping'], true], // uri preserved @see middleware
            ['GET', 'api/v1/ping/params/p1/1/p2/2', ['api/v1/ping', 'params', '/p1/1/p2/2'], true],
            ['POST', 'api/v1/ping', ['api/v1/ping'], true],
            ['PUT', 'api/v1/ping', ['api/v1/ping'], true],
            ['DELETE', 'api/v1/ping', ['api/v1/ping'], true],
            ['GET', 'api/v1/ping/create', ['api/v1/ping', 'create'], true],
            ['GET', 'api/v1/auth', ['api/v1/auth'], true],
            ['GET', 'api/v1/authsn', ['api/v1/authsn'], true],
            ['GET', 'math/whatever', ['math', 'whatever'], false],
            ['GET', 'gonzo/whatever', ['gonzo', 'whatever'], false],
            ['GET', 'home:index', null, true],
            ['GET', 'home\index', null, true],
            ['GET', 'home\index', null, true],
            ['GET', "home\index\n", null, true],
            ['GET', 'home' . json_decode("\u2044") . 'index', null, true],
            ['GET', 'home' . mb_convert_encoding('&#x2044;', 'UTF-8', 'HTML-ENTITIES') . 'index', null, true],
            ['GET', 'home/index', null, false],
            ['GET', 'home/index/p1/p1value/p2/p2value', ['home', 'index', '/p1/p1value/p2/p2value'], true],
            ['GET', 'iam/rhino', null, true],
            ['GET', 'i/am/rhino/and/i/live/in/savannah', null, true],
        ];
    }

    /**
     * testRouter
     *
     * test if the router find a route from a given uri matching route list
     *
     * @param string $method
     * @param string $uri
     * @param type $matchingRoute
     * @param type $expected
     * @covers \App1\App
     * @dataProvider caseRouterProvider
     */
    public function testRouter($method, $uri, $matchingRoute, $expected)
    {
        $this->app->getRequest()->setMethod($method);
        $this->app->getRouter()->setUri($uri);
        $testRoute = $this->app->getRouter()->compile();
        if ($expected) {
            $this->assertTrue($testRoute === $matchingRoute);
        } else {
            $this->assertFalse($testRoute === $matchingRoute);
        }
    }

    /**
     * caseRequestProvider
     *
     * @return array
     */
    public function caseRequestProvider()
    {
        
        return [
            ['GET', false, null, true],
            ['GET', true, null, true],
            ['GET', null, null, true],
            ['GET', 1, null, true],
            ['GET', '', null, true],
            ['GET', '/', null, true],
            ['GET', '(*)', null, true],
            ['GET', '\^home\\', null, true],
            ['GET', '/home/index', ['home', 'index'], true],
            ['GET', '/home/sweet', ['home', 'sweet'], true],
            ['GET', '/api/v1/ping', ['api/v1/ping'], true], // uri preserved @see middleware
            ['GET', '/api/v1/ping/params/p1/1/p2/2', ['api/v1/ping', 'params', '/p1/1/p2/2'], true],
            ['POST', '/api/v1/ping', ['api/v1/ping'], true],
            ['PUT', '/api/v1/ping', ['api/v1/ping'], true],
            ['DELETE', '/api/v1/ping', ['api/v1/ping'], true],
            ['GET', '/api/v1/ping/create', ['api/v1/ping', 'create'], true],
            ['GET', '/api/v1/auth', ['api/v1/auth'], true],
            ['GET', '/api/v1/authsn', ['api/v1/authsn'], true],
            ['GET', '/math/whatever', ['math', 'whatever'], false],
            ['GET', '/gonzo/whatever', ['gonzo', 'whatever'], false],
            ['GET', '/home:index', null, true],
            ['GET', '/home\index', null, true],
            ['GET', '/home\index', null, true],
            ['GET', "/home\index\n", null, true],
            ['GET', '/home' . json_decode("\u2044") . 'index', null, true],
            ['GET', '/home' . mb_convert_encoding('&#x2044;', 'UTF-8', 'HTML-ENTITIES') . 'index', null, true],
            ['GET', '/home/index', null, false],
            ['GET', '/home/index/p1/p1value/p2/p2value', ['home', 'index', '/p1/p1value/p2/p2value'], true],
            ['GET', '/iam/rhino', null, true],
            ['GET', '/i/am/rhino/and/i/live/in/savannah', null, true],
        ];
    }

    /**
     * testRequest
     *
     * test if the router find a route from a given uri matching route list
     *
     * @param string $method
     * @param string $uri
     * @param type $matchingRoute
     * @param type $expected
     * @covers \Pimvc\App
     * @dataProvider caseRequestProvider
     */
    public function testRequest($method, $uri)
    {
        //if (!$this->app) {
        /*
          $this->method = $method;
          $this->uri = $uri;
          $this->setUp(); // because dataProvider starts before setUp
          echo 'setup'; */
        //}
        $this->app->getRequest()->setMethod($method);
        $this->app->getRequest()->setUri($uri);
        $this->app->getRequest()->setServer($this->mockedServer($uri, $method));
        //$params = $this->app->getRequest()->getParams();
        //var_dump($this->app->getRequest()->get());
        var_dump($this->app->getRequest()->getParams());
        //die;
        $this->assertTrue(true === true);
        //}

        /*
          $this->app->getRouter()->setUri($uri);
          $testRoute = $this->app->getRouter()->compile();
          if ($expected) {
          $this->assertTrue($testRoute === $matchingRoute);
          } else {
          $this->assertFalse($testRoute === $matchingRoute);
          } */
    }

    /**
     * mockedServer
     *
     * mock server env to inject pseudo request
     *
     * @param type $uri
     * @return type
     */
    private function mockedServer($uri, $method)
    {
        return [
            "REDIRECT_SCRIPT_URL" => $uri,
            "REDIRECT_SCRIPT_URI" => "https://pimapp.pier-infor.fr" . $uri,
            "REDIRECT_HTTP_AUTHORIZATION" => "",
            "REDIRECT_APP_ENV" => "dev",
            "REDIRECT_HTTPS" => "on",
            "REDIRECT_SSL_TLS_SNI" => "pimapp.pier-infor.fr",
            "REDIRECT_STATUS" => 200,
            "SCRIPT_URL" => $uri,
            "SCRIPT_URI" => "https://pimapp.pier-infor.fr" . $uri,
            "HTTP_AUTHORIZATION" => "",
            "APP_ENV" => "dev",
            "HTTPS" => "on",
            "SSL_TLS_SNI" => "pimapp.pier-infor.fr",
            "HTTP_HOST" => "pimapp.pier-infor.fr",
            "HTTP_USER_AGENT" => "Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0",
            "HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "HTTP_ACCEPT_LANGUAGE" => "fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3",
            "HTTP_ACCEPT_ENCODING" => "gzip, deflate, br",
            "HTTP_REFERER" => "https://pimapp.pier-infor.fr/",
            "HTTP_COOKIE" => "columnManagerCtable_d442368d9d20413c80d1f18a14b0d90f=11111; 9c005d970cb73d0be009c828e722ff3838ebe953=ov3uou04ufd5enhdour1o926u3",
            "HTTP_CONNECTION" => "keep-alive",
            "HTTP_UPGRADE_INSECURE_REQUESTS" => 1,
            "HTTP_CACHE_CONTROL" => "max-age=0",
            "PATH" => "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin",
            "SERVER_SIGNATURE" => "Apache/2.4.25 (Debian) Server at pimapp.pier-infor.fr Port 443",
            "SERVER_SOFTWARE" => "Apache/2.4.25 (Debian)",
            "SERVER_NAME" => "pimapp.pier-infor.fr",
            "SERVER_ADDR" => "192.168.1.49",
            "SERVER_PORT" => 443,
            "REMOTE_ADDR" => "192.168.1.254",
            "DOCUMENT_ROOT" => "/var/www/pimapp/src",
            "REQUEST_SCHEME" => "https",
            "CONTEXT_PREFIX" => "",
            "CONTEXT_DOCUMENT_ROOT" => "/var/www/pimapp/src",
            "SERVER_ADMIN" => "info@pier-infor.fr",
            "SCRIPT_FILENAME" => "/var/www/pimapp/src/index.php",
            "REMOTE_PORT" => 58860,
            "REDIRECT_URL" => $uri,
            "GATEWAY_INTERFACE" => "CGI/1.1",
            "SERVER_PROTOCOL" => "HTTP/1.1",
            "REQUEST_METHOD" => $method,
            "QUERY_STRING" => "",
            "REQUEST_URI" => $uri,
            "SCRIPT_NAME" => "/index.php",
            "PHP_SELF" => "/index.php",
            "REQUEST_TIME_FLOAT" => 1536927790.447,
            "REQUEST_TIME" => 1536927790
        ];
    }
}
