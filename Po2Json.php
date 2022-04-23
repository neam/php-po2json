<?php

namespace neam\po2json;

class Po2Json
{

    static public function parseVariable(
        $contents,
        $fuzzy = false,
        $format = 'raw',
        $domain = 'messages'
    ) {

        // Parse po contents
        $stringHandler = new \Sepia\PoParser\SourceHandler\StringSource($contents);
        $poparser = new \Sepia\PoParser\Parser($stringHandler);
        $catalog = $poparser->parse();
        return self::convertToJson($catalog, $fuzzy, $format, $domain);

    }

    static public function parseFile(
        $path,
        $fuzzy = false,
        $format = 'raw',
        $domain = 'messages'
    ) {

        // Parse po file
        $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($path);
        $poparser = new \Sepia\PoParser\Parser($fileHandler);
        $catalog = $poparser->parse();
        return self::convertToJson($catalog, $fuzzy, $format, $domain);

    }

    static public function convertToJson(
        $catalog,
        $fuzzy = false,
        $format = 'raw',
        $domain = 'messages'
    ) {

        $headers = array();
        foreach (self::parseHeaders($catalog->getHeaders()) as $key => $value) {
            $key = strtolower($key);
            $headers[$key] = $value;
        }

        // Attach headers (overwrites any empty translation keys that may have somehow gotten in)
        $result[""] = $headers;

        // Create gettext/Jed compatible JSON from parsed data
        foreach ($catalog->getEntries() as $entry) {

            // msg id json format
            if ($entry->isPlural()) {
                $msg = array_merge(
                  array($entry->getMsgIdPlural()),
                  $entry->getMsgStrPlurals()
                );
            } else {
                $msg = array(null, $entry->getMsgStr());
            }

            // json object key based on msd id and context
            $msgid = $entry->getMsgId();
            $msgctxt = $entry->getMsgCtxt();
            if ($msgctxt)
                $msgid = "$msgctxt\x04$msgid";

            // do not include fuzzy messages if not wanted
            if ($entry->isFuzzy()) {
                if (!$fuzzy) {
                    continue;
                } else {
                    // todo
                    // if (!fuzzy || options . fuzzy) {result}[translationKey] = [t . msgid_plural ? t . msgid_plural : null] . concat(t . msgstr);
                    throw new \CException("TODO");
                }
            }

            $result[$msgid] = $msg;

        }

        // Make JSON fully Jed-compatible
        if ($format === 'jed') {
            $jed = array(
                "domain" => $domain,
                "locale_data" => array(),
            );
            $jed["locale_data"][$domain] = $result;
            $jed["locale_data"][$domain][""] = array(
                "domain" => $domain,
                "plural_forms" => isset($result[""]["plural-forms"]) ? $result[""]["plural-forms"] : null,
                "lang" => $result[""]["language"],
            );
            $result = $jed;
        }

        return $result;


    }

    static public function toJSON(
        $path,
        $fuzzy = false,
        $format = 'raw',
        $domain = 'messages'
    ) {

        return json_encode(self::parseFile($path, $fuzzy, $format, $domain));

    }

    static public function parseHeaders($headers)
    {
        $result = array();
        foreach($headers as $h)
            if (preg_match('/^([^:]+): *(.*)$/', $h, $m))
                $result[strtolower($m[1])] = $m[2];
        return $result;
    }

}
