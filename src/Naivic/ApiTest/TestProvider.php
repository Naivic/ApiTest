<?php

namespace Naivic\ApiTest;

/**
 * Base class of Test Provider
 */
class TestProvider {
    /**
     * @var array - description of named data elements
     */
    protected array $data = [];
    /**
     * @var array - test description
     */
    protected array $tests = [];

    /**
     * Constructor
     * 
     * @param DataProvider $dataprov  - instance of Data Provider
     */
    public function __construct( private DataProvider $dataprov ) {
        $this->init();
    }

    /**
     * Initialize tests and named data elements
     *
     * Method to overload in child classes
     */
    protected function init() : void {
    }

    /**
     * Shortcut to get raw data from DataProvider
     *
     * @param string $path,...   - the path to data in DataProvider
     *                             list of access keys (left to right)
     *                             each key for corresponded nesting level
     * @return mixed             - data obtained from DataProvider
     *                             by accessing through given $path
     */
    protected function dp( string ...$path ) {
        return $this->dataprov->get( ...$path );
    }

    /**
     * Shortcut to get named data from $this->data through DataProvider
     *
     * @param string $name       - name of data element - as key in array $this->data,
     *                             which value describes the path to corresponded data in DataProvider
     * @param array  $args       - the path to data, which will de added to the path readed from 
     *                             value of $this->data[$name"]
     * @return mixed             - data obtained from DataProvider
     *                             by accessing through combined path ($this->data[$name"] + $args)
     */
    public function __call( $name, $args ) {
        if( !array_key_exists( $name, $this->data ) ) {
            throw new \Exception( "undefined data element : ".$name );
        }
        $d = $this->data[$name];
        return $this->dataprov->get( ...array_merge( $d, $args ) );
    }

    /**
     * Get all tests from $this->tests
     *
     * @return array             - test data array
     */
    public function getAll() {

        foreach( $this->tests as $id => $next ) {
            yield $id => $next;
        }

    }

    /**
     * Get single test by ID
     *
     * @param string $id         - test id (as key in $this->tests array)
     * @return array|null        - test data, or null if test id is not a key in $this->tests
     */
    public function getById( string $id ) : ?array {

        return $this->tests[$id] ?? null;

    }

}
