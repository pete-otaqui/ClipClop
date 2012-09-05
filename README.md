ClipClop
========

A PHP option parser based on [getopt()](http://php.net/manual/en/function.getopt.php).

ClipClop allows you to easily create command line tools with options.  ClipClop automatically generates nicely formatted usage instructions, and also gives a convenient API for accessing parameters and values.

ClipClop handles required and optional parameters, and values for them.  So a given option such as "--verbose" can be required or optional in itself, and it can have no parameter value or an optional one, or a required one.

ClipClop manages multiple values, can enforce single values, can validate against regular expressions and can parse out certain types for you: integers, numbers, json and urls.

Quick Example
-------------

Create a script called "environment_test", with the following code
````php
#!/usr/bin/env php
<?php

// do this unless you have setup an Autoloader
require_once('/path/to/ClipClop.php');

$clipclop = new ClipClop();

$clipclop->addOption(array(
    'short' => 'e', // shortname, i.e. "-e"
    'long' => 'environment', // longname, i.e. "--environment"
    'value' => TRUE, // A value must be given such as "--environment=TEST"
    'help' => 'Set the environment', // help text for the 'usage' text
    'required' => TRUE, // Environment must be provided
));

// as soon as we ask for an option, ClipClop will parse CLI arguments with getopt()

$environment = $clipclop->getOption('e'); // returns the value set for 'e' OR 'environment'

print "You ran this script with environment: $environment";
?>
````
Make the script executable:
````bash
$ chmod +x env
````
Now you can run this from the command line as follows:
````bash
$ ./environment_test --environment=TEST
````
You should see output like: You ran this script with environment: TEST

API
---

### addOption($option_array)

When adding options all keys are optional, but many combinations will (fairly obviously) not be valid, i.e. an option without either a shortname or a longname.  The keys can be set in the argument array:

* *short* - The short name of the argument, e.g. "e"
* *long* - The long name of the argument, e.g. "environment"
* *value* - TRUE means a value is required, FALSE means it is optional, NULL means no value
* *help* - The help text to display for this parameter
* *required* - TRUE if this is a required parameter
* *type* - One of "text" (the default), "integer", "number" (for floats), "url" (returns url_parse on the value) or "json" (return json_decode on the value)
* *validate* - Should be set to a regular expression which the value must match

### setCommandName($name) / getCommandName()

Sets/Gets the command name for the usage output

### setCommandHelp($help) / getCommandHelp()

Sets/Gets the command help for the usage output

### parseGetOpts($getopt_style_array)

Use this method to supply a manual set of options (in the same format as returned by getopt).  Useful for testing or other methods of overriding getopt().

### getUsage()

Returns a string of the usage text

### usage($code=NULL, $message=NULL)

Prints out the usage text preceded by an optional extra message, and optionally exits the script with a supplied exit code (i.e. 0 for "ok", > 0 for "errors")

### getOption($short_or_long_name)

Returns the parsed option value (or array of parsed values if the parameter was given multiple times).  Will return the correct value regardless of whether the short name or long name version was supplied.

### getOptions()

Returns an array of all options with names as keys.  Where options have both long and short names, the values are duplicated across both keys, so you can still access from either the short or long name.

### setWidth($width) / getWidth()

Sets / Gets the current width of the usage output

### setMinimumHelpWidth($width) / getMinimumHelpWidth()

Sets / Gets the current minimum width of the "help" part of the usage output

### setPrinter(ClipClop_Printer_Interface $printer) / getPrinter()

Sets / Gets the class which prints out usage text (useful for testing)

### setQuitter(ClipClop_Quitter_Interface $printer) / getQuitter()

Sets / Gets the class which quits the script (useful for testing)
