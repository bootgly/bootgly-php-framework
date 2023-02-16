<?php
use Bootgly\Bootgly;
use Bootgly\Debugger;
// SAPI
use Bootgly\Web\HTTP\Server\Request;
use Bootgly\Web\HTTP\Server\Response;
use Bootgly\Web\HTTP\Server\Router;
// CAPI?
#use Bootgly\Web\HTTP\Client\Request;
#use Bootgly\Web\HTTP\Client\Response;
// TODO ?

return [
   // ! Server
   'response.length' => 3101873,
   // API
   'sapi' => function (Request $Request, Response $Response, Router $Router) : Response {
      Bootgly::$Project->vendor = '@bootgly/';
      Bootgly::$Project->container = 'web/';
      Bootgly::$Project->package = 'examples/';
      Bootgly::$Project->version = 'app/';

      Bootgly::$Project->setPath();

      return $Response('statics/screenshot.gif')->upload(close: false);
   },
   // ! Client
   // API
   'capi' => function () {
      // return $Request->get('//header/changed/1');
      return "GET /test/download/large_file/1 HTTP/1.0\r\n\r\n";
   },

   'assert' => function ($response) : bool {
      // ! Asserts
      // @ Assert length of response
      $expected = 3101873;

      if (strlen($response) !== $expected) {
         Debugger::$labels = ['HTTP Response length:', 'Expected:'];
         debug(strlen($response), $expected);
         return false;
      }

      return true;
   },

   'except' => function () : string {
      return 'Response length of uploaded file by server is correct?';
   }
];