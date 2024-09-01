<?php

namespace Naivic\ApiTest;

/**
 * Базовый класс выполнения тестирования
 */
class Test {

    /**
     * Конструктор
     * 
     * @param object $api             - экземпляр класса - драйвера API
     * @param TestProvider $testprov  - экземпляр класса - провайдера тестов
     * @param Result $res             - экземпляр класса - сборщика результатов тестирования
     */
    public function __construct(
        protected $api,
        protected TestProvider $testprov,
        protected Result $res,
    ) {
    }

    /**
     * Исполнение отдельного теста
     *
     * Метод для перегрузки в потомках
     *
     * @param string $id   - идентификатор теста
     * @param array  $test - массив данных теста
     */
    protected function run( $id, $test ) {
    }

    /**
     * Исполнение всех тестов от провайдера
     */
    public function runAll() {

        foreach( $this->testprov->getAll() as $id => $test ) {
            $this->res->start( name: $test["name"], id: $id );
            $this->run( $id, $test );
        }
        $this->res->start( name: "", id: "" );

    }

    /**
     * Исполнение теста с конкретным идентификатором
     * 
     * @param string $id              - идентификатор теста
     */
    public function runById( $id ) {

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
     * Исполнение проверки элемента данных типа "объект"
     * 
     * @param string $name              - название проверки
     * @param array  $expect            - образец для элемента
     * @param mixed  $given             - элемент, полученный в ответе хоста
     */
    protected function checkObject( string $name, array $expect, array $given ) {

        $expFields = array_keys( $expect );
        $givFields = array_keys( $given );

        $excess  = array_diff( $givFields, $expFields );
        $missing = array_diff( $expFields, $givFields );

        if( count( $excess ) > 0 ) {
            $this->res->note( false, "excess fields of answer : ".join( ",", $excess ) );
        }

        if( count( $missing ) > 0 ) {
            $this->res->note( false, "missing fields of answer : ".join( ",", $missing ) );
        }

        $same = array_intersect( $givFields, $expFields );
        if( count( $same ) == 0 ) {
            $this->res->note( false, "answer dont have any of expected fields : ".join( ",", $expFields ) );
        }

        foreach( $same as $field ) {
            $this->check( 'field "'.$field.'" value', $expect[$field], $given[$field] );
        }

    }

    /**
     * Исполнение отдельной проверки элемента данных
     * 
     * @param string $name              - название проверки
     * @param array  $expect            - образец для элемента
     * @param mixed  $given             - элемент, полученный в ответе хоста
     */
    protected function check( string $name, $expect, $given ) {

        $sign = $expect[0];
        $val = $expect[1];
        if( $sign == "object" ) return $this->checkObject( $name, $val, $given );
        if( $sign == "array" ) return $this->checkObject( $name, $val, $given );

        $giventext = is_string( $given ) ? '"'.$given.'"' : var_export( $given, 1 );
        if( eval( 'return $given '.$sign.' $val;' ) ) {
            $this->res->note( true, $name." is OK, have got ".$giventext );
        } else {
            $this->res->note( false, "invalid ".$name.", expected ".$val." - have got ".$giventext );
        }

    }

}
