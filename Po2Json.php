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

        // Parse po file
        $poparser = new \Sepia\PoParser();
        $translations = $poparser->read($path);

        $_headers = self::parseHeaders($poparser->headers());

        $headers = array();
        foreach ($_headers as $key => $value) {
            $key = strtolower($key);
            $headers[$key] = $value;
        }

        // Attach headers (overwrites any empty translation keys that may have somehow gotten in)
        $result[""] = $headers;

        // Create gettext/Jed compatible JSON from parsed data
        foreach ($translations as $translationKey => $t) {

            $entry = array();
            if (isset($t["msgid_plural"])) {
                $entry[0] = isset($t["msgid_plural"]) ? $t["msgid_plural"][0] : null;
                $entry[1] = $t["msgstr"][0];
                isset($t["msgstr"][1]) ? ($entry[2] = $t["msgstr"][1]) : null;
                isset($t["msgstr"][2]) ? ($entry[3] = $t["msgstr"][2]) : null;
            } else {
                $entry[0] = isset($t["msgid_plural"]) ? $t["msgid_plural"][0] : null;
                $entry[1] = implode("", $t["msgstr"]);
            }

            // msg id json format
            if ($t["msgid"][0] == '' && isset($t["msgid"][1])) {
                array_shift($t["msgid"]);
                $msgid = implode("", $t["msgid"]);
            } else {
                $msgid = implode("", $t["msgid"]);
            }

            // json object key based on msd id and context
            if (isset($t["msgctxt"][0])) {
                $key = $t["msgctxt"][0] . "\x04" . $msgid;
            } else {
                $key = $msgid;
            }

            // do not include fuzzy messages if not wanted
            if (!empty($t["fuzzy"])) {
                if (!$fuzzy) {
                    continue;
                } else {
                    // todo
                    // if (!fuzzy || options . fuzzy) {result}[translationKey] = [t . msgid_plural ? t . msgid_plural : null] . concat(t . msgstr);
                    throw new \CException("TODO");
                }
            }

            $result[$key] = $entry;

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

    static protected function parseHeaders($headers)
    {
        foreach ($headers as &$h) {
            $h = trim($h, "\"\n");
        }
        $raw = implode("", $headers);
        $raw = str_replace('\n', "\n", $raw);
        return self::parse_http_headers($raw);
    }

    /**
     * From http://stackoverflow.com/a/20933560/682317
     * @param $raw_headers
     * @return array
     */
    static protected function parse_http_headers($raw_headers)
    {

        $headers = array();
        $key = '';

        foreach (explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                } else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            } else {
                if (substr($h[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t" . trim($h[0]);
                } elseif (!$key) {
                    $headers[0] = trim($h[0]);
                }
            }
        }

        return $headers;
    }

} 