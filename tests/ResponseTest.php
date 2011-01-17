<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart
 * @link        http://www.slimframework.com
 * @copyright   2011 Josh Lockhart
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

require_once '../Slim/Http/Request.php';
require_once '../Slim/Http/Response.php';
require_once 'PHPUnit/Extensions/OutputTestCase.php';

class ResponseTest extends PHPUnit_Extensions_OutputTestCase     {

    public function setUp() {
        $_SERVER['REDIRECT_STATUS'] = "200";
        $_SERVER['HTTP_HOST'] = "slim";
        $_SERVER['HTTP_CONNECTION'] = "keep-alive";
        $_SERVER['HTTP_CACHE_CONTROL'] = "max-age=0";
        $_SERVER['HTTP_ACCEPT'] = "application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $_SERVER['HTTP_USER_AGENT'] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.63 Safari/534.3";
        $_SERVER['HTTP_ACCEPT_ENCODING'] = "gzip,deflate,sdch";
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en-US,en;q=0.8";
        $_SERVER['HTTP_ACCEPT_CHARSET'] = "ISO-8859-1,utf-8;q=0.7,*;q=0.3";
        $_SERVER['PATH'] = "/usr/bin:/bin:/usr/sbin:/sbin";
        $_SERVER['SERVER_SIGNATURE'] = "";
        $_SERVER['SERVER_SOFTWARE'] = "Apache";
        $_SERVER['SERVER_NAME'] = "slim";
        $_SERVER['SERVER_ADDR'] = "127.0.0.1";
        $_SERVER['SERVER_PORT'] = "80";
        $_SERVER['REMOTE_ADDR'] = "127.0.0.1";
        $_SERVER['DOCUMENT_ROOT'] = rtrim(dirname(__FILE__), '/');
        $_SERVER['SERVER_ADMIN'] = "you@example.com";
        $_SERVER['SCRIPT_FILENAME'] = __FILE__;
        $_SERVER['REMOTE_PORT'] = "55426";
        $_SERVER['REDIRECT_URL'] = "/";
        $_SERVER['GATEWAY_INTERFACE'] = "CGI/1.1";
        $_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['QUERY_STRING'] = "";
        $_SERVER['REQUEST_URI'] = "/";
        $_SERVER['SCRIPT_NAME'] = basename(__FILE__);
        $_SERVER['PHP_SELF'] = '/'.basename(__FILE__);
        $_SERVER['REQUEST_TIME'] = "1285647051";
        $_SERVER['argv'] = array();
        $_SERVER['argc'] = 0;
    }


    /**
     * Test default response
     *
     * Pre-conditions:
     * A response is instantiated
     *
     * Post-conditions:
     * Response status is 200;
     * Headers array has "text/html" Content-Type
     * Cookies array is empty
     */
    public function testNewResponse() {
        $r = new Slim_Http_Response(new Slim_Http_Request());
        $this->assertEquals($r->status(), 200);
        $this->assertEquals($r->headers(), array('Content-Type' => 'text/html'));
    }

    /**
     * Test status
     *
     * Pre-conditions:
     * Case A: Status code is a valid HTTP status code
     * Case B: Status code is not a valid HTTP status code
     *
     * Post-conditions:
     * Case A: The response status code is set and returned
     * Case B: An InvalidArgumentException is thrown
     */
    public function testResponseStatus() {
        //Case A
        $r1 = new Slim_Http_Response(new Slim_Http_Request());
        $newStatus = $r1->status(201);
        $this->assertEquals($newStatus, 201);

        //Case B
        $r2 = new Slim_Http_Response(new Slim_Http_Request());
        try {
            $r2->status(700);
            $this->fail('Did not throw exception when status code invalid');
        } catch ( InvalidArgumentException $e ) {}
    }

    /**
     * Test headers
     *
     * Pre-conditions:
     * Case A: Set Content-Type to 'application/json'
     * Case B: Get non-existent header
     *
     * Post-conditions:
     * Case A: Header is set correctly
     * Case B: Returned value is NULL
     */
    public function testResponseHeaders() {
        //Case A
        $r1 = new Slim_Http_Response(new Slim_Http_Request());
        $r1->header('Content-Type', 'application/json');
        $this->assertEquals($r1->header('Content-Type'), 'application/json');
        $this->assertEquals($r1->headers(), array('Content-Type' => 'application/json'));

        //Case B
        $this->assertNull($r1->header('foo'));
    }

    /**
     * Test body and write
     *
     * Pre-conditions:
     * Case A: Response body set to "Foo bar"
     * Case B: Same response body is changed to "abc123"
     * Case C: Same response body is appended with "xyz"
     *
     * Post-conditions:
     * Case A: Response body is "Foo bar", and Content-Length = 7
     * Case B: Response body is "abc123" and Content-Length = 6
     * Case C: Response body is "abc123xyz" and Content-Length = 9
     */
    public function testBody() {
        //Case A
        $r1 = new Slim_Http_Response(new Slim_Http_Request());
        $r1->body('Foo bar');
        $this->assertEquals($r1->body(), 'Foo bar');
        $this->assertEquals($r1->header('Content-Length'), 7);

        //Case B
        $r1->body('abc123');
        $this->assertEquals($r1->body(), 'abc123');
        $this->assertEquals($r1->header('Content-Length'), 6);

        //Case C
        $r1->write('xyz');
        $this->assertEquals($r1->body(), 'abc123xyz');
        $this->assertEquals($r1->header('Content-Length'), 9);
    }

    /**
     * Test finalize
     *
     * Pre-conditions:
     * Case A: Response status is 200
     * Case B: Response status is 204
     * Case C: Response status is 304
     *
     * Post-conditions:
     * Case A: Response has body and content-length
     * Case B: Response does not have body and content-length
     * Case C: Response does not have body and content-length
     */
    public function testFinalize() {
        //Case A
        $r1 = new Slim_Http_Response(new Slim_Http_Request());
        $r1->body('body1');
        $r1->finalize();
        $this->assertEquals($r1->body(), 'body1');
        $this->assertEquals($r1->header('Content-Length'), 5);

        //Case B
        $r2 = new Slim_Http_Response(new Slim_Http_Request());
        $r2->body('body2');
        $r2->status(204);
        $r2->finalize();
        $this->assertEquals($r2->body(), '');
        $this->assertNull($r2->header('Content-Type'));

        //Case C
        $r3 = new Slim_Http_Response(new Slim_Http_Request());
        $r3->body('body3');
        $r3->status(304);
        $r3->finalize();
        $this->assertEquals($r3->body(), '');
        $this->assertNull($r3->header('Content-Type'));
    }

    /**
     * Test get messages for code
     *
     * Pre-conditions:
     * Case A: Status = 200
     * Case B: Status = 304
     * Case C: Status = 420 //Fake
     *
     * Post-conditions:
     * Case A: Message = '200 OK'
     * Case B: Message = '304 Not Modified'
     * Case C: Message = NULL
     */
    public function testGetMessageForCode() {
        //Case A
        $this->assertEquals(Slim_Http_Response::getMessageForCode(200), '200 OK');

        //Case B
        $this->assertEquals(Slim_Http_Response::getMessageForCode(304), '304 Not Modified');

        //Case C
        $this->assertNull(Slim_Http_Response::getMessageForCode(420));
    }

    /**
     * Test can have body
     *
     * Pre-conditions:
     * Case A: Status code = 100
     * Case B: Status code = 200
     * Case C: Status code = 204
     * Case D: Status code = 304
     *
     * Post-conditions:
     * Case A: false
     * Case B: true
     * Case C: false
     * Case D: false
     */
    public function testCanHaveBody() {
        $r1 = new Slim_Http_Response(new Slim_Http_Request());

        //Case A
        $r1->status(100);
        $this->assertFalse($r1->canHaveBody());

        //Case B
        $r1->status(200);
        $this->assertTrue($r1->canHaveBody());

        //Case C
        $r1->status(204);
        $this->assertFalse($r1->canHaveBody());

        //Case D
        $r1->status(304);
        $this->assertFalse($r1->canHaveBody());
    }

    /**
     * Test send response
     *
     * Pre-conditions:
     * Response instantiated with body "foo bar"
     *
     * Post-conditions:
     * Output buffer will equal "foo bar"
     */
    function testSendResponse() {
        $this->expectOutputString('foo bar');
        $r1 = new Slim_Http_Response(new Slim_Http_Request());
        $r1->body('foo bar');
        $r1->send();
    }

    /**
     * Test response body if HEAD request
     *
     * Pre-conditions:
     * HTTP method is HEAD
     *
     * Post-conditions:
     * Response body is NOT set;
     * Response headers are set;
     */
    function testResponseBodyIfHeadRequest() {
        $this->expectOutputString('');
        $req = new Slim_Http_Request();
        $req->method = Slim_Http_Request::METHOD_HEAD;
        $res = new Slim_Http_Response($req);
        $res->body('This is a test body');
        $res->send();
        $this->assertEquals('text/html', $res->header('Content-Type'));
        $this->assertEquals(19, $res->header('Content-Length'));
    }
}

?>