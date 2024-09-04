<?php

namespace Naivic\ApiTest;

/**
 * Base class of Data Provider
 */
class DataProvider {
    /**
     * @var array - data description
     */
    protected $data = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize data
     *
     * Method to overload in child classes
     */
    protected function init() {
    }

    /**
     * Get certain data element from $this->data by given $path
     *
     * If data element is an array, method returns random item of that array
     *
     * @param string $path,...   - path to data element
     *                             list of access keys (left to right)
     *                             each key for corresponded nesting level
     * @return mixed             - data obtained from $this->data
     *                             by accessing through given $path
     */
    public function get( ...$path ) {

        $data = $this->data;
        $log = "";
        foreach( $path as $step ) {

            if( !array_key_exists( $step, $data ) ) {
                throw new \Exception(
                    "data element '".$step."' not found"
                  . ($log == "") ? "" : ", log path : ".$log
                );
            }
            $log .= "/".$step;
            $data = $data[ $step ];

        }

        if( is_array( $data ) ) {
            return $data[ array_rand($data) ];
        } else {
            return $data;
        }
        
    }

}
