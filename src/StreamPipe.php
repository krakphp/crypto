<?php

namespace Krak\Crypto;

/** Object Oriented inteface into piping streams.

    This class itself is a streamable object and is an immutable value object.

    ```php
    $stream = new StreamPipe(str_stream('abcd', 1));
    $stream = $stream->pipe(map_stream('strtoupper'))
        ->pipe(filter_stream(function($char) { return $char != 'b'; }))
        ->pipe(base64_encode_stream(3));

    // $contents = str_to_stream($stream);
    // or
    $dst = fopen('php://stdout', 'w');
    $stream->pipe(write_stream($dst));
    fclose($dst);
    ```
*/
class StreamPipe implements \IteratorAggregate
{
    private $stream;

    public function __construct($stream) {
        $this->stream = $stream;
    }

    /** Creates a new StreamPipe after wrapping the internal stream
        @param callable $pipe any function that returns streamable chunks
        @return StreamPipe a new wrapped stream pipe instance
    */
    public function pipe($pipe) {
        return new self($pipe($this->stream));
    }

    /** @inheritDoc */
    public function getIterator() {
        return $this->stream;
    }
}
