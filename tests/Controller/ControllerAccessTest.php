<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Tests\FixtureAwareTestCase;
use App\Tests\Fixtures\AgentFixture;
use App\Tests\Fixtures\AgentStatFixture;
use App\Tests\Fixtures\ChallengeFixture;
use App\Tests\Fixtures\CommentFixture;
use App\Tests\Fixtures\EventFixture;
use App\Tests\Fixtures\HelpFixture;
use App\Tests\Fixtures\IngressEventFixture;
use App\Tests\Fixtures\MapGroupFixture;

class ControllerAccessTest extends FixtureAwareTestCase
{
    private $routeLoader;

    private $exceptions
        = [
            'default'               => [
                'expected' => 200,
            ],
            'app_login'             => [
                'expected' => 200,
            ],
            'agent_add_comment'     => [
                'method' => 'POST',
            ],
            'agent_lookup'          => [
                'method' => 'POST',
            ],
            'comment_delete_inline' => [
                'method' => 'DELETE',
            ],
        ];

    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();
        parent::setUp();
        $kernel = static::bootKernel();

        $this->addFixture(new AgentFixture());
        $this->addFixture(new AgentStatFixture());
        $this->addFixture(new CommentFixture());
        $this->addFixture(new HelpFixture());
        $this->addFixture(new EventFixture());
        $this->addFixture(new IngressEventFixture());
        $this->addFixture(new MapGroupFixture());
        $this->addFixture(new ChallengeFixture());
        $this->executeFixtures();

        $this->routeLoader = $kernel->getContainer()->get('routing.loader');
    }

    private function loadRoutes($controllerName)
    {
        $routerClass = 'App\Controller\\'.$controllerName;

        return $this->routeLoader->load($routerClass);
    }

    public function testShowPage()
    {
        $path = __DIR__.'/../../src/Controller';

        foreach (new \DirectoryIterator($path) as $item) {
            if (
                $item->isDot()
                || in_array(
                    $item->getBasename(), ['.gitignore', 'GoogleController.php']
                )
            ) {
                continue;
            }

            $controllerName = basename($item->getBasename(), '.php');

            $routes = $this->loadRoutes($controllerName)->all();

            foreach ($routes as $routeName => $route) {
                $method = 'GET';
                $defaultId = 1;
                $defaultExpected = 302;

                if (array_key_exists($routeName, $this->exceptions)) {
                    if (array_key_exists('method', $this->exceptions[$routeName])) {
                        $method = $this->exceptions[$routeName]['method'];
                    }
                    if (array_key_exists('expected', $this->exceptions[$routeName])) {
                        $defaultExpected = $this->exceptions[$routeName]['expected'];
                    }
                    if (array_key_exists('params', $this->exceptions[$routeName])) {
                        $params = $this->exceptions[$routeName]['params'];
                        if (array_key_exists('id', $params)) {
                            $defaultId = $params['id'];
                        }
                    }
                }

                $path = $route->getPath();
                $path = str_replace('{id}', $defaultId, $path);
                // echo 'Testing: '.$path.PHP_EOL;
                $this->client->request($method, $path);
                $this->assertEquals(
                    $defaultExpected,
                    $this->client->getResponse()->getStatusCode(),
                    sprintf('failed: %s (%s)', $routeName, $path)
                );
            }
        }
    }
}
