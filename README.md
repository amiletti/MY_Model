# NotOrm

NotOrm is based on wonderful work of [Jamie Rumbelow](https://github.com/jamierumbelow) [codeigniter-base-model](https://github.com/jamierumbelow/codeigniter-base-model).
Usually I don't need all the features of codeigniter-base-model, then I remove some features form his model. Basically I only keep the basic crud operation and the trigger for after/before event callbacks. I removed instead all validation and relationship method and the chance to pass a variable to the callbacks.

# Usage sample

**user_ model.php** // model file
```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model {

  public $_table                = 'users'; // mandatory
  public $_primary_key          = 'user_id'; // mandatory

  public $before_create = array('created_at', 'updated_at', 'hash_password');
  public $before_update = array('updated_at');
  public $after_get     = array('init');

  function init($row)
  {
    if(isset($row->user_id))
    {
      $row->roles = $this->roles_model->get_many_by(array('user_id' => $row->user_id));
    }

    return $row;
  }

  function hash_password($row)
  {
    if(isset($row->password))
    {
      $row->password = password_hash($row->password, PASSWORD_DEFAULT);
    }

    return $row;
  }

}
```

**users.php** // controller file
```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->model('db/user_model');
  }

  public function index()
  {
    $users = $this->user_model->get_all(); // retrieve all user, return ampty array

    $user = new stdClass(); // create new user object
    $user->username = 'Vin Diesel';
    $user->password = 'secret';
    $user_id = $this->user_model->insert($user); // insert and retain user_id

    $user = $this->user_model->get($user_id); // get Vin Diesel
    $user->username = 'Vincenzo Gasolio'; // change username
    $this->user_model->update($user); // update the username

    $user = new stdClass(); // create new user object
    $user->username = 'Guglielmo Cancelli'; //
    $this->user_model->update($user, $user_id); // update the username (user_id is not in $user object)

    $where = ''; // where statements based on CI active record
    $user = $this->user_model->get_by($where); // get one record by conditions
    $users = $this->user_model->get_many(array(1, 2, 3, '...')); // get many records by primary keys
    $users = $this->user_model->get_many_by($where); // get many records by conditions

    $this->user->delete($user_id); // delete user
  }

}
```
