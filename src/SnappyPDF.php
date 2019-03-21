<?php
namespace SynergiTech\ChromePDF;

use InvalidArgumentException;

class SnappyPDF extends PDF
{
    /**
     * @var array list of snappy options relevant to ChromePDF and how to map them to usable options
     */
    private $snappyoptions = array(
        'footer-html' => 'footerTemplate',
        'header-html' => 'headerTemplate',
        'load-error-handling' => null,
        'margin-top' => array(
            'mapto' => 'margin',
            'suboption' => 'top',
        ),
        'margin-right' => array(
            'mapto' => 'margin',
            'suboption' => 'right',
        ),
        'margin-bottom' => array(
            'mapto' => 'margin',
            'suboption' => 'bottom',
        ),
        'margin-left' => array(
            'mapto' => 'margin',
            'suboption' => 'left',
        ),
        'orientation' => array(
            'mapto' => 'landscape',
            'boolean' => array(
                'landscape' => true,
                'portrait' => false,
                'Landscape' => true,
                'Portrait' => false,
            ),
        ),
        'viewport-size' => array(
            'mapto' => 'viewport',
            'suboption' => null,
        ),
    );

    /**
     * Record an applied Snappy option to this instance
     * - it proceeds to run the parent function to allow use of
     *   both Snappy and ChromePDF options in the same instance
     *
     * @param array $options a reference to an existing array of options
     * @param string $name the name of the option to apply
     * @param string|bool $value the input value
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    protected function addOption(array &$options, string $name, $value) : void
    {
        if (array_key_exists($name, $this->snappyoptions)) {
            if (is_array($this->snappyoptions[$name])) {
                $todo = $this->snappyoptions[$name];

                if (array_key_exists('suboption', $todo)) {
                    if ($todo['suboption'] === null) {
                        throw new InvalidArgumentException(sprintf('Remove your option "%s" or rewrite to match format for "%s"', $name, $todo['mapto']));
                    }

                    $options[$todo['mapto']][$todo['suboption']] = $value;
                } elseif (array_key_exists('boolean', $todo) && array_key_exists($value, $todo['boolean'])) {
                    $options[$todo['mapto']] = $todo['boolean'][$value];
                }
            } elseif ($this->snappyoptions[$name] !== null) {
                $options[$this->snappyoptions[$name]] = $value;
            }

            // silently drop where mapped to is null

            return;
        }

        parent::addOption($options, $name, $value);
    }
}
