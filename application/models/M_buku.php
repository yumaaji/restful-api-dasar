<?php 

class M_buku extends CI_Model{

  private $_tbl_buku = 'buku'; //'buku' is name related on table name

  public function getData($id = null){
    if($id == null){
      return $this->db->query("SELECT b.*, k.nama_kategori FROM buku b
                               JOIN kategori k ON b.kategori_id = k.id")->result_array(); 
                               //JOIN "categori name" be alias for "categori id"
    }
    else{
      return $this->db->query("SELECT b.*, k.nama_kategori FROM buku b
                               JOIN kategori k ON b.kategori_id = k.id WHERE b.id = '$id'")->result_array();
                               //JOIN "categori name" be alias for "categori id"
                               //Select data only based on ID
    }
    // if($id == null){
    //   return $this->db->get($this->_tbl_buku)->result_array();
    // }else{
    //   return $this->db->get_where($this->_tbl_buku,['id'=>$id])->result_array();
    // }
  }

  public function insertData($data){
    $this->db->insert($this->_tbl_buku, $data); //add new data
    return $this->db->affected_rows();
  }
 
  public function updateData($data, $id){
    $this->db->update($this->_tbl_buku, $data,['id' => $id]); //update data based on ID choosen
    return $this->db->affected_rows();
  }

  public function deleteData($id){
    $this->db->delete($this->_tbl_buku, ['id' => $id]); //delete data based on ID choosen
    return $this->db->affected_rows();
  }

  public function importBuku($data){
    $this->db->insert_batch($this->_tbl_buku, $data);
    return $this->db->affected_rows();
  }
}