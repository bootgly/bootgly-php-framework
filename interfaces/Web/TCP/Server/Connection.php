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


use Bootgly\CLI\_\ {
   Logger\Logging
};

use Bootgly\Web\TCP\Server;
use Bootgly\Web\TCP\Server\Connections;
use Synchro;

class Connection
{
   use Logging;


   public Server $Server;

   // * Config
   public ? float $timeout;
   // * Data
   public $Socket;
   // * Meta
   // @ Remote
   public array $peers;
   // @ Stats
   public int $connections;
   public int $errors;

   public Connections $Data;


   public function __construct (Server &$Server)
   {
      $this->Server = $Server;

      // * Config
      $this->timeout = 0;
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
         case '@stats':
            $worker = $this->Server->Process::$index;

            $connections = $this->connections;

            $reads = number_format($this->Data->reads, 0, '', ',');
            $writes = number_format($this->Data->writes, 0, '', ',');

            $read = round($this->Data->read / 1024 / 1024, 2);
            $written = round($this->Data->written / 1024 / 1024, 2);

            $errors = [];
            $errors[0] = $this->errors;
            $errors[1] = $this->Data->errors['read'];
            $errors[2] = $this->Data->errors['write'];

            $this->log(<<<OUTPUT
            ==================== Worker #{$worker} ====================
            Connections Accepted | @:info: {$connections} connection(s) @;
            Connection Errors    | @:error: {$errors[0]} error(s) @;
            ---------------------------------------------------
            Data Reads Count     | @:info: {$reads} time(s) @;
            Data Reads in MB     | @:info: {$read} MB @;
            Data Reads Errors    | @:error: {$errors[1]} error(s) @;
            ---------------------------------------------------
            Data Writes Count    | @:info: {$writes} time(s) @;
            Data Writes in MB    | @:info: {$written} MB @;
            Data Writes Errors   | @:error: {$errors[2]} error(s) @;
            ===================================================
            @\;
            OUTPUT);

            break;
         case '@peers':
            $worker = $this->Server->Process::$index;

            $this->log(PHP_EOL . "Worker #{$worker}:" . PHP_EOL);

            foreach (@$this->peers as $Connection => $info) {
               $this->log('Connection ID #' . $Connection . ':' . PHP_EOL, self::LOG_MAGENTA_BOLD_COLOR);

               foreach ($info as $key => $value) {
                  if ( is_array($value) ) {
                     $this->log('  ' . $key . ': ' . PHP_EOL);

                     foreach ($value as $key2 => $value2) {
                        $this->log('    ' . $key2 . ' : ' . $value2 . PHP_EOL);
                     }
                  } else {
                     $this->log('  ' . $key . ': ' . $value . PHP_EOL);
                  }
               }
            }

            if ( empty($this->peers) ) {
               $this->log('No active connection yet?' . PHP_EOL, self::LOG_WARNING_LEVEL);
            }

            break;
      }
   }

   public function accept ($Socket) // Accept connection from client / Open connection with client / Connect with client
   {
      $Connection = false;

      try {
         $Connection = @stream_socket_accept($Socket, $this->timeout, $peer);

         stream_set_blocking($Connection, false);

         #stream_set_read_buffer($Connection, 65535);
         #stream_set_write_buffer($Connection, 65535);
      } catch (\Throwable $Throwable) {}

      if ($Connection === false) {
         $this->errors++;
         $this->log('Socket connection is false!' . PHP_EOL);
         return false;
      }

      // @ On success
      // TODO call handler event on Connect here

      Server::$Event->add($Connection, Server::$Event::EVENT_READ, 'read');

      // Connection Status
      $this->peers[(int) $Connection] = [
         'peer' => $peer,
         'status' => 'opened',
         'stats' => [
            'reads' => 0,
            'writes' => 0
         ]
      ];
      $this->connections++;

      return true;
   }

   public function close ($Connection)
   {
      Server::$Event->del($Connection, Server::$Event::EVENT_READ);
      Server::$Event->del($Connection, Server::$Event::EVENT_WRITE);

      if ($Connection === null || $Connection === false) {
         $this->log('$Connection Socket is false or null on close!');
         return false;
      }

      $resource = get_resource_type($Connection);
      if ($resource !== 'stream') {
         $this->log('Resource of $Connection is not a stream on close!');
         return false;
      }

      $closed = false;
      try {
         $closed = fclose($Connection);
      } catch (\Throwable $Throwable) {
         $this->log($Throwable->getMessage());
      }

      if ($closed === false) {
         $this->log('Connection failed to close!' . PHP_EOL);
         return false;
      }

      //$this->peers[(int) $Connection]['status'] = 'closed';
      unset($this->peers[(int) $Connection]);

      return true;
   }
}