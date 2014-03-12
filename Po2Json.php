<?php

namespace neam\po2json;

class Po2Json
{

    static public function parseFile(
        $path,
        $fuzzy = false,
        $format = 'raw',
        $domain = 'messages'
    ) {

        return array();

    }

    static public function toJSON(
        $path,
        $fuzzy = false,
        $format = 'raw',
        $domain = 'messages'
    ) {

        return json_encode(self::parseFile($path, $fuzzy, $format, $domain));

    }

} 