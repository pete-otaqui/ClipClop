<?php
require_once('PHPUnit/Autoload.php');
require_once(__DIR__.'/ClipClop.php');

class DummyPrinter implements ClipClop_Printer_Interface
{
    public $output = '';
    public function msg($message) {
        $this->output .= $message;
    }
}


class DummyQuitter implements ClipClop_Quitter_Interface
{
    public $code;
    public function quit($code) {
        $this->code = $code;
    }
}

class ClipClopTest extends PHPUnit_Framework_TestCase
{
    public function testGetRequiredValue()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'e',
            'long' => 'environment',
            'value' => TRUE,
            'help' => 'Set the environment',
            'required' => TRUE
        ));
        $usage = $clip->parseGetOpts(array('e'=>'TEST'));
        $this->assertEquals('TEST', $clip->getOption('e'));
    }
    public function testGetOptionalValue()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'e',
            'long' => 'environment',
            'value' => TRUE,
            'help' => 'Set the environment'
        ));
        $usage = $clip->parseGetOpts(array('e'=>'TEST'));
        $this->assertEquals('TEST', $clip->getOption('e'));
    }
    public function testGetBooleanTrue()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'long' => 'verbose',
            'help' => 'Verbosity',
        ));
        $clip->parseGetOpts(array('v'=>FALSE));
        $this->assertEquals(TRUE, $clip->getOption('v'));
    }
    public function testGetBooleanFalse()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'long' => 'verbose',
            'help' => 'Verbosity',
        ));
        $clip->parseGetOpts(array());
        $this->assertEquals(NULL, $clip->getOption('v'));
    }
    public function testLastSingularValue()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'value' => TRUE,
        ));
        $clip->parseGetOpts(array(
            'v' => array('one', 'two'),
        ));
        $this->assertEquals('two', $clip->getOption('v'));
    }
    public function testMultipleFlags()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'multiple' => TRUE,
        ));
        $clip->parseGetOpts(array(
            'v' => array(FALSE, FALSE),
        ));
        $this->assertEquals(array(TRUE, TRUE), $clip->getOption('v'));
    }
    public function testMultpleValues()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'value' => TRUE,
            'multiple' => TRUE,
        ));
        $clip->parseGetOpts(array(
            'v' => array('one', 'two'),
        ));
        $this->assertEquals(array('one', 'two'), $clip->getOption('v'));
    }
    public function testMultipleValuesAlwaysReturnArrays()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'value' => TRUE,
            'multiple' => TRUE,
        ));
        $clip->parseGetOpts(array(
            'v' => 'one',
        ));
        $this->assertEquals(array('one'), $clip->getOption('v'));
    }
    public function testValueValidation()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'value' => TRUE,
            'validate' => '|\d|',
        ));

        $printer = new DummyPrinter();
        $quitter = new DummyQuitter();
        $clip->setPrinter($printer);
        $clip->setQuitter($quitter);
        $clip->parseGetOpts(array(
            'v' => 'a',
        ));
        $this->assertEquals("/usr/bin/phpunit Error: a does not match |\d|

/usr/bin/phpunit

Optional:
-v=value  
", $printer->output);
    }
    public function testIntegerType()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'e',
            'value' => TRUE,
            'help' => 'Set the environment',
            'type' => 'integer',
        ));
        $clip->parseGetOpts(array('e'=>'1'));
        $this->assertEquals(1, $clip->getOption('e'));
    }
    public function testNumberType()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'e',
            'value' => TRUE,
            'help' => 'Set the environment',
            'type' => 'number',
        ));
        $clip->parseGetOpts(array('e'=>'1.1'));
        $this->assertEquals(1.1, $clip->getOption('e'));
    }
    public function testUrlType()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'u',
            'value' => TRUE,
            'help' => 'Set the url',
            'type' => 'url',
        ));
        $url = 'https://www.example.com:8080/foo/bar.html';
        $parsed_url = parse_url($url);
        $clip->parseGetOpts(array('u'=>$url));
        $this->assertEquals($parsed_url, $clip->getOption('u'));
    }
    public function testJsonType()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'j',
            'value' => TRUE,
            'help' => 'Some JSON',
            'type' => 'json',
        ));
        $json = '{"obj":{"k":"v"}, "arr":[0, 1]}';
        $parsed_json = json_decode($json);
        $clip->parseGetOpts(array('j'=>$json));
        $this->assertEquals($parsed_json, $clip->getOption('j'));
    }

    public function testOnlyShortName()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'v',
            'help' => 'Verbosity',
        ));
        $clip->parseGetOpts(array());
        $this->assertEquals(NULL, $clip->getOption('v'));
    }
    public function testOnlyLongName()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'long' => 'verbose',
            'help' => 'Verbosity',
        ));
        $clip->parseGetOpts(array());
        $this->assertEquals(NULL, $clip->getOption('verbose'));
    }
    public function testCanAccessByShortName()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'e',
            'long' => 'environment',
            'value' => TRUE,
            'help' => 'Set the environment'
        ));
        $clip->parseGetOpts(array('e'=>'TEST'));
        $this->assertEquals('TEST', $clip->getOption('environment'));
    }
    public function testCanAccessByLongName()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'e',
            'long' => 'environment',
            'value' => TRUE,
            'help' => 'Set the environment'
        ));
        $clip->parseGetOpts(array('environment'=>'TEST'));
        $this->assertEquals('TEST', $clip->getOption('e'));
    }
    public function testGetAllOptionNames()
    {
        $clip = new ClipClop();
        $clip->addOption(array(
            'short' => 'e',
            'help' => 'Env'
        ));
        $clip->addOption(array(
            'long' => 'verbose',
            'help' => 'Verbose'
        ));
        $clip->addOption(array(
            'short' => 'x',
            'long' => 'x-ray',
            'help' => 'X-Ray',
        ));
        $clip->parseGetOpts(array('e'=>FALSE,'verbose'=>FALSE,'x'=>FALSE));
        $options = $clip->getOptions();
        $this->assertEquals(TRUE, $options['e']);
        $this->assertEquals(TRUE, $options['verbose']);
        $this->assertEquals(TRUE, $options['x']);
        $this->assertEquals(TRUE, $options['x-ray']);
    }
    public function testCommandHelp()
    {
        $clip = new ClipClop();
        $clip->setWidth(80);
        $clip->addOption(array(
            'short' => 'e',
            'long' => 'environment',
            'value' => TRUE,
            'help' => 'Set the environment'
        ));
        $clip->setCommandHelp('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sagittis nunc ac ante tristique vitae egestas ligula pharetra. Sed bibendum augue non libero venenatis sed euismod velit consequat. Mauris vulputate ornare nisl sit amet vulputate. Aenean facilisis nibh vitae justo rhoncus rutrum. Donec congue interdum dui ut congue. Nam fermentum lacus quis nibh aliquet interdum. Nullam non condimentum est. Nulla condimentum libero sed libero aliquam vel ultrices mauris varius. Mauris eget rutrum nunc. Ut at nunc nibh. Praesent et arcu blandit dui facilisis pharetra. Phasellus a porttitor neque');
        $usage = $clip->getUsage();
        $this->assertEquals('/usr/bin/phpunit

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sagittis nunc 
ac ante tristique vitae egestas ligula pharetra. Sed bibendum augue non libero v
enenatis sed euismod velit consequat. Mauris vulputate ornare nisl sit amet vulp
utate. Aenean facilisis nibh vitae justo rhoncus rutrum. Donec congue interdum d
ui ut congue. Nam fermentum lacus quis nibh aliquet interdum. Nullam non condime
ntum est. Nulla condimentum libero sed libero aliquam vel ultrices mauris varius
. Mauris eget rutrum nunc. Ut at nunc nibh. Praesent et arcu blandit dui facilis
is pharetra. Phasellus a porttitor neque

Optional:
-e=value, --environment=value  Set the environment
' , $usage);
    }
    public function testFormatsSimpleUsage()
    {
        $clip = new ClipClop();
        $clip->setWidth(80);
        $clip->addOption(array(
            'short' => 'e',
            'long' => 'environment',
            'value' => TRUE,
            'help' => 'Set the environment'
        ));
        $usage = $clip->getUsage();
        $this->assertEquals('/usr/bin/phpunit

Optional:
-e=value, --environment=value  Set the environment
' , $usage);
    }
    public function testFormatsLongDescriptions()
    {
        $clip = new ClipClop();
        $clip->setWidth(80);
        $clip->addOption(array(
            'short' => 'e',
            'long' => 'environment',
            'value' => TRUE,
            'help' => 'Set the environment'
        ));
        $clip->addOption(array(
            'short' => 'x',
            'long' => 'xray',
            'value' => TRUE,
            'help' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sagittis nunc ac ante tristique vitae egestas ligula pharetra. Sed bibendum augue non libero venenatis sed euismod velit consequat. Mauris vulputate ornare nisl sit amet vulputate. Aenean facilisis nibh vitae justo rhoncus rutrum. Donec congue interdum dui ut congue. Nam fermentum lacus quis nibh aliquet interdum. Nullam non condimentum est. Nulla condimentum libero sed libero aliquam vel ultrices mauris varius. Mauris eget rutrum nunc. Ut at nunc nibh. Praesent et arcu blandit dui facilisis pharetra. Phasellus a porttitor neque'
        ));
        $clip->addOption(array(
            'short' => 'c',
            'long' => 'commit',
            'value' => TRUE,
            'help' => 'Commit',
            'required' => TRUE
        ));
        $usage = $clip->getUsage();
        $this->assertEquals('/usr/bin/phpunit

Required:
-c=value, --commit=value       Commit

Optional:
-e=value, --environment=value  Set the environment
-x=value, --xray=value         Lorem ipsum dolor sit amet, consectetur adipiscing
                                elit. Maecenas sagittis nunc ac ante tristique vi
                               tae egestas ligula pharetra. Sed bibendum augue no
                               n libero venenatis sed euismod velit consequat. Ma
                               uris vulputate ornare nisl sit amet vulputate. Aen
                               ean facilisis nibh vitae justo rhoncus rutrum. Don
                               ec congue interdum dui ut congue. Nam fermentum la
                               cus quis nibh aliquet interdum. Nullam non condime
                               ntum est. Nulla condimentum libero sed libero aliq
                               uam vel ultrices mauris varius. Mauris eget rutrum
                                nunc. Ut at nunc nibh. Praesent et arcu blandit d
                               ui facilisis pharetra. Phasellus a porttitor neque
' , $usage);
    }
}