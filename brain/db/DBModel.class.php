<?php

djimport('brain.db.DBQuery');

class DBModel extends DBQuery {

  protected $bd;
  public $id;
  protected $log_name = 'um registro';
  public $log = false;
  protected $permanentDelete = true;
  protected $doValidation = true;
  protected $error = '';

  /**
   * Construtor da classe de conexão abstrata
   * @author Raphael Hardt
   * @param array|int $constraints ID ou filtros para selecionar apenas certos registros
   */
  public function __construct($constraints = array()) {
    parent::__construct();
    $this->bd = DB::getInstance();

    // TODO: tirar esta parte dessa classe
    if (!empty($constraints)) {
      if (is_int($constraints) || is_numeric($constraints)) {
        $this->id = $constraints;
      } else if (is_array($constraints)) {
        foreach ($constraints as $key => $value) {
          $this->setConstraint($key, $value);
        }
      }
    }
  }

  function validate() {
    return true;
  }

  /**
   * Insere um registro na tabela; Retorna true caso seja inserido com sucesso, e o $this->id é definido 
   * automaticamente
   * @author Raphael Hardt
   * @return bool
   */
  function insert() {

    // valida os valores
    //if ($this->doValidation === true) {
    //    if (!$this->validate())
    //       return false;
    //}
    // retorno
    $success = true;
    $id = null;

    // instancia de conexao com o banco de dados
    $bd = & $this->bd;

    // cria o SQL
    $this->buildSQL('INSERT');

    // pega as variaveis criadas do buildSQL
    $sql = $this->sql;
    $bind_v = $this->bind_values;

    // prepara o sql
    if ($success = $success && $bd->prepare($sql)) {
      foreach ($bind_v as $k => $value) {
        // binda o valor
        $bd->bind_param($bind_v[$k]);
      }

      // executa a query
      if ($success = $success && $bd->execute()) {
        // seta o id
        $id = $bd->insert_id();

        //Log::salvar('Inclusão de registro', '_NOTE', 'Usuário '.$GLOBALS['user']->login.' incluiu '.$this->log_name.' (id: '.$this->id.').');
      }
    }
    // sempre limpar o prepare, não importa se retornou true ou false
    $bd->free();

    return $id;
  }

  /**
   * Altera um registro na tabela; Retorna true caso seja alterado com sucesso
   * @author Raphael Hardt
   * @return bool
   */
  function update() {

    //TODO: passar validação pro model
    // valida os valores
    //if ($this->doValidation === true) {
    //    if (!$this->validate(true))
    //        return false;
    //}
    // retorno
    $success = true;

    // instancia de conexao com o banco de dados
    $bd = & $this->bd;

    // valor de id
    //if (!empty($this->id))
    //$this->setPrimaryKey($this->id);
    // cria o SQL
    $this->buildSQL('UPDATE');

    // pega as variaveis criadas do buildSQL
    $sql = $this->sql;
    $bind_v = $this->bind_values;

    // prepara o sql
    if ($success = $success && $bd->prepare($sql)) {
      foreach ($bind_v as $k => $value) {
        // binda o valor
        $bd->bind_param($bind_v[$k]);
      }

      // executa a query
      if ($success = $success && $bd->execute()) {

        $affected = $bd->affected_rows();
        //if ($this->log)
        //Log::save('Alteração de registro', Log::NOTE, 'Usuário ' . $_SESSION['user']->login . ' alterou ' . $this->log_name . ' (id: ' . $this->id . ').');
      }
    }
    // sempre limpar o prepare, não importa se retornou true ou false
    $bd->free();

    return $affected;
  }

  /**
   * Deleta um registro na tabela; Retorna true caso seja deletado com sucesso
   * @author Raphael Hardt
   * @return bool
   */
  function delete() {

    // valida os valores
    /* if(empty($this->id))
      return false; */

    // retorno
    $success = true;

    // instancia de conexao com o banco de dados
    $bd = & $this->bd;

    // valor de id
    //$this->setPrimaryKey($this->id);
    // cria o SQL
    if ($this->permanentDelete === true) {
      // se for permanente, deleta
      $this->buildSQL('DELETE');
    } else {
      // se não for permanente, só fazer update
      $this->setField('deletado', 's');
      $this->setData('deletado', '1');

      $this->buildSQL('UPDATE');
    }

    // pega as variaveis criadas do buildSQL
    $sql = $this->sql;
    $bind_v = $this->bind_values;

    // evita deletar toda a tabela (questoes de seguranca 12/03/2013)
    if (empty($bind_v)) {
      throw new BDConnectorException('Alerta: tentativa de excluir toda a tabela!');
    }

    // prepara o sql
    if ($success = $success && $bd->prepare($sql)) {
      foreach ($bind_v as $k => $value) {
        // binda o valor
        $bd->bind_param($bind_v[$k]);
      }

      // executa a query
      if ($success = $success && $bd->execute()) {

        $affected = $bd->affected_rows();
        //if ($this->log)
        //Log::save('Remoção de registro', Log::NOTE, 'Usuário ' . $GLOBALS['user']->login . ' editou ' . $this->log_name . ' (id: ' . $this->id . ').');
      }
    }
    // sempre limpar o prepare, não importa se retornou true ou false
    $bd->free();

    return $affected;
  }

  function select() {

    // retorno
    $success = true;

    // instancia de conexao com o banco de dados
    $bd = & $this->bd;

    // se não tiver valor de constraints, usar id
    if (!$this->getConstraints()) {
      if (empty($this->id))
        return false;

      $this->setPrimaryKey($this->id);
    }

    // só pega registros não deletados, se a tabela foi configurada para tal
    if ($this->permanentDelete !== true) {
      $this->setConstraint('deletado', '0', 's');
    }

    // cria o SQL
    $this->buildSQL('SELECT');

    // pega as variaveis criadas do buildSQL
    $sql = $this->sql;
    $bind_v = $this->bind_values;

    // prepara o sql
    if ($success = $success && $bd->prepare($sql)) {
      foreach ($bind_v as $k => $value) {
        // binda o valor
        $bd->bind_param($bind_v[$k]);
      }

      // executa a query
      if ($success = $success && $bd->execute()) {

        if ($success = $success && ($bd->num_rows() == 1)) {
          $row = $bd->fetch_assoc();

          // seta os valores do registro selecionado
          //foreach ($row as $fieldName => $fieldValue) {
          //  $this->setData($fieldName, $fieldValue);
          //  if (strtolower($fieldName) === 'id') {
          //    $this->id = $fieldValue;
          //  }
          //}
        }

        $this->total = $success ? 1 : 0;
      }
    }
    // sempre limpar o prepare, não importa se retornou true ou false
    $bd->free();

    return $row; // retorna o registro fetchado
  }

  /**
   * Seleciona todos os registros da tabela, de acordo com o filtro definido por constraints e exceptions;
   * Retorna uma matriz com todos os registros encontrados
   * @author Raphael Hardt
   * @param $type Define que tipo de keys devem ser retornados na matriz (assoc=associativo, num=numerico, array=ambos)
   * @return array
   */
  function listing() {

    // retorno
    $rows = array();

    // instancia de conexao com o banco de dados
    $bd = & $this->bd;

    // só pega registros não deletados, se a tabela foi configurada para tal
    if ($this->permanentDelete !== true) {
      $this->setConstraint('deletado', '0', 's');
    }

    // cria o SQL
    $this->buildSQL('SELECT');

    // pega as variaveis criadas do buildSQL
    $sql = $this->sql;
    $bind_v = $this->bind_values;

    // prepara o sql
    if ($bd->prepare($sql)) {
      foreach ($bind_v as $k => $value) {
        // binda o valor
        $bd->bind_param($bind_v[$k]);
      }

      // executa a query
      if ($bd->execute()) {

        //$rows = array();
        if (( $this->total = $bd->num_rows() ) >= 1) {

          while ($row = $bd->fetch_assoc()) {
            $rows[] = $row;
          }

          // seta os valores do registro selecionado
          //$this->setDatas($row);
        }
      }
    }
    // sempre limpar o prepare, não importa se retornou true ou false
    $bd->free();

    return $rows;
  }

  /**
   * Inicia uma transação; Necessário para mudanças em vários registros simultâneos que requer que o
   * processo seja concluido sempre com sucesso para não haver problemas de consistência
   * @author Raphael Hardt
   * @return bool Autocommit setado na ultima transação; Necessário para voltar ao estado anterior caso haja
   * 				transações indendadas/recursivas
   */
  public function startTransaction() {
    $bd = & $this->bd;

    // retorna o valor que estava no auto commit
    $old_ac = $bd->autocommit();

    $bd->autocommit(false);

    // retorna pra quem usar saber qual era o auto commit
    return $old_ac;
  }

  /**
   * Termina uma transação iniciada, deve ser passado se a transação foi bem sucedida ou não; Caso não seja,
   * todas alterações feitas durante a transação serão canceladas (rollback).
   * @author Raphael Hardt
   * @param bool $success Define se a transação foi bem sucedida ou não
   * @param bool $old_ac Autocommit da transação anterior, para retornar o estado caso haja transações indendadas/recursivas
   */
  public function endTransaction($success = false, $old_ac = null) {
    $bd = & $this->bd;

    if ($success === true) {
      // só commita se o auto commit anterior fosse true ou se não tivesse nenhum ac antes
      if ($old_ac === true || !isset($old_ac)) {
        $bd->commit();
      }
    } else {
      $bd->rollback();
    }

    // se tinha um auto commit antes, voltar pra ele 
    if (is_bool($old_ac)) {
      $bd->autocommit($old_ac);
    } else {
      $bd->autocommit(true);
    }
  }

}