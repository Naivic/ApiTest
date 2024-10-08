<?php

namespace Naivic\ApiTest;

/**
 * Base class of Test framework
 */
class Test {

    /**
     * List of check operations, which will be performed by php' eval()
     */
    protected array $basic_check = [
        "==", "!=", ">=", "<=", ">", "<",
    ];

    /**
     * Constructor
     * 
     * @param object $api             - instance of API driver
     * @param TestProvider $testprov  - instance of Test Provider
     * @param Result $res             - instance of Test Result Collector
     */
    public function __construct(
        protected $api,
        protected TestProvider $testprov,
        protected Result $res,
    ) {
    }

    /**
     * Perform single test
     *
     * Method to overload in child classes
     *
     * @param string $id   - test id
     * @param array  $test - test data
     */
    protected function run( string $id, array $test ) : void {
    }

    /**
     * Perform all tests from TestProvider
     */
    public function runAll() : void {

        foreach( $this->testprov->getAll() as $id => $test ) {
            $this->res->start( name: $test["name"], id: $id );
            $this->run( $id, $test );
        }
        $this->res->start( name: "", id: "" );

    }

    /**
     * Perform single test given by ID
     * 
     * @param string $id  - test id
     */
    public function runById( string $id ) : void {

        $test = $this->testprov->getById( $id );
        if( $test === null ) {
            $this->res->start( "Invalid test id", $id );
            $this->res->note( false, "test not found" );
        } else {
            $this->res->start( name: $test["name"], id: $id );
            $this->run( $id, $test );
        }
        $this->res->start( name: "", id: "" );

    }

    /**
     * Perform check of "array" type
     * 
     * @param string $name              - name of check
     * @param array  $expect            - description of expected value
     * @param mixed  $given             - given value
     */
    protected function checkArray( string $name, array $expect, $given ) : void {

        if( !is_array( $given ) ) {
            $this->res->note( false, $name." fails: given value is not array" );
        } else {

            $this->res->note( true, $name." type check pass, array given" );
            if( count( $expect ) > 0 ) {

                if( count( $given ) == 0 ) {
                    $this->res->note( false, $name." fails: given is an empty array" );
                }

                foreach( $expect as $n => $val ) {
                    $item = array_shift( $given );
                    $this->check( 'item "'.$n.'" value', $val, $item );
                }
            }
        }

    }


    /**
     * Perform check of "object" type
     * 
     * @param string $name              - name of check
     * @param array  $expect            - description of expected value
     * @param mixed  $given             - given value
     */
    protected function checkObject( string $name, array $expect, $given ) : void {

        if( !is_array( $given ) ) {
            $this->res->note( false, $name." fails: given value is not object" );
            return;
        }

        $expFields = array_keys( $expect );
        $givFields = array_keys( $given );

        $excess  = array_diff( $givFields, $expFields );
        $missing = array_diff( $expFields, $givFields );

        if( count( $excess ) > 0 ) {
            $this->res->note( false, $name." fails: excess fields of answer : ".join( ",", $excess ) );
        }

        if( count( $missing ) > 0 ) {
            $this->res->note( false, $name." fails: missing fields of answer : ".join( ",", $missing ) );
        }

        $same = array_intersect( $givFields, $expFields );
        if( count( $same ) == 0 ) {
            $this->res->note( false, $name." fails: answer dont have any of expected fields : ".join( ",", $expFields ) );
        }

        foreach( $same as $field ) {
            $this->check( 'field "'.$field.'" value', $expect[$field], $given[$field] );
        }

    }

    /**
     * Perform check of "object_equal" type
     * 
     * @param string $name              - name of check
     * @param array  $expect_path       - path to expected object in DataProvider
     * @param mixed  $given             - given value
     */
    protected function checkObjectEqual( string $name, array $expect_path, $given ) : void {

        if( !is_array( $given ) ) {
            $this->res->note( false, $name." fails: given value is not object" );
            return;
        }

        $expect = $this->testprov->dp( ...$expect_path );

        $expFields = array_keys( $expect );
        $givFields = array_keys( $given );

        $excess  = array_diff( $givFields, $expFields );
        $missing = array_diff( $expFields, $givFields );

        if( count( $excess ) > 0 ) {
            $this->res->note( false, $name." fails: excess fields of answer : ".join( ",", $excess ) );
        }

        if( count( $missing ) > 0 ) {
            $this->res->note( false, $name." fails: missing fields of answer : ".join( ",", $missing ) );
        }

        $same = array_intersect( $givFields, $expFields );
        if( count( $same ) == 0 ) {
            $this->res->note( false, $name." fails: answer dont have any of expected fields : ".join( ",", $expFields ) );
        }

        foreach( $same as $field ) {
            $this->check( 'field "'.$field.'" value', [ "==", $expect[$field] ], $given[$field] );
        }

    }

    /**
     * Perform single check
     * 
     * @param string $name              - name of check
     * @param array  $expect            - description of expected value
     * @param mixed  $given             - given value
     */
    protected function check( string $name, array $expect, $given ) {

        $type = array_shift( $expect );
        if( in_array( $type, $this->basic_check, true ) ) {
            // Perform basic type check, based on php eval()
            $val = array_shift( $expect );
            $giventext = is_string( $given ) ? '"'.$given.'"' : var_export( $given, 1 );
            if( eval( 'return $given '.$type.' $val;' ) ) {
                $this->res->note( true, $name." is OK, have got ".$giventext );
            } else {
                $this->res->note( false, "invalid ".$name.", expected ".$val." - have got ".$giventext );
            }
        } else {
            // Perform custom type check
            $val = array_shift($expect);
            switch( $type ) {
            	case "object" :
                    $this->res->startSection( $name." type Object" );
                    $this->checkObject( $name, $val, $given );
                    $this->res->endSection();
                    break;
                case "object_equal" :
                    $this->res->startSection( $name." type Object" );
                    $this->checkObjectEqual( $name, $val, $given );
                    $this->res->endSection();
                    break;
                case "array" :
                    $this->res->startSection( $name." type Array" );
                    $this->checkArray( $name, $val, $given );
                    $this->res->endSection();
                    break;
                default :
                    throw new \Exception( "Unknown check type : ".$type );
            }
        }

    }

}
