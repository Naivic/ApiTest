<?php

namespace Naivic\ApiTest;

/**
 * Базовый класс поставщика тестов
 */
class TestProvider {
    /**
     * @var array - описание доступа к данным для тестов
     */
    protected array $data = [];
    /**
     * @var array - описание тестов
     */
    protected $tests = [];

    /**
     * Конструктор
     * 
     * @param DataProvider $dataprov  - экземпляр класса - провайдера данных для тестов
     */
    public function __construct( private DataProvider $dataprov ) {
        $this->init();
    }

    /**
     * Инициализация данных и тестов
     *
     * Метод для перегрузки в потомках
     */
    protected function init() {
    }

    /**
     * Шорткат для получения данных из DataProvider
     *
     * @param string $path,...   - путь к данным для DataProvider
     *                             слева направо перечисляются ключи доступа
     *                             для соответствующих уровней вложенности
     * @return mixed             - данные, полученные выборкой
     *                             по последовательности ключей
     */
    protected function dp( ...$path ) {
        return $this->dataprov->get( ...$path );
    }

    /**
     * Шорткат для получения именованных данных из $this->data через DataProvider
     *
     * @param string $name       - ключ элемента в массиве $this->data
     *                             описывающего путь к данным в DataProvider
     * @param array  $args       - путь к данным, который будет добавлен
     *                             к начальной части пути, прочитанной из $this->data
     *                             по ключу $name
     * @return mixed             - данные, полученные выборкой из DataProvider
     *                             по предоставленной последовательности ключей
     */
    public function __call( $name, $args ) {
        if( !array_key_exists( $name, $this->data ) ) {
            throw new \Exception( "undefined data element : ".$name );
        }
        $d = $this->data[$name];
        return $this->dataprov->get( ...array_merge( $d, $args ) );
    }

    /**
     * Получение всех тестов из $this->tests
     *
     * @return array             - массив тестов
     */
    public function getAll() {

        foreach( $this->tests as $id => $next ) {
            yield $id => $next;
        }

    }

    /**
     * Получение определенного теста по его идентификатору
     *
     * @param string $id         - идентикатор теста -
     *                             ключ элемента в массиве $this->tests
     * @return array|null        - даные теста
     *                             или null, если тест с таким id не найден
     */
    public function getById( $id ) {

        return $this->tests[$id] ?? null;

    }

}
