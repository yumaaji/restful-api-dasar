<?php 

class M_auth extends CI_Model{

  private $_user = 'user';

  public function doLogin($username, $encryptPassword){
    $query = $this->db->get_where($this->_user, ['username' => $username, 'password' => $encryptPassword]);
    if($query->num_rows() == 1)
      return $query->result();
    else
      return false;
  }
  
}
