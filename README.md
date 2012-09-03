ClipClop
========

A PHP option parser based on getopt().

ClipClop allows you to easily create command line tools with options.  ClipClop automatically generates nicely formatted usage instructions, and also gives a convenient API for accessing parameters and values.

Usage Example
-------
````php
#!/usr/bin/env php
<?php
$clipclop = new ClipClop();

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

$clipclop->getOption('e'); // returns the value set for 'e' or 'environment'

$clipclop->getOption('environment'); // returns the value set for 'environment' or 'e'

$clipclop->getOption('v'); // returns TRUE if set, NULL otherwise

$clipclop->getOptions(); // returns array('environment'=>'test', 'v'=>TRUE);

$clipclop->setCommandName('foome'); // overrides default of $argv[0]
$clipclop->setCommandHelp('foome does a foo on me');

$clipclop->usage(); // manually print out the usage text

/*
outputs something like:

foome

does a foo on me

Required:
-v, --verbose                  More verbose output

Optional:
-e=value, --environment=value  Set the environment
*/
````