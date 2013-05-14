<?php

class DBQuery {

  private $tables;
  private $joins;
  private $schema;
  private $start = 0;
  private $page = 0;
  private $limit = 0;
  private $max_limit = 2000;
  protected $data;
  private $fields;
  private $sfields;
  private $search = array(); //string
  private $search_fields = array();
  private $order = array();
  private $group = array();
  private $constraints = array();
  private $primary;
  protected $primary_name = 'id';
  private $exception = '';
  private $exception_values = array();
  public $sql;
  public $bind_values;
  // for objectcollection
  public $total = 0;
  public $group_type = 'single';
  public $group_field;
  public $group_field_2;
  private $last_state = null; // grava o ultimo state do objeto

  function __construct() {
    
  }

  /**
   * Gera uma chave única para um array, numa nomenclatura key_#, onde # é um numero único numa
   * ordem sequencial
   * @author Raphael Hardt
   * @param string $key Nome da key que quer deixar unico
   * @param array $array Array que será acrescentado o novo key unico
   */
  public function generateUniqueKey($key, $array) {
    $i = 1;
    // guarda o key
    $original_key = $key;

    // vai até o fim do array, verificando se o key já existe
    while (array_key_exists($key, $array)) {
      // cria um key unico (key_n)
      $key = $original_key . '_' . $i;
      ++$i;
    }
    return $key;
  }

  /**
   * Limpa os valores de montagem de SQL; não limpa tabelas definidas!
   * @author Raphael Hardt
   */
  public function cleanSQL() {
    $this->start = 0;
    $this->page = 0;
    $this->limit = 0;

    $this->data = array();
    $this->fields = array();
    $this->sfields = array();

    $this->search = array(); //string
    $this->search_fields = array();
    $this->order = array();
    $this->group = array();
    $this->constraints = array();
    $this->merges_on = array();
    $this->primary = null;
    $this->exception = '';
    $this->exception_values = array();

    unset($this->sql, $this->bind_values);
  }

  /**
   * Guarda o último "estado" definido. Serve para redefinir um objeto ao seu estado inicial (ou estado
   * definido pelo desenvolvedor) quando necessário. Por ex: 
   * <pre>
   * $model = new User(); // definição inicial: campos nome, senha e email
   * $model->saveState();
   * 
   * $model->setFields('id', 'nome'); // mudou os campos
   * ...
   * 
   * $model->loadState(); // volta os campos definidos para nome, senha e email
   * </pre>
   * @author Raphael Hardt
   */
  public function saveState() {

    $this->last_state = array(
        'schema' => $this->schema,
        'tables' => $this->tables,
        'joins' => $this->joins,
        'procedure' => $this->procedure,
        'procedure_params' => $this->procedure_params,
        'procedure_data' => $this->procedure_data,
        'start' => $this->start,
        'page' => $this->page,
        'limit' => $this->limit,
        'data' => $this->data,
        'fields' => $this->fields,
        'sfields' => $this->sfields,
        'search' => $this->search,
        'search_fields' => $this->search_fields,
        'order' => $this->order,
        'group' => $this->group,
        'constraints' => $this->constraints,
        'merges_on' => $this->merges_on,
        'primary' => $this->primary,
        'primary_name' => $this->primary_name,
        'exception' => $this->exception,
        'exception_values' => $this->exception_values
    );
    return true;
  }

  /**
   * Volta o último "estado" definido. Serve para redefinir um objeto ao seu estado inicial (ou estado
   * definido pelo desenvolvedor) quando necessário. Por ex: 
   * <pre>
   * $model = new User(); // definição inicial: campos nome, senha e email
   * $model->saveState();
   * 
   * $model->setFields('id', 'nome'); // mudou os campos
   * ...
   * 
   * $model->loadState(); // volta os campos definidos para nome, senha e email
   * </pre>
   * @author Raphael Hardt
   */
  public function loadState() {

    $this->schema = $this->last_state['schema'];

    $this->tables = $this->last_state['tables'];
    $this->joins = $this->last_state['joins'];

    $this->procedure = $this->last_state['procedure'];
    $this->procedure_params = $this->last_state['procedure_params'];
    $this->procedure_data = $this->last_state['procedure_data'];

    $this->start = $this->last_state['start'];
    $this->page = $this->last_state['page'];
    $this->limit = $this->last_state['limit'];

    $this->data = $this->last_state['data'];
    $this->fields = $this->last_state['fields'];
    $this->sfields = $this->last_state['sfields'];

    $this->search = $this->last_state['search'];
    $this->search_fields = $this->last_state['search_fields'];

    $this->order = $this->last_state['order'];
    $this->group = $this->last_state['group'];
    $this->constraints = $this->last_state['constraints'];
    $this->merges_on = $this->last_state['merges_on'];
    $this->primary = $this->last_state['primary'];
    $this->primary_name = $this->last_state['primary_name'];

    $this->exception = $this->last_state['exception'];
    $this->exception_values = $this->last_state['exception_values'];

    return true;
  }

  /**
   * Defina uma tabela para montar o query
   * @author Raphael Hardt
   * @param string $table
   */
  public function setTable($table) {
    // limpa espaços
    $table = trim($table);
    // divide a string nos espaços
    $tmp_table = explode(' ', $table);
    // o nome da tabela é sempre o primeiro elemento do explode, e o alias é o ultimo
    $table = reset($tmp_table);
    $alias = end($tmp_table);

    if (empty($alias))
      $alias = $table;
    $this->tables[] = array('#name' => $table, '#alias' => $alias);
  }

  /**
   * Define tabelas para montar o query
   * @author Raphael Hardt
   * @param mixed $tables
   */
  public function setTables($tables) {
    $args = func_get_args();
    $t = array();
    $joins = 0;
    $aliases = 0;

    if (is_array($tables)) {
      $t = $tables;
    } else if (count($args) > 1) {
      $t = $args;
    } else {
      throw new Exception('BDQuery: Tabelas inválidas.');
    }

    //verifica se existe alguma tabela com join, e se (nesse caso) todas as tabelas possuem um alias
    //as tabelas sem join sao inseridas em um vetor a parte
    foreach ($t as $table) {
      if (!empty($table['#join']) && !empty($table['#on'])) {
        if (!empty($table['#alias'])) {
          ++$aliases;
        } else {
          throw new Exception('BDQuery: Falta um alias na tabela: ' . $table['#name']);
        }
        ++$joins;
        $this->joins[] = $table;
      } else {
        if ($table['#alias']) {
          ++$aliases;
        } else {
          $table['#alias'] = '';
        }
        $this->tables[] = $table;
      }
    }

    if ($joins > 0 && (count($this->joins) + count($this->tables) != $aliases)) {
      throw new Exception('BDQuery: Todas as tabelas precisam ter um alias.');
    }
  }
  
  /**
   * Retorna o nome da primeira tabela definida
   * @author Raphael Hardt
   * @return string $table
   */
  public function getTable() {
    if (!is_array($this->tables)) // TODO: colocar para buscar entre os joins também
      $this->tables = array();
    $table = reset($this->tables);
    return $table['#name'];
  }

  /**
   * Retorna o alias da primeira tabela definida. Se não tiver alias, retorna o nome
   * @author Raphael Hardt
   * @return string $table
   */
  public function getTableAlias() {
    if (!is_array($this->tables)) // TODO: colocar para buscar entre os joins também
      $this->tables = array();
    $table = reset($this->tables);
    return $table['#alias'] ? $table['#alias'] : $table['#name'];
  }

  /**
   * Define tabelas para montar o query
   * @author Raphael Hardt
   * @param mixed $tables
   */
  public function getTables() {
    return array_merge((array) $this->tables, (array) $this->joins);
  }

  /**
   * Limpa as tabelas definidas para montagem do query
   * @author Raphael Hardt
   */
  public function clearTables() {
    $this->tables = array();
    $this->joins = array();
  }

  /**
   * Define uma frase que será transformada em palavras para procurar nos campos definidos pelo
   * setSearchField()
   * @author Raphael Hardt
   * @param string $search Frase de busca
   */
  public function setSearch($search) {
    // tirar caracteres especiais
    $search = urldecode($search);
    $search = str_replace(array('!', '@', '#', '$', '%', '*', '&', '+', '?', ':', '.', ',', '|', '\\', '<', '>', '/', '-'), '', $search);

    // vetor de palavras encontradas na frase
    $words = array();

    // cada char da frase
    $s = '';

    // auxiliador pro contador de palavras
    $j = 0;

    // se alguma frase está entre aspas (ou apostrofe), mostrar ela como se fosse uma palavra só
    // variaveis auxiliadoras para identificar as aspas ou apostrofes
    $quote = false;
    $qquote = false;

    // corre cada char da frase
    $len = strlen($search);
    for ($i = 0; $i < $len; $i++) {
      if ($search{$i} == "'") {
        // se encontrar um apostrofe

        if ($qquote) {
          // se o char ainda estiver dentro de aspas
          $s .= $search{$i};
        } else if ($quote) {
          // se encontrou o apostrofe de fim, fechar palavra
          $words[$j] = $s;
          ++$j;
          $s = '';
          $quote = false;
        } else {
          // se encontrou um apostrofe de começo, começar palavra
          if (!empty($s)) {
            // se a palavra anterior não tiver sido fechada, fechar
            $words[$j] = $s;
            ++$j;
            $s = '';
          }
          // seta que a palavra esta entre apostrofes
          $quote = true;
        }
      } else if ($search{$i} == '"') {
        // se encontrar uma aspa

        if ($quote) {
          // se o char ainda estiver dentro de apostrofo
          $s .= $search{$i};
        } else if ($qquote) {
          // se encontrou uma aspa de fim, fechar palavra
          $words[$j] = $s;
          ++$j;
          $s = '';
          $qquote = false;
        } else {
          // se encontrou uma aspa de começo, iniciar palavra

          if (!empty($s)) {
            // se a palavra anterior não tiver sido fechada, fechar
            $words[$j] = $s;
            ++$j;
            $s = '';
          }
          // seta que a palavra está entre aspas
          $qquote = true;
        }
      } else if ($search{$i} == ' ') {
        // se encontrou um espaço (separador de palavras)

        if ($quote || $qquote) {
          // se estiver dentro de aspas ou apostrofes
          $s .= $search{$i};
        } else if (!empty($s)) {
          // se a palavra anterior existir, fechar
          $words[$j] = $s;
          ++$j;
          $s = '';
        }
      } else {
        // se encontrou qualquer caracter, ela faz parte da palavra
        $s .= $search{$i};
      }
    }
    if (!empty($s)) {
      // se ainda tem alguma palavra pra fechar, fechar
      $words[$j] = $s;
    }

    $this->search = $words;
  }

  /**
   * Define uma palavra ou frase para busca em todos os campos definidos pela setSearchFields(),
   * aqui ela terá uma key unica que será acessivel para consulta via getSearchAtKey();
   * Utilize sempre um key não-numerico, para não conflitar com os campos de busca do setSearch()
   * @author Raphael Hardt
   * @param string $key Key unica para valor de pesquisa
   * @param string $word Palavra ou frase de pesquisa 
   */
  public function setSearchAtKey($key, $word) {
    $this->search[$key] = $word;
  }

  /**
   * Deleta uma palavra ou frase que foi definida como busca para os campos de setSearchFields()
   * @author Raphael Hardt
   * @param string $key Key unica do valor de pesquisa
   */
  public function unsetSearchAtKey($key) {
    unset($this->search[$key]);
  }

  /**
   * Define uma palavra ou frase para busca em todos os campos definidos pela setSearchFields(),
   * aqui ela terá uma key unica que será acessivel para consulta via getSearchAtKey();
   * Utilize sempre um key não-numerico, para não conflitar com os campos de busca do setSearch()
   * @author Raphael Hardt
   * @param string $key Key unica para valor de pesquisa
   * @return string Palavra ou frase de pesquisa
   */
  public function getSearchAtKey($key) {
    return $this->search[$key];
  }

  /**
   * Define os campos de busca de uma tabela
   * @author Raphael Hardt
   * @param string $key Campo da tabela
   * @param string $type Tipo de pesquisa
   * @param string $content_type Tipo de campo (string, int, float). Use:
   * 				s = string, i = integer, f = float, m = money (0.00), d = date
   * @param bool $replace
   */
  public function setSearchField($key, $type = '=', $content_type = 's', $replace = false) {
    // verifica se é pra substituir o key passado ou se cria como novo
    $original_key = $key;
    if ($replace == false) {
      $key = $this->generateUniqueKey($key, $this->search_fields);
    }
    // padrão é todos os campos serem tratados como string
    if (empty($content_type))
      $content_type = 's';

    $this->search_fields[$key] = array('#key' => $original_key, '#type' => $type, '#content' => $content_type, '#like' => ($type == 'like'));
  }

  /**
   * Retorna um campo de busca definido de uma tabela
   * @author Raphael Hardt
   * @param string $key
   * @return array Informações do campo definido
   */
  public function getSearchField($key) {
    return $this->search_fields[$key];
  }

  /**
   * Retorna todos os campos de busca definidos de uma tabela
   * @author Raphael Hardt
   * @param string $key
   * @return array Informações de todos os campos, num matriz
   */
  public function getSearchFields() {
    return $this->search_fields;
  }

  /**
   * Função auxiliar para setOrder()
   * @author Raphael Hardt
   * @access private
   * @param string $key
   * @param string $direction
   */
  private function _setOrder($key, $direction = 'asc') {
    $this->order[$key] = array('#field' => $key, '#direction' => $direction);
  }

  /**
   * Define um ou vários campos como ordenador de uma tabela
   * @author Raphael Hardt
   * @param mixed $order Pode ser passados varios parametros (order1, order2, ...), pode ser uma string
   * 			do nome do campo ou dois argumentos onde o primeiro é o campo e o segundo é a direção
   * 			(asc, desc), ou uma matriz com os campos, onde #field é o campo e #direction é a direção
   */
  public function setOrder($order) {
    $args = func_get_args();
    $count = count($args);

    if ($count > 1) {
      // se tiver mais de 1 argumento
      // deduz que o segundo argumento é o direction
      $direction = strtolower($args[1]);

      // se for o direction, chama função para setar 
      if (($direction == 'asc' || $direction == 'desc') && $count == 2) {
        $this->_setOrder($order, $direction);
      } else {
        // se não for, cada argumento é um order diferente
        $len = count($args);
        for ($i = 0; $i < $count; $i++) {
          $this->_setOrder($args[$i]);
        }
      }
    } else if (is_string($order)) {
      // se tiver apenas 1 argumento e ele for string
      $this->_setOrder($order);
    } else if (is_array($order)) {
      // se tiver apenas 1 argumento e ele for array

      if (isset($order['#field'])) {
        // se for apenas um registro de order
        $key = $order['#field'];
        $direction = $order['#direction'];

        $this->_setOrder($key, $direction);
      } else {
        // se for um array bidimensional
        foreach ($order as $o) {
          // vai em cada um e seta o key dele corretamente
          // OBS: se tiver duas vezes um order para o mesmo campo, o que vai prevalecer
          // será o ultimo!
          $this->_setOrder($o['#field'], $o['#direction']);
        }
      }
    } else {
      $this->order = array();
    }
  }

  /**
   * Retorna os campos de ordenação da tabela
   * @author Raphael Hardt
   * @return array
   */
  public function getOrder() {
    return $this->order;
  }

  /**
   * Define um ou vários campos como agrupamento de uma tabela
   * @author Raphael Hardt
   * @param mixed $order Pode ser passados varios parametros (group1, group2, ...), pode ser uma string
   * 			do nome do campo, ou um vetor com os campos, onde cada valor é um campo
   */
  public function setGroupBy($group) {
    $args = func_get_args();
    $count = count($args);

    if ($count > 1) {
      // se tiver mais de 1 argumento
      $len = count($args);
      for ($i = 0; $i < $count; $i++) {
        $key = $args[$i];
        $this->group[$key] = $key;
      }
    } else if (is_string($group)) {
      // se tiver apenas 1 argumento e ele for string
      $this->group[$group] = $group;
    } else if (is_array($group)) {
      // se tiver apenas 1 argumento e ele for array

      $tmp = array();
      foreach ($group as $g) {
        // vai em cada um e seta o key dele corretamente
        // OBS: se tiver duas vezes um group by para o mesmo campo, o que vai prevalecer
        // será o ultimo!

        $tmp[$g] = $g;
      }
      $this->group = $tmp;
      unset($tmp);
    } else {
      $this->group = array();
    }
  }

  /**
   * Retorna os campos de agrupamento da tabela
   * @author Raphael Hardt
   * @return array
   */
  public function getGroupBy() {
    return $this->group;
  }

  /**
   * Define um campo que vai buscar exatamente o que o campo solicitar (ex: ID = 1)
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @param string $value Valor do campo a ser filtrado
   * @param string $type Operador de comparação para o campo com o valor (opções: =, <>, LIKE, ...)
   * @param string $extra 
   * @param bool $replace Se substitui o campo já definido ou não
   */
  public function setConstraint($key, $value, $content_type = 's', $type = '=', $replace = false) {
    // verifica se é pra substituir o key passado ou se cria como novo
    $original_key = $key;
    if ($replace == false) {
      $key = $this->generateUniqueKey($key, $this->constraints);
    }
    // padrão é todos os campos serem tratados como string
    if (empty($content_type))
      $content_type = 's';

    $this->constraints[$key] = array('#key' => $original_key, '#value' => $value, '#type' => $type, '#content_type' => $content_type);
  }

  /**
   * Define um campo que vai buscar exatamente o que o campo solicitar no modo IN (ex: ID IN (1,2,3) )
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @param string $value Valor do campo a ser filtrado
   */
  public function setConstraintIn($key, $value, $content_type = 's') {
    // padrão é todos os campos serem tratados como string
    if (empty($content_type))
      $content_type = 's';

    if (!isset($this->constraints[$key])) {
      $this->constraints[$key] = array('#key' => $key, '#values' => array(), '#type' => 'IN', '#content_type' => $content_type);
    }
    if (is_array($value)) {
      $this->constraints[$key]['#values'] = $value;
    } else {
      $this->constraints[$key]['#values'][] = $value;
    }
  }

  /**
   * Define um campo que vai buscar exatamente o que o campo solicitar no modo NOT IN (ex: ID NOT IN (1,2,3) )
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @param string $value Valor do campo a ser filtrado
   */
  public function setConstraintNotIn($key, $value, $content_type = 's') {
    // padrão é todos os campos serem tratados como string
    if (empty($content_type))
      $content_type = 's';

    if (!isset($this->constraints[$key])) {
      $this->constraints[$key] = array('#key' => $key, '#values' => array(), '#type' => 'NOT IN', '#content_type' => $content_type);
    }
    if (is_array($value)) {
      $this->constraints[$key]['#values'] = $value;
    } else {
      $this->constraints[$key]['#values'][] = $value;
    }
  }

  /**
   * Define um campo que vai buscar exatamente o que o campo solicitar no modo BETWEEN (ex: ID 1 BETWEEN 3 )
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @param string $value Valor do campo a ser filtrado
   */
  public function setConstraintBetween($key, $value1, $value2, $content_type = 's', $replace = false) {
    // verifica se é pra substituir o key passado ou se cria como novo
    $original_key = $key;
    if ($replace == false) {
      $key = $this->generateUniqueKey($key, $this->constraints);
    }
    // padrão é todos os campos serem tratados como string
    if (empty($content_type))
      $content_type = 's';

    $this->constraints[$key] = array('#key' => $original_key, '#value1' => $value1, '#value2' => $value2, '#type' => 'BETWEEN', '#content_type' => $content_type);
  }

  /**
   * Retorna uma constraint definida
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @return array Informações da contraint
   */
  public function getConstraint($key) {
    return $this->constraints[$key];
  }

  /**
   * Retorna todas as constraints definidas
   * @author Raphael Hardt
   * @return array Informações das contraints
   */
  public function getConstraints() {
    return $this->constraints;
  }

  /**
   * Retorna todas as constraints definidas
   * @author Raphael Hardt
   * @return array Informações das contraints
   */
  public function unsetConstraints() {
    $this->constraints = array();
  }

  /**
   * Define um primary_key para encontrar exatamente o registro que procura, pelo ID
   * @author Raphael Hardt
   * @param int $value Valor do ID
   */
  public function setPrimaryKey($value) {
    $this->primary = $value;
  }

  /**
   * Retorna o primary_key definido
   * @author Raphael Hardt
   * @return int Valor do ID
   */
  public function getPrimaryKey() {
    return $this->primary;
  }

  /**
   * Define um limite inicial para busca de registros (como LIMIT N,M, onde start é o N)
   * @author Raphael Hardt
   * @param int $value
   */
  public function setStart($start) {
    $this->start = $start;
  }

  /**
   * Retorna o start definido
   * @author Raphael Hardt
   * @return int Valor do ID
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * Define um limite final para busca de registros (como LIMIT N,M, onde limit é o M+N)
   * @author Raphael Hardt
   * @param int $value
   */
  public function setLimit($limit) {
    $this->limit = $limit;
  }

  /**
   * Retorna o start definido
   * @author Raphael Hardt
   * @return int Valor do ID
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * Define uma where personalizada; setando ela, todos as funções de montagem de where serão ignoradas
   * @author Raphael Hardt
   * @param string $exception WHERE personalizado
   */
  public function setException($exception) {
    $args = func_get_args();
    // tira o primeiro argumento, pois é o string exception
    array_shift($args);
    $count = count($args);

    $this->exception = $exception;

    // verifica se tem valores para bindar no exception
    if ($count > 0) {
      if (!isset($this->exception_values))
        $this->exception_values = array();

      if (is_array($args[0])) {
        $this->exception_values = $args[0];
      } else {
        for ($i = 0; $i < $count; $i++) {
          $this->exception_values[] = $args[$i];
        }
      }
    }
  }

  /**
   * Deleta uma where personalizada definida
   * @author Raphael Hardt
   */
  public function unsetException() {
    unset($this->exception, $this->exception_values);
  }

  /**
   * Retorna o exception (where personalizado) definido
   * @author Raphael Hardt
   * @return string WHERE personalizado
   */
  public function getException() {
    return $this->exception;
  }

  /**
   * Define um campo pra tabela
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @param string $type Tipo de campo (string, int, float ...)
   */
  public function setField($key, $type = 's') {

    // limpa espaços
    $key = trim($key);

    // tabela padrão é vazia
    $table = '';

    // verifica que tipo de campo está sendo passado para o setField
    if (preg_match('/^(?P<function>\w+\((?P<field>[^,\)]+)(,[^\)]+)?\))\s+(as\s+)?(?P<alias>\w*)$/i', $key, $match)) {
      // ex: to_char(campo, 'DD-MM') as campo
      if (strpos($match['field'], '.') !== false) {
        list($table, $field) = explode('.', $match['field']);
      } else {
        $field = $match['field'];
      }
      $alias = $match['alias'];
      $fieldraw = $match['function'];
    } elseif (preg_match('/^(?P<function>\w+\((?P<field>[^,\)]+)(,[^\)]+)?\))$/i', $key, $match)) {
      // ex: to_char(campo, 'DD-MM'), sem alias
      if (strpos($match['field'], '.') !== false) {
        list($table, $field) = explode('.', $match['field']);
      } else {
        $field = $match['field'];
      }
      $alias = $field;
      $fieldraw = $match['function'];
    } elseif (preg_match('/^(?P<field>[^\s]+)(\s+(as\s+)?(?P<alias>\w*))?$/i', $key, $match)) {
      // ex: campo as campo (alias opcional)
      if (strpos($match['field'], '.') !== false) {
        list($table, $field) = explode('.', $match['field']);
      } else {
        $field = $match['field'];
      }
      if ($match['alias'])
        $alias = $match['alias'];
      else
        $alias = $field;

      $fieldraw = ($table ? $table . '.' : '') . $field;
    }
    // padrão é todos os campos serem tratados como string
    if (empty($type))
      $type = 's';

    // TODO: passar essa validação para o BUILDSQL ================================================
    // verifica se a tabela existe mesmo nesse objeto
    $alltables = array_merge((array) $this->tables, (array) $this->joins);

    if (empty($alltables)) {
      // não existem tabelas definidas ainda
      // não fazer nada, lidar com falta de tabelas no buildSQL
    } else {
      // existem tabelas definidas
      $table_exists = empty($table);
      reset($alltables);
      while ((list(, $t) = each($alltables)) && !$table_exists) {
        if (strtolower($table) === strtolower($t['#alias'])) {
          $table_exists = true;
        }
      }
      if (!$table_exists) {
        throw new Exception('BDQuery: a tabela (' . $table . ') definida para o campo (' . $field . ') não está definida nesta classe.');
      }
    }

    // define o campo
    $this->fields[strtolower($alias)] = array('#field' => strtoupper($field), '#raw' => $fieldraw, '#alias' => $alias, '#table' => $table, '#type' => $type);

    // cria um valor nulo praquele campo criado
    $this->data[strtolower($alias)] = null;
  }

  /**
   * Apaga um campo que passa para instrução SQL
   * @author Raphael Hardt
   * @param string $key Nome do campo
   */
  public function unsetField($key) {
    unset($this->fields[strtolower($key)], $this->data[strtolower($key)]);
  }

  /**
   * Define vários campos pra tabela
   * @author Raphael Hardt
   * @param mixed $fields
   */
  public function setFields($fields) {
    $args = func_get_args();
    $count = count($args);

    if ($count > 1) {
      // se tiver mais de 1 field
      $len = count($args);
      for ($i = 0; $i < $count; $i++) {
        $field = $args[$i];

        $this->setField($field);
      }
    } else if (is_string($fields)) {
      // se tiver apenas 1 field e ele for string
      $this->setField($fields);
    } else if (is_array($fields)) {
      // se tiver apenas 1 field e ele for array

      foreach ($fields as $field) {
        // vai em cada um e cria mais um campo
        if (is_array($field)) {
          if (count($field) === 1) {
            // array( 'field' => 'type')
            $this->setField(key($field), current($field));
          } elseif (count($field) === 2) {
            // array( 'field', 'type' )
            $this->setField($field[0], $field[1]);
          } else {
            // desconhecido
            throw new Exception('BDQuery: Tipo de campo não reconhecido (' . var_export($field, true) . ')');
          }
        } else {
          $this->setField($field);
        }
      }
    } else {
      // limpa os fields
      $this->fields = array();
      $this->data = array();
    }
  }

  public function getFields() {
    $fields = array();
    foreach ($this->fields as $field) {
      $fields[] = $field['#raw'];
    }
    return $fields;
  }

  /**
   * Define um campo pra tabela
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @param string $type Tipo de campo (string, int, float ...)
   */
  public function setSpecialField($key) {

    // limpa espaços
    $key = trim($key);

    // define o campo
    $this->sfields[strtolower($key)] = $key;
  }

  /**
   * Apaga um campo que passa para instrução SQL
   * @author Raphael Hardt
   * @param string $key Nome do campo
   */
  public function unsetSpecialField($key) {
    unset($this->sfields[strtolower($key)]);
  }

  /**
   * Define dados para passar pra instrução SQL
   * @author Raphael Hardt
   * @param string $key Nome do campo
   * @param mixed $value Valor do campo
   * @param bool $parseHTML Deixa o HTML do texto codificado
   */
  public function setData($key, $value, $parseHTML = false) {

    /* if (empty($this->fields)) {
      Handler::error('Você não pode definir dado se não tiver definido os campos da tabela');
      } elseif (in_array(strtolower($key), $this->fields)) {
      Handler::error('Erro ao definir dado: campo '.$key.' não existe');
      } else { */

    //if (empty($this->fields)) {
    //$this->setField($key);
    //}

    /* if($parseHTML) {
      $value = $this->kses->Parse($value);
      } */
    $this->data[strtolower($key)] = $value;
    //}
  }

  /**
   * Apaga um campo que passa para instrução SQL
   * @author Raphael Hardt
   * @param string $key Nome do campo
   */
  public function unsetData($key) {
    $this->data[strtolower($key)] = null;
  }

  /**
   * Define dados para passar pra instrução SQL por campo
   * @author Raphael Hardt
   * @param mixed $datas Valores dos campos
   */
  public function setDatas($datas) {
    // se não tiver nenhum campo definido, você não pode setar dados FIX: os campos vão ser setados automatic
    if (empty($this->fields) || !is_array($this->fields)) {
      //BD2::error('Você não pode definir dados se não tiver definido os campos da tabela');
      // 10/03/2013
      // se datas for um array com keys (como um array de result, por ex)
      if (is_array($datas)) {
        $this->setFields(array_keys($datas));
      } else {
        Handler::error('Você não pode definir dados se não tiver definido os campos da tabela');
        return;
      }
    }

    $args = func_get_args();
    $count = count($args);

    // limpa os fields
    $this->data = array();

    if ($count > 1) {

      if (empty($this->fields) || !is_array($this->fields)) {
        throw new Exception('BDQuery: Você não pode definir dados desta forma (lista de parâmetros) se não tiver definido os campos da tabela.');
      }
      $count_fields = count($this->fields);
      if ($count_fields < $count) {
        throw new Exception('BDQuery: O número de dados a serem definidos (' . $count . ') é maior que o número de campos definidos (' . $count_fields . ')');
      }
      // se tiver mais de 1 data
      $len = count($args);
      for ($i = 0; $i < $count; $i++) {
        $data = $args[$i];

        // procura o ultimo key de campo não usado
        reset($this->fields);
        $key = key($this->fields);
        $j = 0;
        while (array_key_exists((string) $key, $this->data) && $j < $count) {
          next($this->fields);
          $key = key($this->fields);
          ++$j; // apenas para evitar loop infinito, embora o throw acima não permita entrar aqui
        }
        $this->data[$key] = $data;
      }
    } else if (is_string($datas)) {
      // se tiver apenas 1 data e ele for string
      // procura o ultimo key de campo não usado

      if (empty($this->fields) || !is_array($this->fields)) {
        throw new Exception('BDQuery: Você não pode definir dados desta forma (parâmetro único) se não tiver definido pelo menos 1 campo da tabela.');
      }

      reset($this->fields);
      $key = key($this->fields);
      $j = 0;
      while (array_key_exists((string) $key, $this->data) && $j < $count) {
        next($this->fields);
        $key = key($this->fields);
        ++$j; // apenas para evitar loop infinito, embora o throw acima não permita entrar aqui
      }

      $this->data[$key] = $datas;
    } else if (is_array($datas)) {
      // se tiver apenas 1 data e ele for array

      foreach ($datas as $key => $data) {
        // se o key for numerico,
        // procura o ultimo key de campo não usado
        if (is_numeric($key)) {

          if (empty($this->fields) || !is_array($this->fields)) {
            throw new Exception('BDQuery: Você não pode definir dados desta forma (array de dados) se não tiver definido os campos da tabela.');
          }
          $count_fields = count($this->fields);
          if ($count_fields < $count) {
            throw new Exception('BDQuery: O número de dados a serem definidos (' . $count . ') é maior que o número de campos definidos (' . $count_fields . ')');
          }

          reset($this->fields);
          $key = key($this->fields);
          $j = 0;
          while (array_key_exists((string) $key, $this->data) && $j < $count) {
            next($this->fields);
            $key = key($this->fields);
            ++$j; // apenas para evitar loop infinito
          }
        }

        // vai em cada um e joga mais um valor
        $this->data[strtolower($key)] = $data;
      }
    }
    // reseta o ponteiro do array de campos (por segurança)
    if (is_array($this->fields)) {
      reset($this->fields);
    }
  }

  /**
   * Retorna dados definidos de uma instrução SQL
   * @author Raphael Hardt
   * @param string $key
   * @return string Dados
   */
  public function getData($key = NULL) {
    if (empty($this->data)) {
      throw new Exception('Não há nenhum dado para retornar');
    }
    if (empty($key)) {
      // se não setar key, devolver tudo
      return $this->data;
    } else {
      // se setar key, mostrar dados daquele campo
      return $this->data[strtolower($key)];
    }
  }

  public function clean() {
    unset($this->sql, $this->bind_values);
  }

  public function setPrimaryKeyName($name) {
    $this->primary_name = strtoupper($name);
  }

  public function getPrimaryKeyName() {
    return $this->primary_name;
  }

  public function buildSQL($instruction) {
    // limpa o ultimo sql feito
    $this->clean();

    $instruction = strtoupper($instruction);
    // busca os valores setados
    $tables = $this->tables;
    $joins = $this->joins;

    $data = $this->data;
    $fields = $this->fields;
    $sfields = $this->sfields;

    $start = $this->start;
    $limit = $this->limit;

    $search = $this->search;
    $search_fields = $this->search_fields;
    $order = $this->order;
    $group = $this->group;
    $merges_on = $this->merges_on;
    $constraints = $this->constraints;
    $primary = $this->primary;
    $exception = $this->exception;
    $exception_values = $this->exception_values;

    // valores adicionais
    $values_w = array();
    if (empty($data) || !is_array($data))
      $data = array();

    // primeiro valida os dados com os campos (8/4/13)
    if (!empty($fields)) {
      $data_tmp = $fields_tmp = array();
      foreach ($data as $key => $val) {
        if (array_key_exists($key, $fields)) {
          $data_tmp[] = $val;
          $fields_tmp[] = $fields[$key];
        }
      }
      $data = $data_tmp;
      $fields = $fields_tmp;
    } else {
      // se não tiver campos definidos, verifica se é uma instrução que precisa de campos
      if ($instruction === 'UPDATE' || $instruction === 'INSERT') {
        throw new Exception('BDQuery: para instruções UPDATE ou INSERT, definir campos é obrigatório');
      }
    }

    // variaveis auxiliares
    $e = ''; //exception
    $f = $f_s = $f_u = $f_mu = $f_mi = $f_ms = array(); //fields
    $b = array(); //binds
    $v = array(); //values
    $t = $j = array(); //tables/joins
    $g = $o = $l = ''; //groups/orders/limit
    $m = array(); //merges
    // variaveis auxiliares para merge on
    $data_merge_upd = array();
    $data_merge_ins = array();

    // montagem do where
    if (!empty($primary)) {
      // se tiver primary, o where nem é montado

      $e = ' WHERE ' . $this->primary_name . ' = :i LIMIT 1';
      $values_w[] = $primary;
    } else {
      if (!empty($exception)) {

        // se tiver exception, o where nem é montado
        $e = $exception;
        $values_w = $exception_values;
      } else {
        // se não tiver primary nem exception
        // começa a montar o where
        $e = ' WHERE 1=1 ';

        $params_w = array();

        // verifica as contraints
        if (!empty($constraints)) {
          $tmp = array();
          foreach ($constraints as $constraint) {

            if ($constraint['#type'] == 'IN' || $constraint['#type'] == 'NOT IN') {
              $inner_tmp = array();
              foreach ($constraint['#values'] as $contraint_value) {
                $inner_tmp[] = ':s';
                $values_w[] = $contraint_value;
              }
              $tmp[] = $constraint['#key'] . ' ' . $constraint['#type'] . ' (' . implode(',', $inner_tmp) . ')';
            } elseif ($constraint['#type'] == 'BETWEEN') {

              $tmp[] = '(' . $constraint['#key'] . ' BETWEEN :' . $constraint['#content_type'] . ' AND :' . $constraint['#content_type'] . ')';
              $values_w[] = $constraint['#value1'];
              $values_w[] = $constraint['#value2'];
            } else {
              $tmp[] = $constraint['#key'] . ' ' . $constraint['#type'] . ' :' . $constraint['#content_type'] . '';
              $values_w[] = $constraint['#value'];
            }
          }
          $params_w[] = '( ' . implode(' AND ', $tmp) . ' ) ';
        }

        // verifica a busca
        if (!empty($search)) {
          $tmp = array();
          foreach ($search as $word) {
            $aux = array();
            foreach ($search_fields as $field) {
              $cp_word = $word;
              if ($field['#like']) {
                $cp_word = '%' . $cp_word . '%';
              }
              $aux[] = $field['#key'] . ' ' . $field['#type'] . ' :' . $field['#content'] . '';
              $values_w[] = $cp_word;
            }
            $tmp[] = '(' . implode(' OR ', $aux) . ')';
          }
          $params_w[] = '(' . implode(' AND ', $tmp) . ')';
        }

        // monta o where inteiro
        if (!empty($params_w)) {
          $e .= ' AND ' . implode(' AND ', $params_w);
        }
      }

      // monta o group by
      if (!empty($group)) {
        $g .= ' GROUP BY ' . implode(', ', $group);
      }

      // monta o order by
      if (!empty($order)) {
        $tmp = array();
        foreach ($order as $ord) {
          $tmp[] = $ord['#field'] . ' ' . $ord['#direction'];
        }
        $o .= ' ORDER BY ' . implode(', ', $tmp);
        unset($tmp);
      }

      if (!empty($start) || !empty($limit)) {
        $l = ' LIMIT ';
        // verifica o start
        if (!empty($start)) {
          $l .= $start;
          if (!empty($limit)) {
            $l .= ', ';
          }
        } else {
          $start = 0;
        }
        // verifica o limit
        if (!empty($limit)) {
          $l .= ($start - $limit);
        }
      }
    }

    // pega os campos definidos
    if (!empty($fields)) {

      foreach ($fields as $field) {
        // normaliza o nome da tabela
        if (!empty($field['#table']))
          $field['#table'] .= '.';

        // fields para selects e inserts/updates/deletes
        $f[] = $field['#table'] . $field['#field'];
        $f_s[] = $field['#raw'] . ( $field['#raw'] != $field['#alias'] ? ' AS ' . $field['#alias'] : '');
        $f_u[] = $field['#table'] . $field['#field'] . ' = :' . $field['#type'];

        // para insert
        $b[] = ':' . $field['#type'];
      }
    } else {
      // se não tiver campos definidos
      $f = $f_s = array('*');
    }

    // só para caso de updates
    if (!empty($sfields)) {
      foreach ($sfields as $field) {
        $f_u[] = $field;
        $f_mu[] = $field;
      }
    }

    // pega as tabelas definidas
    if (!empty($tables)) {
      foreach ($tables as $table) {
        if ($table['#name'] != $table['#alias'])
          $t[] = $table['#name'] . ' ' . $table['#alias'];
        else
          $t[] = $table['#name'];
      }
    } else {
      // se nenhuma tabela for setada, usar DUAL
      $tables = $t = array('DUAL');
    }

    // pega os joins definidos
    if (!empty($joins)) {
      foreach ($joins as $join) {
        $j[] = $join['#join'] . ' JOIN ' . $join['#name'] . ' ' . $join['#alias'] . ' ON (' . $join['#on'] . ')';
      }
    }

    // string de sql
    $sql = '';

    // verifica a instrução para montar o cabeçalho do sql

    switch ($instruction) {
      case 'SELECT':
        // limpa o $data, pois os binds vão apenas no where, e não nos fields
        $data = array();

        $sql = 'SELECT ' .
                implode(', ', $f_s) . // fields
                ' FROM ' .
                implode(',', $t) . // tabelas
                ' ' .
                implode(' ', $j) . // joins
                ' ' .
                $e . // where
                $g . // group by
                $o . // order by
                $l; // limit


        break;

      case 'INSERT':
        // pega apenas a primeira tabela, pois insert é uma instrução pra 1 tabela só
        $table = reset($t);

        $sql = 'INSERT INTO ' .
                $table . // tabela
                ( ($f[0] == '*') ?
                        '' :
                        ' (' . implode(',', $f) . ') ' // fields
                ) .
                ' VALUES ' .
                ' (' . implode(', ', $b) . ') ' . // values (binds)
                //$e. // where
                $l; // limit

        break;

      case 'UPDATE':
        // pega apenas a primeira tabela, pois insert é uma instrução pra 1 tabela só
        $table = reset($t);

        $sql = 'UPDATE ' .
                $table . // tabela
                ' SET ' .
                ' ' . implode(', ', $f_u) . ' ' . // fields
                $e . // where
                $l; // limit

        break;

      case 'DELETE':
        // limpa o $data, pois os binds vão apenas no where, e não nos fields
        $data = array();

        // pega apenas a primeira tabela, pois insert é uma instrução pra 1 tabela só
        $table = reset($t);

        $sql = 'DELETE FROM ' .
                $table . // tabela
                ' ' .
                $e . // where
                $l; // limit

        break;

      case 'REPLACE':
        // pega apenas a primeira tabela, pois insert é uma instrução pra 1 tabela só
        $table = reset($tables);

        $sql = 'MERGE INTO ' .
                $table['#name'] . ' target ' . // tabela
                ' USING ' .
                '(SELECT ' . implode(', ', $f_ms) . ' FROM DUAL) source ' . // select
                ' ON ' .
                ' (' . implode(' OR ', $m) . ') ' . // merges on
                ' WHEN MATCHED THEN ' .
                ' UPDATE SET ' . // update
                ' ' . implode(', ', $f_mu) . ' ' .
                ' WHEN NOT MATCHED THEN ' .
                ' INSERT ' . // insert
                ' (' . implode(', ', $f_mi) . ') ' .
                ' VALUES ' .
                ' (' . implode(', ', $b) . ')'; // values (binds)
        // adiciona os valores novamente pra o bind
        $values_w = array_merge($values_w, $data_merge_upd);
        $values_w = array_merge($values_w, $data_merge_ins);

        break;

      default:
        Handler::error('Instrução ' . $instruction . ' desconhecida');
        $sql = '';
    }

    // define todos os valores
    $v = array_merge($data, $values_w);

    // captura os binds do SQL
    $bind_values = $v;
    $sql = $this->_normalizeBinds($sql, $bind_values);

    // salva o sql
    $this->sql = $sql;
    $this->bind_values = $bind_values;
    
    // colocar log
    
    return true;
  }

  protected function _normalizeBinds($sql, &$values) {
    // salva os valores na função   	
    self::_replaceBinds('save', $values);

    // busca pelos binds no sql e substitui por um nome unico para cada bind
    $sql = preg_replace_callback('/:([dtsinfm:]t?)/', __CLASS__ . '::_replaceBinds', $sql);

    // retorna os valores normalizados
    $values = self::_replaceBinds('catch');

    return $sql;
  }

  protected static function _replaceBinds($match, $vals = null) {
    static $counter = 1;
    static $values = array(), $values_return = array();

    if ($match === 'save') {
      $counter = 1;
      $values_return = array();
      $values = $vals;
      return;
    } elseif ($match === 'catch') {
      return $values_return;
    }

    // extrai o valor a ser manipulado, 
    $value = array_shift($values);

    $field = 'field' . $counter;

    switch ($match[1]) {
      case 'dt':
        $return = 'TO_DATE(?, \'YYYY-MM-DD HH24:MI:SS\')';
        break;
      case 'd':
        $return = 'TO_DATE(?, \'YYYY-MM-DD\')';
        break;
      case 't':
        $return = 'TO_DATE(?, \'HH24:MI:SS\')';
        break;
      case 's':
        $value = (string) $value;
        $return = '?';
        break;
      case 'i':
      case 'n':
        $value = is_numeric($value) && !preg_match('/x/i', $value) ? $value : '0';
        if ($value > PHP_INT_SIZE) {
          $precision = ini_get('precision');
          @ini_set('precision', 16);
          $value = sprintf('%.0f', $value);
          @ini_set('precision', $precision);
        } else {
          $value = (int) $value;
        }
        $return = '?';
        break;
      case 'f':
        $value = (float) $value;
      case 'm':
        $value = round($value, 2);
        $return = '?';
        break;
      case ':':
        array_unshift($values, $value);
        return ':';
      default:
        $return = '?';
    }

    if (!array_key_exists($field, $values_return))
      $values_return[$field] = $value;
    else {
      // caso não esteja contido nos valores de retorno, devolve o campo para
      // outro field usar
      array_unshift($values, $value);
    }
    ++$counter;

    return $return;
  }

}