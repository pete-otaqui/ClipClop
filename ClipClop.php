<?php
/**
 * ClipClop - a PHP option parser based on getopt()
 *
 * @example
 * $clipclop = new \ClipClop();
 * 
 * $clipclop->addOption(array(
 *     'short' => 'e', // shortname, i.e. "-e"
 *     'long' => 'environment', // longname, i.e. "--environment"
 *     'value' => FALSE, // value required?  skip or set NULL for no value
 *     'help' => 'Set the environment', // help text
 *     'required' => TRUE, // This 'option' must be set to something
 * ));
 * 
 * $clipclop->addOption(array(
 *     'short' => 'v', // shortname
 *     'long' => 'verbose', // longname
 * ));
 * 
 * 
 * $clipclop->run();
 * 
 * $clipclop->getOption('e'); // returns the value set for 'e' or 'environment'
 * 
 * $clipclop->getOption('environment'); // returns the value set for 'environment' or 'e'
 * 
 * $clipclop->getOption('v'); // returns TRUE if set, NULL otherwise
 * 
 * $clipclop->getOptions(); // returns array('environment'=>'test', 'v'=>TRUE);
 * 
 * $clipclop->setCommandName('foome'); // overrides default of $argv[0]
 * 
 * $clipclop->usage();
*/
class ClipClop
{
    private $options = array();
    private $short_options = array();
    private $long_options = array();
    private $getopts;
    private $parsed_options = array();
    private $command_name;
    private $has_run = FALSE;

    /**
     * Construct a ClipClop instance
     * @param array $options Array of options to add right away
     */
    public function __construct($options = array())
    {
        foreach ( $options as $option ) {
            $this->addOption($option);
        }
    }

    /**
     * Add an option to be parsed.
     * @param array $option Containing keys 'value', 'short', 'long', 'required', 'help'
     */
    public function addOption($option)
    {
        $this->options[] = $option;
        $value_part = '';
        if ( array_key_exists('value', $option) && $option['value'] !== NULL ) {
            $value_part = ($option['value']) ? ':' : '::';
        }
        if ( array_key_exists('short', $option) ) {
            $this->short_options[] = $option['short'] . $value_part;
        }
        if ( array_key_exists('long', $option) ) {
            $this->long_options[] = $option['long'] . $value_part;
        }
        usort($this->options, function($a, $b) {
            $cmp = 0;
            if ( array_key_exists('short', $a) && array_key_exists('short', $b) ) {
                $cmp = strcmp($a['short'], $b['short']);
            }
            if ( $cmp === 0 && array_key_exists('long', $a) && array_key_exists('long', $b) ) {
                $cmp = strcmp($a['long'], $b['long']);
            }
            return $cmp;
        });
    }

    /**
     * Run the parser using getopt()
     */
    public function run()
    {
        $gotopts = getopt(implode('', $this->short_options), $this->long_options);
        $this->parseGetOpts($gotopts);
    }

    /**
     * Run the parser with a predefined array, useful for testing
     * @param  array $gotopts A getopt() style array
     */
    public function parseGetOpts($gotopts)
    {
        $this->has_run = TRUE;
        if ( $gotopts === FALSE ) {
            $this->usage(1);
        }
        // loop over all the option we *might* have got
        foreach ( $this->options as $option ) {
            // we prefer long options
            // did we get a long option?
            $found = FALSE;
            if ( array_key_exists('long', $option) ) {
                $lname = $option['long'];
                if ( array_key_exists($lname, $gotopts) ) {
                    $found = TRUE;
                    $this->parsed_options[$lname] = $gotopts[$lname];
                }
            // or did we get a short option for this?
            }
            if ( !$found && array_key_exists('short', $option) ) {
                $sname = $option['short'];
                if ( array_key_exists($sname, $gotopts) ) {
                    $found = TRUE;
                    $this->parsed_options[$sname] = $gotopts[$sname];
                }
            // was it required?
            }
            if ( !$found && array_key_exists('required', $option) ) {
                $this->usage(1);
            }
        }
    }

    /**
     * Get the formatted usage printout
     * @return string The formatted usage
     */
    public function getUsage()
    {
        $out = "";
        $required = array(
            'helps' => array(),
            'names' => array(),
        );
        $optional = array(
            'helps' => array(),
            'names' => array(),
        );
        foreach ($this->options as $option) {
            $container = ( array_key_exists('required', $option) ) ? 'required' : 'optional';
            $opt_names = array();
            if ( array_key_exists('short', $option) ) {
                $short_name = "-{$option['short']}";
                if ( array_key_exists('value', $option) && $option['value'] !== NULL ) {
                    $short_name .= '=value';
                }
                $opt_names[] = $short_name;
            }
            if ( array_key_exists('long', $option) ) {
                $long_name = "--{$option['long']}";
                if ( array_key_exists('value', $option) && $option['value'] !== NULL ) {
                    $long_name .= '=value';
                }
                $opt_names[] = $long_name;
            }
            $opt_help = '';
            if ( array_key_exists('help', $option) ) {
                $opt_help = $option['help'];
            }
            if ( $container == 'required' ) {
                $required['helps'][] = $opt_help;
                $required['names'][] = implode(', ', $opt_names);
            } else {
                $optional['helps'][] = $opt_help;
                $optional['names'][] = implode(', ', $opt_names);
            }
        }
        $name_length = 0;
        foreach ( $required['names'] as $name ) {
            if ( strlen($name) > $name_length ) {
                $name_length = strlen($name);
            }
        }
        foreach ( $optional['names'] as $name ) {
            if ( strlen($name) > $name_length ) {
                $name_length = strlen($name);
            }
        }
        $name_length += 1;
        $output_length = max($this->getWidth(), round($name_length+$this->getMinimumHelpWidth()));
        $help_length = $output_length - $name_length;
        $out = $this->getCommandName();
        $out .= "\n";
        $out .= $this->formatDescriptions($required, "Required", $name_length, $help_length);
        $out .= $this->formatDescriptions($optional, "Optional", $name_length, $help_length);
        return $out;
    }
    /**
     * Print out the usage, optionally exiting with a given code
     * @param  integer $code The exit code (0 for OK, 1 for error, etc)
     */
    public function usage($code) {
        $out = $this->getUsage();
        print $out;
        if ( $code !== NULL ) {
            exit($code);
        }
    }


    private function formatDescriptions($descriptions, $text, $name_length, $help_length) {
        $out = "";
        if ( count($descriptions['names']) > 0 ) {
            $out .= "\n{$text}:\n";
            for ( $i=0, $imax=count($descriptions['names']); $i<$imax; $i++ ) {
                $temp_name = $descriptions['names'][$i];
                $temp_help = $descriptions['helps'][$i];
                $temp_name = str_pad($temp_name, $name_length);
                $out .= $temp_name." ";
                $temp_help = $temp_help;
                $chunks = ceil(strlen($temp_help)/$help_length);
                $out .= substr($temp_help, 0, $help_length) . "\n";
                for ( $j=1; $j<$chunks; $j++ ) {
                    $help_part = substr($temp_help, ($j*$help_length), $help_length) . "\n";
                    $chunk_len = $name_length+strlen($help_part)+1;
                    $help_part = str_pad($help_part, $chunk_len, " ", STR_PAD_LEFT);
                    $out .= $help_part;
                }
            }
        }
        return $out;
    }

    /**
     * Get the value for an option by long name or short name
     * @param  string $name The name of the option
     * @return string       The value of the option, NB - returns TRUE for boolean (valueless) options if they were provided, unlike getopt().
     */
    public function getOption($name)
    {
        if ( !$this->has_run ) {
            $this->run();
        }
        $given_option = NULL;
        $other_name = NULL;
        // is this a valid thing to ask for?
        foreach ( $this->options as $given_option ) {
            // in either case, track what the "other" name for this
            // might be, so that we could invoke the long form
            // "--verbose" but ask for the short form "v"
            if ( array_key_exists('long', $given_option) && $given_option['long'] === $name ) {
                $option = $given_option;
                $other_name = array_key_exists('short', $option) ? $option['short'] : NULL;
                break;
            } elseif ( array_key_exists('short', $given_option) && $given_option['short'] === $name ) {
                $option = $given_option;
                $other_name = array_key_exists('long', $option) ? $option['long'] : NULL;
                break;
            }
        }
        if ( !$given_option ) {
            throw new \Exception('Invalid option requested');
        }
        if ( array_key_exists($name, $this->parsed_options) ) {
            $return = $this->parsed_options[$name];
        } elseif ( array_key_exists($other_name, $this->parsed_options) ) {
            $return = $this->parsed_options[$other_name];
        } else {
            $return = NULL;
        }
        if ( $return === FALSE ) {
            $return = TRUE;
        }
        return $return;
    }

    /**
     * Get an array of all options, duplicate values for those with short and long names
     * @return array Array of ('name'=>'value')
     */
    public function getOptions()
    {
        $return = array();
        foreach ( $this->options as $option ) {
            if ( array_key_exists('long', $option) ) {
                $return[$option['long']] = $this->getOption($option['long']);
            }
            if ( array_key_exists('short', $option) ) {
                $return[$option['short']] = $this->getOption($option['short']);
            }
        }
        return $return;
    }

    const DEFAULT_MINIMUM_HELP_WIDTH = 30;
    private $minimum_help_width;
    /**
     * Get the minimum help string length for the usage printout
     * @return integer The length, defaults to 30
     */
    public function getMinimumHelpWidth()
    {
        if ( !$this->minimum_help_width ) {
            $this->minimum_help_width = self::DEFAULT_MINIMUM_HELP_WIDTH;
        }
        return $this->minimum_help_width;
    }
    /**
     * Set the minimum help string length for the usage printout
     * @param integer $width The length
     */
    public function setMinimumHelpWidth($width)
    {
        $this->minimum_help_width = $width;
    }

    const DEFAULT_WIDTH = 80;
    private $width;
    /**
     * Get the overall length for the usage printout.  Defaults to `tput cols`
     * @return number The length
     */
    public function getWidth()
    {
        if ( !$this->width ) {
            try {
                $this->width = (int) exec('tput cols');
            } catch (\Exception $e) {}
            if ( !$this->width ) {
                $this->width = self::DEFAULT_WIDTH;
            }
        }
        return $this->width;
    }
    /**
     * Set the width of the usage printout
     * @param integer $width The width
     */
    public function setWidth($width)
    {
        $this->width = (int) $width;
    }

    /**
     * Set the command name for usage prinout, defaults to $argv[0]
     * @param string $name The command name
     */
    public function setCommandName($name)
    {
        $this->command_name = $name;
    }

    /**
     * Get the command name for usage printout, defaults to $argv[0]
     * @return string The command name
     */
    public function getCommandName()
    {
        if ( !$this->command_name ) {
            global $argv;
            $this->command_name = $argv[0];
        }
        return $this->command_name;
    }
}