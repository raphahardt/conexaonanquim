<?php

/**
 * Classe que serve de base para os models que manipulam dados, mas apenas um registro.<br>
 * Como são models singulares, eles podem apenas guardar e manipular um registro de uma tabela.
 * Se o filtro que faz o seu select retornar mais que 1 registro, é retornado false e o model
 * não é instanciado corretamente. <b>Para manipulação de mais de 1 registro, utilize ModelCollection</b>.
 * 
 * Exemplo de uso:
 * <pre>
 * // manipulação de usuario
 * <b>$user</b> = new Usuario(); // extends Model
 * <b>$user</b>->setPrimaryKey(12); // ou $user = new Usuario(12);
 * 
 * // executa o select no banco
 * $success = $this->select(); // retorna TRUE caso o registro tenha sido selecionado, senão FALSE
 * 
 * if ($row_count > 0) {
 *     // o proprio objeto coleção é um iterador
 *     echo 'Usuario: '. <b>$user</b>->getData('NOME');
 * } else {
 *     echo 'Usuario 12 inexistente';
 * }
 * </pre>
 * 
 * Veja um exemplo de como NÃO usar:
 * <pre>
 * <b>$user</b> = new Usuario(); // extends Model
 * <b>$user</b>->setConstrint('id', 12, 'i', '>'); // (..where id > 12..) este filtro retorna mais de 1 registro
 * 
 * <b>$user</b>->select(); // FALSE
 * </pre>
 * Para estes casos, utilize o model de coleção:
 * <pre>
 * <b>$users</b> = new UsuarioCollection(); // extends ModelCollection
 * <b>$users</b>->setConstrint('id', 12, 'i', '>'); 
 * 
 * <b>$users</b>->select();
 * <b>$users</b>[0]->getData('NOME'); // OK (primeiro usuario da lista)
 * </pre>
 * 
 * @package helpers
 * @uses BDConnector, BDQuery, BD
 * @since 1.0 (5/4/13 Raphael)
 * @version 1.0 (5/4/13 Raphael)
 * @author Raphael Hardt <sistema13@furacao.com.br>
 */
class Model extends DBModel implements ArrayAccess, Countable {
  
  function __construct($constraints = array()) {

    if (!empty($constraints)) {
      if (is_int($constraints) || is_numeric($constraints)) {
        $this->id = $constraints;
      } else if (is_array($constraints)) {
        foreach ($constraints as $key => $value) {
          $this->setConstraint($key, $value);
        }
      }
    }

    parent::__construct();
  }
  
  // --------------------- INICIO DOS METODOS DE ACESSO POR ARRAY ----
  
  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para adicionar um valor ao objeto (ex: $obj[] = 'valor').
   */
  public function offsetSet($offset, $value) {
    /*if (is_null($offset)) {
      //$this->data[] = $value;
      throw new Exception('Você não pode atribuir um valor para o Model sem um identificador');
      // TODO: deixar ele acrescentar valores, desde que os fields tenham sido definidos
      // e que o valor a ser adicionado não ultrapasse o numero de campos definidos
    } else {
      $this->setData($offset, $value);
    }*/
    throw new Exception('Você não pode atribuir um valor para o Model por array. Utilize setData()');
  }
  
  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para verificar se o elemento existe (ex: isset($obj[1]) ).
   */
  public function offsetExists($offset) {
    return isset($this->data[$offset]);
  }

  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para deletar um elemento do objeto (ex: unset($obj[1]) ).
   */
  public function offsetUnset($offset) {
    $this->unsetData($offset);
  }

  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para retornar o valor de um elemento existente (ex: $var = $obj[1] ).
   */
  public function offsetGet($offset) {
    if (is_numeric($offset)) {
      // TODO: procurar uma função ou metodo mais eficiente de busca (talvez busca binaria)
      $counter = 0;
      foreach ($this->data as $val) {
        if ($counter == $offset) {
          return $val;
        }
        ++$counter;
      }
      return null;
    } else {
      return isset($this->data[strtolower($offset)]) ? $this->getData($offset) : null;
    }
  }
  
  /**
   * NÃO MUDAR!<br>
   * Método de acesso como array. Serve para contar os elementos do array (ex: count($var) )
   */
  public function count() {
    return count($this->data);
  }
  // --------------------- FIM DOS METODOS DE ACESSO POR ARRAY ----

  function select() {
    if ($row = parent::select()) {
      // seta os valores do registro selecionado
      $this->setDatas($row);
      
      $this->id = $row[$this->primary_name];
      
      return true;
      
    } else
      return false;
  }

  function listing() {
    throw new Exception('A função ' . __FUNCTION__ . '() não existe para esta classe');
  }
  
  function update() {
    if (get_class($this) === 'Model')
      throw new Exception('A função ' . __FUNCTION__ . '() não é permitida para um Model sem definição');
    
    // valida os valores
    if ($this->doValidation === true) {
      if (!$this->validate(true))
        return false;
    }
    
    if (!empty($this->id))
      $this->setPrimaryKey($this->id);
    
    return parent::update();
  }
  
  function insert() {
    if (get_class($this) === 'Model')
      throw new Exception('A função ' . __FUNCTION__ . '() não é permitida para um Model sem definição');
    
    // valida os valores
    if ($this->doValidation === true) {
      if (!$this->validate(true))
        return false;
    }
    
    $affected = 0;
    if ($id = parent::insert()) {
      $this->id = $id;
      ++$affected;
    }

    return $affected;
  }
  
  function delete() {
    if (get_class($this) === 'Model')
      throw new Exception('A função ' . __FUNCTION__ . '() não é permitida para um Model sem definição');
    
    $affected = parent::delete();
    if ($affected > 0) {
      // deixa o objeto limpo
      $this->cleanSQL();
    }
    return $affected;
  }
  
  function exists($constraints, $exceptions) {
    if (get_class($this) === 'Model')
      throw new Exception('A função ' . __FUNCTION__ . '() não é permitida para um Model sem definição');
    
    return parent::exists($constraints, $exceptions);
  }

}