<?php

/**
 * Classe que serve de base para os models que manipulam coleções de dados.<br>
 * Diferente do model singular, um model de coleção é um recorset que retorna uma coleção de models
 * singulares, todos individualmente manipuláveis.
 * 
 * Exemplo de uso:
 * <pre>
 * // listagem de usuarios
 * <b>$users</b> = new UsuarioCollection(); // extends ModelCollection
 * 
 * // define filtros
 * <b>$users</b>->setConstraint('campo1', 'valor1', 's', '=');
 * <b>$users</b>->setConstraint('campo2', 'valor2', 'i', '<>'); // ...
 * 
 * // executa o select no banco
 * $row_count = $this->select(); // retorna o numero de linhas extraidas e guarda internamente os registros
 * 
 * if ($row_count > 0) {
 *     // o proprio objeto coleção é um iterador
 *     foreach (<b>$users</b> as $user) {
 *         // cada $user é um model singular do objeto Usuario, que pode ser manipulado individualmente ou não
 *         echo 'Usuario: '. $user->getData('NOME');
 * 
 *         // exemplo de manipulação individual
 *         if ($user->getId() == 1) {
 *             $user->setData('NOME', 'mudando o nome do user 1');
 *             $user->update();
 *         }
 *     }
 * } else {
 *     echo 'Nenhum registro no banco';
 * }
 * </pre>
 * 
 * @package helpers
 * @uses BDConnector, BDQuery, BD, Model
 * @since 1.0 (5/4/13 Raphael)
 * @version 1.0 (5/4/13 Raphael)
 * @author Raphael Hardt <sistema13@furacao.com.br>
 */
class ModelCollection extends DBModel implements Iterator, ArrayAccess, Countable {

  /**
   * Nome da classe model singular que este model de coleção irá retornar. Caso não seja definido, 
   * um model singular genérico será utilizado
   * @var string
   * @access protected
   */
  protected $model = 'Model';
  
  /**
   * Ponteiro interno do iterador
   * @var type int
   * @access private
   */
  private $pointer = 0;
  
  /**
   * Dados retornados de um SELECT. Eles são acessíveis através de foreach{} com a própria classe
   * @var mixed
   * @access private
   */
  private $recordset_data = array();
  
  /**
   * Construtor da classe ModelCollection. Ao criar um model a partir dela, é necessário sobrescrever
   * esta classe da seguinte forma:<br>
   * <pre>
   * // class UsuarioCollection
   * public function __construct() {
   *     parent::__construct();
   *     
   *     $this->setTable('[NOME DA TABELA]'); // ex: TB_USUARIO
   *     $this->setModel('[NOME DO MODEL SINGULAR]'); // ex: Usuario
   * 
   *     // outras definicoes
   *     ...
   * }
   * </pre>
   * @author Raphael Hardt
   */
  public function __construct() {
    parent::__construct();
    $this->pointer = 0;
    $this->id = null; // collections não possuem id interno
  }

  // --------------------- INICIO DOS METODOS ITERATIVOS ----
  /**
   * NÃO MUDAR!<br>
   * Método iterativo. Serve para reiniciar o ponteiro interno.
   */
  public function rewind() {
    $this->pointer = 0;
  }

  /**
   * NÃO MUDAR!<br>
   * Método iterativo. Serve para retornar o model atual baseado no ponteiro interno.
   * @return mixed Model
   */
  public function current() {
    return $this->recordset_data[$this->pointer];
  }

  /**
   * NÃO MUDAR!<br>
   * Método iterativo. Serve para retornar o ponteiro interno atual.
   * @return int Ponteiro
   */
  public function key() {
    return $this->pointer;
  }

  /**
   * NÃO MUDAR!<br>
   * Método iterativo. Serve para andar para o próximo model.
   */
  public function next() {
    ++$this->pointer;
  }

  /**
   * NÃO MUDAR!<br>
   * Método iterativo. Serve para verificar quando o iterador deve parar.
   * @return bool <b>TRUE</b> continua a iteração, <b>FALSE</b> para.
   */
  public function valid() {
    return isset($this->recordset_data[$this->pointer]);
  }
  
  /**
   * NÃO MUDAR!<br>
   * Método iterativo. Serve para retornar o model baseado num ponteiro. Pode também ser
   * utilizado acesso por array (ex: $obj[0] ao inves de $obj->item(0))
   * @return mixed Model
   */
  public function item($pointer) {
    return $this->recordset_data[$pointer];
  }
  // --------------------- FIM DOS METODOS ITERATIVOS ----
  
  // --------------------- INICIO DOS METODOS DE ACESSO POR ARRAY ----
  
  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para adicionar um valor ao objeto (ex: $obj[] = 'valor').
   */
  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->recordset_data[] = $value;
    } else {
      $this->recordset_data[$offset] = $value;
    }
  }
  
  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para verificar se o elemento existe (ex: isset($obj[1]) ).
   */
  public function offsetExists($offset) {
    return isset($this->recordset_data[$offset]);
  }

  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para deletar um elemento do objeto (ex: unset($obj[1]) ).
   */
  public function offsetUnset($offset) {
    unset($this->recordset_data[$offset]);
  }

  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para retornar o valor de um elemento existente (ex: $var = $obj[1] ).
   */
  public function offsetGet($offset) {
    return isset($this->recordset_data[$offset]) ? $this->recordset_data[$offset] : null;
  }
  
  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para contar os elementos do array (ex: count($var) )
   * @return int
   */
  public function count() {
    return $this->total;
    //return count($this->recordset_data);
  }
  // --------------------- FIM DOS METODOS DE ACESSO POR ARRAY ----

  /**
   * Define o model singular que este model de coleção irá retornar a cada iteração. Deve
   * ser exatamente o mesmo tipo de model (ex: UsuarioCollection = Usuario)
   * @author Raphael Hardt
   * @param string $model Nome do model singular
   */
  final protected function setModel($model) {
    $this->model = $model;
  }

  /**
   * Retorna o model singular que foi definido para este model de coleção.
   * @author Raphael Hardt
   * @return string Model singular definido
   */
  final public function getModel() {
    return $this->model;
  }

  /**
   * Função que define um valor customizado pra certo campo da tabela.
   * Só muda os dados se puxados pelo select() desta classe.<br>
   * <b>Só substitua esta função se precisa de retornos bem específicos para cada campo. Não esqueça
   * de retornar $value para os outros campos da tabela.</b> Para datas e outros valores básicos, utilize format()
   * @author Raphael Hardt
   * @param string $col Nome do field da tabela (sempre é o alias)
   * @param string $value Valor que veio do banco, para referencia
   * @return string Deve retornar o valor a ser substituido. Para não alterar o valor padrão, retorne $value
   */
  protected function renderData($col, $value, $full) {
    return $value;
  }
  
  public function getArray() {
    $array = array();
    foreach ($this->recordset_data as $data) {
      if ($data instanceof Model) {
        $data = $data->getData();
      }
      $array[] = $data;
    }
    return $array;
  }

  public function select() {
    
    $this->id = null; // collections não possuem id interno
    
    // seta obrigatoriamente que o recordset deve retornar o campo ID junto, caso o model esteja definido
    if ($this->getModel() !== 'Model') {
      $this->setField($this->getTableAlias() . '.' .$this->primary_name, 'i');
    }

    // busca registros no banco
    $result = parent::listing();

    // constroi o recordset orientado a Model
    $recordset = array();
    $counter = 0;
    foreach ($result as $row) {
      $o = new $this->model();

      // renderiza cada campo com um valor customizado, caso a
      // funcao renderData() exista
      if (method_exists($this, 'renderData')) {
        foreach ($row as $fieldname => &$fieldvalue) {
          // chama a funcao pra mudar o valor
          $fieldvalue = $this->renderData(strtolower($fieldname), $fieldvalue, $row);
        }
        // apaga referencia
        unset($fieldvalue);
      }

      $o->setDatas($row);

      // seta id pro model
      if ($this->getModel() !== 'Model') {
        $o->id = $row[$this->primary_name];
      }

      $recordset[] = $o;
      ++$counter;
    }

    // salva o recordset no collection
    $this->recordset_data = $recordset;

    return $counter;
  }
  
  public function update() {
    // valida os valores
    if ($this->doValidation === true) {
      if (!$this->validate(true))
        return false;
    }

    // retorno
    $affected = parent::update();

    return $affected;
  }
  
  public function insert() {
    // valida os valores
    if ($this->doValidation === true) {
      if (!$this->validate(true))
        return false;
    }

    // retorno
    $id = parent::insert();
    
    // cria model
    $o = new $this->model();
    $o->setDatas( $this->getData() );
    $o->id = $id;
    
    // apaga dados definidos pro collection, por ser temporário e nao necessario manter
    unset($this->data);
    
    // salva o recordset no collection
    $this->recordset_data = array( $o );

    return (1);
  }

  function listing() {
    throw new Exception('DEPRECATED: A funcao ' . __FUNCTION__ . '() nao existe para esta classe');
  }

}