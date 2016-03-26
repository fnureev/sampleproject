<?php
use Components\CURL;

class CURLTest extends \PHPUnit_Framework_TestCase
{
    use Codeception\Specify;

    protected function setUp()
    {
        $this->curl = new CURL;

        $this->mock = $this->getMockBuilder('CURL')
            ->setMethods(['getredirects'])
            ->getMock();
    }

    protected function tearDown()
    {
    }

    public function testSetNGet()
    {
        $this->curl->url = 'http://example.com';
        $this->assertEquals('http://example.com', $this->curl->url);

        $this->curl->notCurlOption = 1;
        $this->assertNotEquals(1, $this->curl->notCurlOption);
    }

    /**
     * @depends testSetNGet
     */
    public function testOptions()
    {
        $this->curl->returnHeaders = 1;
        $this->assertEquals(1, $this->curl->header);

        $this->curl->returnHeaders = 0;
        $this->assertEquals(0, $this->curl->header);

        $this->curl->resetOptions();
        $this->assertEquals('', $this->curl->url);
    }

    /**
     * @depends testSetNGet
     */
    public function testCookies()
    {
        $this->curl->setCookie('test');
        $this->assertRegExp('/test/', $this->curl->cookie);

        $this->curl->clearCookie();
        $this->assertEquals('', $this->curl->cookie);
    }

    /**
     * @depends testSetNGet
     */
    public function testPost()
    {
        $this->curl->postData = ['a' => 1, 'b' => 2];
        $this->assertEquals('a=1&b=2', $this->curl->postfields);

        $this->curl->json = 1;
        $this->curl->postData = ['a' => 1, 'b' => 2];
        $this->assertEquals('{"a":1,"b":2}', $this->curl->postfields);

        $this->curl->postData = [];
        $this->assertEmpty($this->curl->postfields);
    }

    public function redirectsProvider()
    {
        return [
            ['Location: http://example.com', 'http://example.com'],
            ['location.replace("http://example.com")', 'http://example.com'],
            ['<meta http-equiv="location" content="http://example.com">', 'http://example.com'],
            ['Refresh: 0;url=http://example.com', 'http://example.com'],
            ['document.location.href="http://example.com"', 'http://example.com'],
            ['Page w/ redirect', ''],
        ];
    }

    /**
     * @dataProvider redirectsProvider
     */
    public function testGetRedirects($text, $value)
    {
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $this->mock->expects($this->any())->method('send')->will($this->returnValue($text));
        $redirects = $this->mock->getredirects('http://domain.com');

        $this->assertEquals($redirects, ['http://domain.com', $value]);
    }
}
