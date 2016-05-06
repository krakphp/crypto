<?php

namespace Krak\Crypto;

function _normalize_chunksize($chunksize, $blocksize) {
    return $chunksize % $blocksize
        ? $chunksize = $chunksize - ($chunksize % $blocksize)
        : $chunksize;
}

function pipe_pad_streams(Pad $pad, $blocksize, $chunksize, $src, $dst) {
    $chunksize = _normalize_chunksize($chunksize, $blocksize);

    while(!feof($src)) {
        $chunk = fread($src, $chunksize);

        if (strlen($chunk) && strlen($chunk) < $chunksize) {
            $chunk = $pad->pad($chunk, $blocksize);
        }

        fwrite($dst, $chunk);
    }
}

function pipe_strip_streams(Pad $pad, $blocksize, $chunksize, $src, $dst) {
    $chunksize = _normalize_chunksize($chunksize, $blocksize);

    while(!feof($src)) {
        $chunk = fread($src, $chunksize);

        if (strlen($chunk) && strlen($chunk) < $chunksize) {
            $chunk = $pad->strip($chunk, $blocksize);
        }

        fwrite($dst, $chunk);
    }
}

// function pipe_streams(...$streams) {
//     $size = 32;
//
//     $first = $streams[0];
//     $last = $streams[count($streams) - 1];
//     $streams = array_slice($streams, 1);
//
//     while (!feof($first)) {
//         $cur_stream = $first;
//         foreach ($streams as $stream) {
//             $content = fread($cur_stream, $size);
//             $written = fwrite($stream, $content);
//
//             /* we can't reset the last one because it doesn't write content in
//                 order to undo the offset */
//             if ($last !== $stream) {
//                 fseek($stream, $written * -1, SEEK_CUR);
//                 $cur_stream = $stream;
//             }
//         }
//     }
//
//     return $last;
// }
