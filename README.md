ClipClop
========

A PHP option parser based on getopt()

Usage Example
-------
````php
$clipclop = new \ClipClop();

$clipclop->addOption(array(
    'short' => 'e', // shortname, i.e. "-e"
    'long' => 'environment', // longname, i.e. "--environment"
    'value' => FALSE, // value required?  skip or set NULL for no value
    'help' => 'Set the environment', // help text
    'required' => TRUE, // This 'option' must be set to something
));

$clipclop->addOption(array(
    'short' => 'v', // shortname
    'long' => 'verbose', // longname
    'help' => 'More verbose output',
));


$clipclop->run();

$clipclop->getOption('e'); // returns the value set for 'e' or 'environment'

$clipclop->getOption('environment'); // returns the value set for 'environment' or 'e'

$clipclop->getOption('v'); // returns TRUE if set, NULL otherwise

$clipclop->getOptions(); // returns array('environment'=>'test', 'v'=>TRUE);

$clipclop->setCommandName('foome'); // overrides default of $argv[0]

$clipclop->usage();

/*
outputs something like:

foome

Required:
-v, --verbose                  More verbose output

Optional:
-e=value, --environment=value  Set the environment
*/
````