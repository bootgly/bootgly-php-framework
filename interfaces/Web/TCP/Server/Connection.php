<?php
/*
 * --------------------------------------------------------------------------
 * Bootgly PHP Framework
 * Developed by Rodrigo Vieira (@rodrigoslayertech)
 * Copyright 2020-present
 * Licensed under MIT
 * --------------------------------------------------------------------------
 */

namespace Bootgly\Web\TCP\Server;


use Bootgly\OS\Process\Timer;
use Bootgly\CLI\_\ {
   Logger\Logging
};
use Bootgly\Event;
use Bootgly\Web\TCP\Server;
use Bootgly\Web\TCP\Server\Connections;


class Connection
{
   use Logging;


   public ? Server $Server;

   public Event\On $On;

   // * Config
   public ? float $timeout;
   public ? \Closure $handler;
   // * Data
   public $Socket;
   // * Meta
   // @ Remote
   public array $peers;
   // @ Stats
   public int $connections;
   public int $errors;

   public Connections $Data;


   public function __construct (? Server &$Server = null, $Socket = null)
   {
      $this->Server = $Server;

      $this->On = new Event\On;

      // * Config
      $this->timeout = 5;
      // * Data
      $this->Socket = $Socket;
      // * Meta
      // @ Remote
      $this->peers = [];      // Connections peers
      // @ Stats
      $this->connections = 0; // Connections count
      $this->errors = 0;
   }
   public function __get ($name)
   {
      // TODO use @/resources pattern folder
      switch ($name) {
         // TODO move to Info class?
         case '@stats':
            if ($this->Server === null) {
               return false;
            }

            $worker = sprintf("%02d", $this->Server->Process::$index);

            $connections = $this->connections;

            $reads = number_format($this->Data->reads, 0, '', ',');
            $writes = number_format($this->Data->writes, 0, '', ',');

            $read = round($this->Data->read / 1024 / 1024, 2);
            $written = round($this->Data->written / 1024 / 1024, 2);

            $errors = [];
            $errors[0] = $this->errors;
            $errors[1] = $this->Data->errors['read'];
            $errors[2] = $this->Data->errors['write'];

            $this->log("@\;==================== @:success: Worker #{$worker} @; ====================@\;");
            if ($connections > 0) {
               $this->log(<<<OUTPUT
               Connections Accepted | @:info: {$connections} connection(s) @;
               Connection Errors    | @:error: {$errors[0]} error(s) @;
                --------------------------------------------------
               Data Reads Count     | @:info: {$reads} time(s) @;
               Data Reads in MB     | @:info: {$read} MB @;
               Data Reads Errors    | @:error: {$errors[1]} error(s) @;
                --------------------------------------------------
               Data Writes Count    | @:info: {$writes} time(s) @;
               Data Writes in MB    | @:info: {$written} MB @;
               Data Writes Errors   | @:error: {$errors[2]} error(s) @;@\;
               OUTPUT);
            } else {
               $this->log(' -------------------- No data. -------------------- @\;', 2);
            }
            $this->log("====================================================@\\;");

            break;
         case '@stats reset':
            $this->connections = 0;
            $this->Data->reads = 0;
            $this->Data->writes = 0;
            $this->Data->read = 0;
            $this->Data->written = 0;
            $this->Data->errors['read'] = 0;
            $this->Data->errors['write'] = 0;
            break;

         case '@peers':
            if ($this->Server === null) {
               return false;
            }

            $worker = $this->Server->Process::$index;

            $this->log(PHP_EOL . "Worker #{$worker}:" . PHP_EOL);

            foreach (@$this->peers as $Connection => $info) {
               $this->log('Connection ID #' . $Connection . ':' . PHP_EOL, self::LOG_INFO_LEVEL);

               foreach ($info as $key => $value) {
                  switch ($key) {
                     case 'timers':
                        $this->log($key . ': ' . count($value) . PHP_EOL);
                        break;

                     case 'used':
                     case 'started':
                        $this->log($key . ': ' . date('Y-m-d H:i:s', $value) . PHP_EOL);
                        break;

                     default:
                        $this->log($key . ': ' . $value . PHP_EOL);
                  }
               }
            }

            if ( empty($this->peers) ) {
               $this->log('No active connection.' . PHP_EOL, self::LOG_WARNING_LEVEL);
            }

            break;
      }
   }

   // Accept connection from client / Open connection with client / Connect with client
   public function accept ($Socket)
   {
      $Connection = false;

      try {
         $Connection = @stream_socket_accept($Socket, $this->timeout, $peer);

         stream_set_blocking($Connection, false);

         #stream_set_read_buffer($Connection, 65535);
         #stream_set_write_buffer($Connection, 65535);
      } catch (\Throwable) {}

      if ($Connection === false) {
         $this->errors++;
         #$this->log('Socket connection is false!' . PHP_EOL);
         return false;
      }

      // @ On success
      $Peer = new Peer($Connection, $peer);

      // @ Set stats
      // Global
      $this->connections++;
      // Per client
      $this->peers[(int) $Connection] = $Peer;

      // TODO call handler event $this->On->accept here
      // $this->On->accept($Connection);
      $Peer->timers[] = Timer::add(interval: 5, handler: [$this, 'expire'], args: [$Peer, 5]);
      Server::$Event->add($Connection, Server::$Event::EVENT_READ, 'read');
      // TODO implement this data write by default?
      #Server::$Event->add($Connection, Server::$Event::EVENT_WRITE, 'write');

      return true;
   }

   public function expire (Peer $Peer, int $timeout = 5) 
   {
      $expired = $Peer->expire($timeout);

      if ($expired) {
         return $this->close($Peer->Socket);
      }

      return false;
   }

   public function close ($Connection)
   {
      $this->peers[(int) $Connection]->close($Connection);

      // @ On success
      // Remove active connection from @peers
      unset($this->peers[(int) $Connection]);

      return true;
   }
}