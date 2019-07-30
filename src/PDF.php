<?php
namespace SynergiTech\ChromePDF;

use SynergiTech\ChromePDF\Exceptions\CannotOverwriteFileException;
use SynergiTech\ChromePDF\Exceptions\InvalidOptionException;
use SynergiTech\ChromePDF\Exceptions\InvalidValueException;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PDF
{
    /**
     * @var array list of defaults applied on instantiation
     */
    private $defaultoptions = array(
        'format' => 'A4', // override binary default
        'margin' => array(
            'top' => 0,
            'right' => 0,
            'bottom' => 0,
            'left' => 0,
        ),
        'printBackground' => true, //override binary default
    );

    /**
     * @var array list of filehandles created
     */
    private $filehandles = array();

    /**
     * @var array list of possible options from binary
     */
    private $possibleoptions = array(
        'waitUntil',
        'emulateMedia',
        'content',
        'page',
        'path',
        'viewport',
        'landscape',
        'scale',
        'displayHeaderFooter',
        'headerContent',
        'footerContent',
        'headerTemplate',
        'footerTemplate',
        'printBackground',
        'pageRanges',
        'format',
        'width',
        'height',
        'margin',
        'preferCSSPageSize',
        'file',
        'sandbox',
    );

    /**
     * @var string path to binary
     */
    private $pathtobinary;

    /**
     * @var string desired output of this class
     */
    protected $type = 'pdf';

    /**
     * @var array currently recorded options for this instance
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param string $pathtobinary the path to the chrome-pdf binary
     */
    public function __construct(string $pathtobinary = 'chrome-pdf')
    {
        // if the binary isn't available in the path, dev must override on construct
        $this->binary = $pathtobinary;

        $this->options = array_merge($this->options, $this->defaultoptions);
    }

    /**
     * Record an applied option to this instance
     * - this function can be overridden with custom functions
     *   to allow easy conversion from other libraries
     *
     * @param array $options a reference to an existing array of options
     * @param string $name the name of the option to apply
     * @param string|bool $value the input value
     *
     * @throws InvalidOptionException
     * @throws InvalidValueException
     *
     * @return void
     */
    protected function addOption(array &$options, string $name, $value) : void
    {
        if (in_array($name, $this->possibleoptions)) {
            if ($name == 'margin') {
                if (is_array($value)) {
                    foreach ($value as $side => $width) {
                        if (! array_key_exists($side, $options['margin'])) {
                            throw new InvalidOptionException(sprintf('margin-%s', $side));
                        }

                        $options['margin'][$side] = $width;
                    }
                } elseif (is_string($value)) {
                    $values = explode(' ', $value);
                    switch (count($values)) {
                        case 1:
                            // set all the same value
                            $options['margin'] = array_fill_keys(array_keys($options['margin']), $value);
                            break;
                        case 2:
                            // top-bottom
                            $options['margin']['top'] = $values[0];
                            $options['margin']['bottom'] = $values[0];
                            // left-right
                            $options['margin']['right'] = $values[1];
                            $options['margin']['left'] = $values[1];
                            break;
                        case 3:
                            // top
                            $options['margin']['top'] = $values[0];
                            // left-right
                            $options['margin']['left'] = $values[1];
                            $options['margin']['right'] = $values[1];
                            // bottom
                            $options['margin']['bottom'] = $values[2];
                            break;
                        case 4:
                            //top
                            $options['margin']['top'] = $values[0];
                            //right
                            $options['margin']['right'] = $values[1];
                            //bottom
                            $options['margin']['bottom'] = $values[2];
                            //left
                            $options['margin']['left'] = $values[3];
                            break;
                        default:
                            throw new InvalidValueException("margin", $value);
                    }
                }

                return;
            }

            $options[$name] = $value;

            return;
        }

        throw new InvalidOptionException($name);
    }

    /**
     * Public interface to addOption function
     *
     * @param string $name the name of the option to apply
     * @param string|bool $value the input value
     *
     * @return self allows chaining
     */
    public function setOption(string $name, $value) : self
    {
        $this->addOption($this->options, $name, $value);

        return $this;
    }

    /**
     * Shared interface to create array of options
     *
     * @param array $options additional options to apply to current options
     *
     * @return array compiled options
     */
    private function makeTempOptions(array $options = []) : array
    {
        $tempoptions = $this->options;

        foreach ($options as $name => $value) {
            $this->addOption($tempoptions, $name, $value);
        }

        return $tempoptions;
    }

    /**
     * Prepare an array of options based on input from file or URL
     *
     * @param string $filenameorurl the input filename or URL
     * @param array $options additional options to apply at this stage
     *
     * @return array compiled options
     */
    private function makeTempOptionsForFile(string $filenameorurl, array $options = []) : array
    {
        $tempoptions = $this->makeTempOptions($options);

        $tempoptions[((strpos($filenameorurl, '://') === false) ? 'file' : 'page')] = $filenameorurl;

        return $tempoptions;
    }

    /**
     * Prepare an array of options based on input from pre-rendered HTML
     *
     * @param string $html the input HTML
     * @param array $options additional options to apply at this stage
     *
     * @return array compiled options
     */
    private function makeTempOptionsForHTML(string $html, array $options = []) : array
    {
        $tempoptions = $this->makeTempOptions($options);

        $tempoptions['content'] = $html;

        return $tempoptions;
    }

    /**
     * Create a PDF from an existing file or URL and return it to the code
     *
     * @param string $filenameorurl the input filename or URL
     * @param array $options additional options to apply at this stage
     *
     * @return string generated PDF output
     */
    public function getOutput(string $filenameorurl, array $options = []) : string
    {
        $tempoptions = $this->makeTempOptionsForFile($filenameorurl, $options);

        // ensure output is to code
        if (array_key_exists('path', $tempoptions)) {
            unset($tempoptions['path']);
        }

        return $this->make($tempoptions);
    }

    /**
     * Create a PDF from pre-rendered HTML and return it to the code
     *
     * @param string $html the pre-rendered HTML
     * @param array $options additional options to apply at this stage
     *
     * @return string generated PDF output
     */
    public function getOutputFromHtml(string $html, array $options = []) : string
    {
        $tempoptions = $this->makeTempOptionsForHTML($html, $options);

        // ensure output is to code
        if (array_key_exists('path', $tempoptions)) {
            unset($tempoptions['path']);
        }

        return $this->make($tempoptions);
    }

    /**
     * Create a PDF from an existing file or URL and save it to disk
     *
     * @param string $filenameorurl the input filename or URL
     * @param string $output the destination file location on disk
     * @param array $options additional options to apply at this stage
     * @param bool $overwrite whether to overwrite a file at the pre-supplied destination
     *
     * @throws CannotOverwriteFileException
     *
     * @return string the output of the successful command
     */
    public function generate(string $filenameorurl, string $output, array $options = [], bool $overwrite = false) : string
    {
        if ($overwrite === false && file_exists($output)) {
            throw new CannotOverwriteFileException($output);
        }

        $tempoptions = $this->makeTempOptionsForFile($filenameorurl, $options);

        // ensure output is to filesystem
        $tempoptions['path'] = $output;

        return $this->make($tempoptions);
    }

    /**
     * Create a ODF from pre-rendered HTML and save it to disk
     *
     * @param string $html the pre-rendered HTML
     * @param string $output the destination file location on disk
     * @param array $options additional options to apply at this stage
     * @param bool $overwrite whether to overwrite a file at the pre-supplied destination
     *
     * @throws CannotOverwriteFileException
     *
     * @return string the output of the successful command
     */
    public function generateFromHtml(string $html, string $output, array $options = [], bool $overwrite = false) : string
    {
        if ($overwrite === false && file_exists($output)) {
            throw new CannotOverwriteFileException($output);
        }

        $tempoptions = $this->makeTempOptionsForHTML($html, $options);

        // ensure output is to filesystem
        $tempoptions['path'] = $output;

        return $this->make($tempoptions);
    }

    /**
     * Execute the result of the compiled options.
     *
     * @param array $options the compiled collection of options
     *
     * @return string the output of the successful command
     */
    private function make(array $options) : string
    {
        $command = array($this->binary, $this->type);

        foreach ($options as $option => $value) {
            if ($option == 'sandbox') {
                // only mess with sandbox if it is explicitly disabled
                if ($value === false) {
                    $command[] =  '--no-sandbox';
                }

                // don't need the below lines
                continue;
            }

            if ($option == 'headerContent' || $option == 'content' || $option == 'footerContent') {
                $tmpfile = tempnam(sys_get_temp_dir(), 'chromepdf-' . $option);
                rename($tmpfile, $tmpfile .= '.html'); // the temporary file needs to have extension html

                $this->filehandles[] = $tmpfile;

                file_put_contents($tmpfile, $value);

                $option = ($option == 'content') ? 'file' : str_replace('Content', 'Template', $option);

                $command[] = '--' . $option;
                $command[] = $tmpfile;
                continue;
            }

            $command[] = '--' . $option;

            if (is_array($value)) {
                $command[] = implode(',', $value);
                continue;
            } elseif ($value === true || $value === false) {
                $command[] = var_export($value, true);
                continue;
            } elseif (! is_string($value)) {
                throw new InvalidValueException;
            }

            $command[] = $value;
        }

        $process = new Process($command);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (count($this->filehandles) > 0) {
            array_map('unlink', $this->filehandles);
            $this->filehandles = array();
        }

        return $process->getOutput();
    }
}
