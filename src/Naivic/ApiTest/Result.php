<?php

namespace Naivic\ApiTest;

/**
 * Base class of Test Result Collector
 *
 * Collects and prints results of testrun
 */
class Result {
    /**
     * @var int - maximim message length
     */
    public const MSG_MAXLEN = 200;
    /**
     * @var array - test results [ id_of_test => test_data, ... ]
     */
    public array $tests = [];
    /**
     * @var array - array of successful test ids 
     */
    public array $success = [];
    /**
     * @var array - array of fail test ids 
     */
    public array $fails = [];
    /**
     * @var bool|null - result of current test
     */
    private ?bool $res = null;
    /**
     * @var string - id of current test
     */
    private string $id;
    /**
     * @var string - name of current test
     */
    private string $name;
    /**
     * @var array - array of messages for current test
     */
    private array $msgs;
    /**
     * @var array - stack of pointers to current messages array (according to nesting level of checks)
     */
    private array $mst;
    /**
     * @var int - current nesting level of checks
     */
    private int $level;


    /**
     * Start next test
     *
     * Each subsequent start transfers the accumulated results of the previous test
     * to the corresponding arrays. Therefore, after the end of the last test
     * it is necessary to make an empty start.
     *
     * @param string $name       - test name
     * @param string $name       - test id
     */
    public function start( string $name, string $id ) {

        if( $this->res !== null && $this->id !== "" ) {
            $this->tests[ $this->id ] = [
                "id"   => $this->id,
                "name" => $this->name,
                "res"  => $this->res,
                "msgs" => $this->msgs,
            ];
            if( $this->res ) {
                $this->success[] = $this->id;
            } else {
                $this->fails[] = $this->id;
            }
        }

        $this->id = $id;
        $this->name = $name;
        $this->res = true;
        $this->msgs = [];
        $this->mst = [ &$this->msgs ];
        $this->level = 0;

    }

    /**
     * Store message and set corresponded check result
     *
     * If $res == false, result of whole test will be set as negative
     *
     * @param bool  $res        - check result
     * @param string $msg       - сообщение по итогам проверки
     */
    public function note( bool $res, string $msg ) {
        $this->mst[ $this->level ][] = [ $res, $msg ];
        if( !$res ) {
            $this->res = $res;
        }
    } 

    /**
     * Start new section of messages
     *
     * @param string $header    - section header
     */
    public function startSection( string $header ) {
        $cnt = count( $this->mst[ $this->level ] );
        $this->mst[ $this->level ][] = [ null, $header, [] ];
        $this->level++;
        $this->mst[ $this->level ] = &$this->mst[ $this->level-1 ][$cnt][2];
    } 

    /**
     * End current section of messages
     */
    public function endSection() {
        if( $this->level == 0 ) {
            throw new \Exception( "Error nesting sections - endSection without corresponded startSection" );
        }
        array_pop( $this->mst );
        $this->level--;
    } 

    /**
     * Get status of current test
     *
     * @return bool  - current status
     */
    public function getStatus() {
        return $this->res;
    }


    /**
     * Return common testrun results as a string
     *
     * @param string $depth     - "verbose" to show all messages (according to test result)
     * @return string           - full result of testrun
     */
    public function show( string $depth = "" ) : string {
        ob_start();
        echo "Total : ".count($this->tests);
        echo "\nSuccess : ".count($this->success).", Fails : ".count($this->fails);
        if( count($this->fails) > 0 ) {
            echo "\nProblematic:";
            foreach( $this->fails as $id ) {
                $this->showtest( $depth, $id, $this->tests[$id]["msgs"] );
            }
        }
        if( count($this->success) > 0 ) {
            echo "\nDone:";
            foreach( $this->success as $id ) {
                $this->showtest( $depth, $id, $this->tests[$id]["msgs"] );
            }
        }
        $text = ob_get_contents();
        ob_end_clean();
        return $text;
    }

    /**
     * Print result for single given test
     *
     * @param string $depth     - "verbose" to show all messages (according to test result)
     * @param string $id        - test id
     * @param array  $msgs      - array of messages for this test
     */
    private function showtest( string $depth, string $id, array $msgs ) {
        echo "\n\t".$id." : ".$this->tests[$id]["name"];
        if( $depth == "verbose" ) {
            foreach( $msgs as $msg ) {
                $this->showmsg( $msg, $this->tests[$id]["res"], 0 );
            }
            echo "\n";
        }
    }

    /**
     * Print single message
     *
     * @param array  $msg       - single message
     * @param bool   $res       - entire test result
     * @param int    $level     - nesting level of message
     */
    private function showmsg( array $msg, bool $res, int $level ) {
        if( $msg[0] === null ) {
            echo "\n\t\t".str_repeat("\t",$level).$msg[1];
            foreach( $msg[2] as $m ) {
                $this->showmsg( $m, $res, $level+1 );
            }
        } else {
            if( $res == $msg[0] ) {
                echo "\n\t\t".str_repeat("\t",$level)
                    .($res ? "+ " : "- ")
                    .mb_strimwidth( str_replace( "\n", '\n', $msg[1] ), 0, static::MSG_MAXLEN, "..." )
                ;
            }
        }
    }
}
