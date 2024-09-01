<?php

namespace Naivic\ApiTest;

/**
 * Базовый класс поставщика данных для тестов
 */
class DataProvider {
    /**
     * @var array - описание данных
     */
    protected $data = [];

    /**
     * Конструктор
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Инициализация данных
     *
     * Метод для перегрузки в потомках
     */
    protected function init() {
    }

    /**
     * Получение данных из $this->data по последовательности ключей
     *
     * Если конечный элемент данных представляет собой массив -
     * будет возвращен случайно выбранный элемент этого массива
     *
     * @param string $path,...   - путь к данным
     *                             слева направо перечисляются ключи доступа
     *                             для соответствующих уровней вложенности
     * @return mixed             - данные, полученные выборкой
     *                             по последовательности ключей
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
