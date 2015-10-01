<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A super-simple codeigniter model with CRUD functions and callbacks support
 */

class MY_Model extends CI_Model {

  /* --------------------------------------------------------------
   * Properties
   * ------------------------------------------------------------ */
  protected $_database;
  protected $_table;
  protected $_primary_key;

  protected $before_create = array();
  protected $after_create  = array();
  protected $before_update = array();
  protected $after_update  = array();
  protected $before_get    = array();
  protected $after_get     = array();
  protected $before_delete = array();
  protected $after_delete  = array();


  /* --------------------------------------------------------------
   * Init
   * ------------------------------------------------------------ */
  public function __construct()
  {
    parent::__construct();
    $this->_database = $this->db;
  }

  /* --------------------------------------------------------------
   * CRUD methods
   * ------------------------------------------------------------ */
  public function get($primary_value)
  {
    return $this->get_by($this->_primary_key, $primary_value);
  }

  public function get_by()
  {
    $where = func_get_args();

    $this->_set_where($where);

    $this->trigger('before_get');

    $row = $this->_database->get($this->_table)->row();

    $row = $this->trigger('after_get', $row);

    return $row;
  }

  public function get_many($values)
  {
    $this->_database->where_in($this->_primary_key, $values);

    return $this->get_all();
  }

  public function get_many_by()
  {
    $where = func_get_args();
    
    $this->_set_where($where);

    return $this->get_all();
  }

  public function get_all()
  {
    $this->trigger('before_get');

    $result = $this->_database->get($this->_table)->result();

    foreach($result as $key => &$row)
    {
      $row = $this->trigger('after_get', $row,($key == count($result) - 1));
    }

    return $result;
  }

  public function insert($data)
  {
    if($data !== FALSE)
    {
      $data = $this->trigger('before_create', $data);

      $this->_database->insert($this->_table, $data);
      $insert_id = $this->_database->insert_id();

      $this->trigger('after_create', $insert_id);

      return $insert_id;
    }
    else
    {
      return FALSE;
    }
  }

  public function update($data, $primary_value = FALSE)
  {
    if( ! $primary_value) { $primary_value = $data->{$this->_primary_key}; }

    $data = $this->trigger('before_update', $data);

    if($data !== FALSE)
    {
      $result = $this->_database
                     ->where($this->_primary_key, $primary_value)
                     ->set($data)
                     ->update($this->_table);

      $this->trigger('after_update', array($data, $result));

      return $result;
    }
    else
    {
      return FALSE;
    }
  }

  public function delete($id)
  {
    $this->trigger('before_delete', $id);

    $this->_database->where($this->_primary_key, $id);

    $result = $this->_database->delete($this->_table);

    $this->trigger('after_delete', $result);

    return $result;
  }

  /* --------------------------------------------------------------
   * Codeigniter query builder wrapper
   * ------------------------------------------------------------ */
  public function order_by($criteria, $order = 'ASC')
  {
    if(is_array($criteria))
    {
      foreach($criteria as $key => $value) { $this->_database->order_by($key, $value); }
    }
    else
    {
      $this->_database->order_by($criteria, $order);
    }

    return $this;
  }

  public function limit($limit, $offset = 0)
  {
    $this->_database->limit($limit, $offset);

    return $this;
  }

  /* --------------------------------------------------------------
   * Custom methods
   * ------------------------------------------------------------ */
  protected function _set_where($params)
  {
    if(count($params) == 1 && is_array($params[0]))
    {
      foreach($params[0] as $field => $filter)
      {
        if(is_array($filter))
        {
          $this->_database->where_in($field, $filter);
        }
        else
        {
          if(is_int($field))
          {
            $this->_database->where($filter);
          }
          else
          {
            $this->_database->where($field, $filter);
          }
        }
      }
    } 
    else if(count($params) == 1)
    {
      $this->_database->where($params[0]);
    }
    else if(count($params) == 2)
    {
      if(is_array($params[1]))
      {
        $this->_database->where_in($params[0], $params[1]);  
      }
      else
      {
        $this->_database->where($params[0], $params[1]);
      }
    }
    else if(count($params) == 3)
    {
      $this->_database->where($params[0], $params[1], $params[2]);
    }
    else
    {
      if(is_array($params[1]))
      {
        $this->_database->where_in($params[0], $params[1]);  
      }
      else
      {
        $this->_database->where($params[0], $params[1]);
      }
    }
  }

  /* --------------------------------------------------------------
   * Trigger method for callbacks
   * ------------------------------------------------------------ */
  public function trigger($event, $data = FALSE, $last = TRUE)
  {
    if(isset($this->$event) && is_array($this->$event))
    {
      foreach($this->$event as $method)
      {
        $data = call_user_func_array(array($this, $method), array($data, $last));
      }
    }

    return $data;
  }

  /* --------------------------------------------------------------
   * Custom callbacks
   * ------------------------------------------------------------ */
  public function created_at($row)
  {
    $row->created_at = date('Y-m-d H:i:s');

    return $row;
  }

  public function updated_at($row)
  {
    $row->updated_at = date('Y-m-d H:i:s');

    return $row;
  }

}
