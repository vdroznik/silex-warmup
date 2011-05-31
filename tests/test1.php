<?php
require_once '../silex.phar';

use Silex\WebTestCase;

class HelloWorldTest extends WebTestCase
{
  public function createApplication() {
    return require __DIR__.'app/helloworld.php';
  }

  public function testInitialPage()
  {
    $client = $this->createClient();
    $crawler = $client->request('GET', '/hello/test');

    $this->assertTrue($client->getResponse()->isOk());
    $this->assertEquals('Hello test', $crawler->text());
  }   
}
