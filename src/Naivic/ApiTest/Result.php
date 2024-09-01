<?php

namespace Naivic\ApiTest;

/**
 * Базовый класс сборки результатов тестирования
 */
class Result {
    /**
     * @var array - массив результатов тестов, ключи - идентификаторы тестов
     */
    public array $tests = [];
    /**
     * @var array - массив идентификаторов тестов, выполненных успешно
     */
    public array $success = [];
    /**
     * @var array - массив идентификаторов тестов, выполненных с ошибками
     */
    public array $fails = [];
    /**
     * @var bool|null - результат текущего теста
     */
    private ?bool $res = null;
    /**
     * @var string - идентификатор текущего теста
     */
    private string $id;
    /**
     * @var string - имя текущего теста
     */
    private string $name;
    /**
     * @var array - массив сообщений о проверках, выполненных с ошибкой
     */
    private array $errs;
    /**
     * @var array - массив сообщений о проверках, выполненных успешно
     */
    private array $msgs;

    /**
     * Сигнал к началу следующего теста
     *
     * Каждый следующий старт переносит накопленные результаты предыдущего теста
     * в соответствующие массивы. Поэтому по окончании последнего теста
     * обязательно нужно дать пустой старт.
     *
     * @param string $name       - имя теста
     * @param string $name       - id теста
     */
    public function start( string $name, string $id ) {

        if( $this->res !== null ) {
            $this->tests[ $this->id ] = [
                "id"   => $this->id,
                "name" => $this->name,
                "res"  => $this->res,
                "msgs" => $this->msgs,
                "errs" => $this->errs,
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
        $this->errs = [];

    }

    /**
     * Сообщение о результатах очередной проверки в рамках текущего теста
     *
     * Если в сообщении указан негативный результат - весь тест
     * будет признан неуспешным.
     *
     * @param bool   $res       - результат проверки
     * @param string $msg       - сообщение по итогам проверки
     */
    public function note( $res, $msg ) {
        if( !$res ) {
            $this->res = $res;
            $this->errs[] = $msg;
        } else {
            $this->msgs[] = $msg;
        }
    } 

    /**
     * Вывод результата тестирования в stdout
     *
     * @param string $depth     - если "verbose" - будут показаны сообщения о всех проверках
     */
    public function show( $depth = null ) {
        echo "Total : ".count($this->tests);
        echo "\nSuccess : ".count($this->success).", Fails : ".count($this->fails);
        if( count($this->fails) > 0 ) {
            echo "\nProblematic:";
            foreach( $this->fails as $id ) {
                $this->showtest( $depth, $id, $this->tests[$id]["errs"] );
            }
        }
        if( count($this->success) > 0 ) {
            echo "\nDone:";
            foreach( $this->success as $id ) {
                $this->showtest( $depth, $id, $this->tests[$id]["msgs"] );
            }
        }
    }

    /**
     * Вывод в stdout данных по отдельному тесту
     *
     * @param string $depth     - если "verbose" - будут показаны сообщения о всех проверках
     * @param string $id        - идентификатор теста
     * @param array  $msgs      - массив строк-сообщений
     */
    private function showtest( $depth, $id, $msgs ) {
        echo "\n\t".$id." : ".$this->tests[$id]["name"];
        if( $depth == "verbose" ) {
            echo "\n\t\t- ".join( "\n\t\t- ", $msgs );
            echo "\n";
        }
    }

}
