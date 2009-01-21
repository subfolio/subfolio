<?php  

class Auth {
  var $config_name = "auth";
  var $users;
  var $groups;
  
  public function __construct($config_name='auth') {
    $this->session = Session::instance();
    $this->config = Kohana::config($config_name);
    $this->config_name = $config_name;
        
    $user_file  = Kohana::config('filebrowser.users_yaml_file');
    $group_file = Kohana::config('filebrowser.groups_yaml_file');
    
    $array = Spyc::YAMLLoad($user_file);
    $this->users = $array['users'];

    $array = Spyc::YAMLLoad($group_file);
    $this->groups = $array['groups'];
  }
  
	public static function instance($config_name='auth') {
    static $instance;
    // Load the Authlite instance
    empty($instance) and $instance = new Auth('auth');
    return $instance;
	}
  
	public function in_group($user, $groupname) {
    if (isset($this->groups[$groupname])) {
      if (in_array($user->name, $this->groups[$groupname])) {
          return true;
      }
    }
    
    return false;
	}
	
  public function logged_in() {
    // Get the user from the session
    $user = $this->session->get($this->config['session_key']);
    
    $status = (gettype($user) == "object") ? true : false;

    // Get the user from the cookie
    if ($status == false) {
      $token = cookie::get("auth_{$this->config_name}_autologin");

      if (is_string($token)) {
        $cookie_vars = explode(":", $token);
        $username = $cookie_vars[0];
        $hash = $this->hash($username.$this->config['salt']);

        if ($hash == $cookie_vars[1]) {
          $user = new User($cookie_vars[0], $this->users[$cookie_vars[0]]);
          $status = true;
          $this->session->set($this->config['session_key'], $user);
        }
      }
    }
 
    if ($status == true) {
      return $user;
    }
    
    return false;
  }	

  public function get_user() {
    if ($this->logged_in()) {
      return $this->session->get($this->config['session_key']);
    }
 
    return false;
  }


  public function login($username, $password, $remember = false) {
    if (empty($password)) {
      return false;
    }
    
    $user = new User($username, $this->users[$username]);
    
    if ($user->password === $password) {
      $this->session->set($this->config['session_key'], $user);
      
      if ($remember == true) {
        $token = $user->name.":".$this->hash($user->name.$this->config['salt']);
        cookie::set("auth_{$this->config_name}_autologin", $token, $this->config['lifetime']);
      }
      
      return $user;
    } else {
      return false;
    }
  }
	
  public function logout($destroy = false) {
    if (cookie::get("auth_{$this->config_name}_autologin")) {
      cookie::delete("auth_{$this->config_name}_autologin");
    }
    
    if ($destroy === true) {
      // Destroy the session completely
      Session::instance()->destroy();
    } else {
      // Remove the user from the session
      $this->session->delete($this->config['session_key']);
 
      // Regenerate session_id
      $this->session->regenerate();
    }
 
    return ! $this->logged_in();
  }
  
  protected function hash($str) {
    return md5($str);
  }  
  
}

?>