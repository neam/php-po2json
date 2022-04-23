<?php

class Po2JsonTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $_fixturesPath;

    protected function _before()
    {
        $this->_fixturesPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_data' . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
    }

    protected function _after()
    {
    }

    // tests
    public function testParse()
    {
        $expected = json_encode(json_decode(file_get_contents($this->_fixturesPath . 'pl.json')));
        $expected = str_replace("_empty_", "", $expected);
        $result = \neam\po2json\Po2Json::toJSON($this->_fixturesPath . 'pl.po');
        $this->assertEquals($expected, $result);
    }

    public function testParseWithJedFormat()
    {
        $expected = json_encode(json_decode(file_get_contents($this->_fixturesPath . 'pl-jed.json')));
        $expected = str_replace("_empty_", "", $expected);
        $result = \neam\po2json\Po2Json::toJSON($this->_fixturesPath . 'pl.po', null, "jed");
        $this->assertEquals($expected, $result);
    }

}
