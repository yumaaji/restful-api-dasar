<?php

use chriskacerguis\RestServer\RestController;

class Buku extends RestController{

  function __construct(){
    parent::__construct();
    $this->load->model('M_buku','buku');
  }

  public function index_get(){
    $id = $this->get('id_buku');
    // looking for data based on ID or without ID
    $data_buku = $this->buku->getData($id);
    // response
    if($data_buku){
      $this->response([
        'status' => 'true',
        'message' => 'Berhasil mendapatkan data buku',
        'result' => $data_buku
      ], self::HTTP_OK);
    } 
    else {
      $this->response([
        'status' => 'false',
        'message' => 'Gagal mendapatkan data buku'
      ], self::HTTP_BAD_REQUEST);
    }
  }

  public function index_post(){
    // validation check with function "_validationCheck()"
    // function in bottom
    if($this->_validationCheck() == false){
      $this->response([
        'status' => false,
        'message' => strip_tags(validation_errors()) //"strip_tags" konvert to array
      ], self::HTTP_BAD_REQUEST);
    } 
    else {
      $file = $_FILES['cover'];
      $path = "uploads/buku/"; //set path for image storage
      if(!is_dir($path)){
        mkdir($path, 0777, true);
      }

      $path_file = ""; //default path name
      if (!empty($file['name'])){
        $config['upload_path'] = './'.$path; //path folder park
        $config['allowed_types'] = 'jpg|png|jpeg'; //type file
        $config['file_name'] = time(); //rename file be (seconds)
        $config['max_size'] = 1024; //max size is 1MB
        $this->upload->initialize($config); //konfigurasi upload
        if($this->upload->do_upload('cover')){
          // get file that success to upload
          $uploadData = $this->upload->data(); //data uploaded
          $path_file = './' . $path . $uploadData['file_name']; //path folder + file name
        }
      }

      $data = [
        'judul' => $this->post('judul'),
        'penulis' => $this->post('penulis'),
        'tahun' => $this->post('tahun'),
        'penerbit' => $this->post('penerbit'),
        'cover' => $path_file, //path folder + file name
        'stock' => $this->post('stock'),
        'harga_beli' => $this->post('harga_beli'),
        'harga_jual' => $this->post('harga_jual'),
        'kategori_id' => $this->post('kategori_id'),
      ];
      
      $insert = $this->buku->insertData($data); // if there is row changed (in this case row be added)
      if($insert > 0){
        $this->response([
          'status' => 'true',
          'message' => 'Berhasil menambahkan data buku'
        ], self::HTTP_CREATED);
      }
      else{
        $this->response([
          'status' => 'false',
          'message' => 'Data gagal ditambahkan'
        ], self::HTTP_BAD_REQUEST);
      }
    }
  }

  public function index_put(){
    // to make sure ID Buku had sent
    $id = $this->input->post('id_buku');
    $dataBuku = $this->buku->getData($id);
    
    // independent validation for ID Buku
    $this->form_validation->set_rules(
      'id_buku',
      'Id Buku',
      'required|numeric',
      array(
        'required' => '{field} wajib diisi',
        'numeric' => '{field} tidak valid',
      )
    );
    
    $file = $_FILES['cover'];
    $path = "uploads/buku/";
    if(!is_dir($path)){
      mkdir($path, 0777, true);
    }

    $path_file = ""; //default value for final path name
    if (!empty($file['name'])){
      $config['upload_path'] = './'.$path; //path folder park
      $config['allowed_types'] = 'jpg|png|jpeg'; //type file
      $config['file_name'] = time(); //rename file be (seconds)
      $config['max_size'] = 1024; //max size is 1MB
      $this->upload->initialize($config); //upload configuration
      if($this->upload->do_upload('cover')){
        // delete old cover from storage
        @unlink('./' . $dataBuku[0]['cover']);
        // get file that success to upload
        $uploadData = $this->upload->data(); //data uploaded
        $path_file = './' . $path . $uploadData['file_name']; //path folder + file name
      }
    }
    // validation check with function "_validationCheck()"
    // function in bottom
    if($this->_validationCheck() == false || $this->form_validation->run() == false){
      $this->response([
        'status' => false,
        'message' => strip_tags(validation_errors()) //strip_tags konvensi ke array
      ], self::HTTP_BAD_REQUEST);
    } else {
      // get ID Buku from 'id_buku'
        $data['judul'] = $this->input->post('judul');
        $data['penulis'] = $this->input->post('penulis');
        $data['tahun'] = $this->input->post('tahun');
        $data['penerbit'] = $this->input->post('penerbit');
        $data['cover'] = $path_file;
        $data['stock'] = $this->input->post('stock');
        $data['harga_beli'] = $this->input->post('harga_beli');
        $data['harga_jual'] = $this->input->post('harga_jual');
        $data['kategori_id'] = $this->input->post('kategori_id');

      // after escape from validation
      $updated = $this->buku->updateData($data, $id); //update data
      if($updated > 0){
        $this->response([
          'status' => 'true',
          'message' => 'Berhasil memperbarui data buku'
        ], self::HTTP_OK);
      }
      else{
        $this->response([
          'status' => 'false',
          'message' => 'Data gagal diperbarui'
        ], self::HTTP_BAD_REQUEST);
      }
    }
  }

  public function index_delete(){
    $id = $this->delete('id_buku'); //get buku ID based on field (id_buku)
    if($id === null){
      $this->response([
        'status' => false,
        'message' => 'Masukkan id buku'
      ], self::HTTP_NOT_FOUND);
    } 
    else {
      $dataBuku = $this->buku->getData($id); //get data Buku based on its ID
      @unlink($dataBuku[0]['cover']); //delete image in storage
      $deleted = $this->buku->deleteData($id); //delete data in database
      if($deleted > 0){
        $this->response([
          'status' => true,
          'message' => 'Berhasil menghapus data'
        ], self::HTTP_OK);
      }
      else{
        $this->response([
          'status' => false,
          'message' => 'Gagal menghapus data'
        ], self::HTTP_BAD_REQUEST);
      }
    }
  }

  public function _validationCheck(){
    $this->form_validation->set_rules(
      'judul',
      'Judul Buku', //nama field
      'required',
      array(
        'required' => '{field} wajib diisi',
      )
    );

    $this->form_validation->set_rules(
      'penulis',
      'Penulis',
      'required',
      array(
        'required' => '{field} wajib diisi',
      )
    );

    $this->form_validation->set_rules(
      'tahun',
      'Tahun',
      'required|numeric',
      array(
        'required' => '{field} wajib diisi',
        'numeric' => '{field} tidak valid',
      )
    );

    $this->form_validation->set_rules(
      'penerbit',
      'Penerbit Buku',
      'required',
      array(
        'required' => '{field} wajib diisi',
      )
    );

    // $this->form_validation->set_rules(
    //   'cover',
    //   'Cover buku',
    //   'required',
    //   array(
    //     'required' => '{field} wajib diisi',
    //   )
    // );

    $this->form_validation->set_rules(
      'stock',
      'Stock buku',
      'required|numeric',
      array(
        'required' => '{field} wajib diisi',
        'numeric' => '{field} tidak valid',
      )
    );

    $this->form_validation->set_rules(
      'harga_beli',
      'Harga Beli Buku',
      'required|numeric',
      array(
        'required' => '{field} wajib diisi',
        'numeric' => '{field} tidak valid',
      )
    );

    $this->form_validation->set_rules(
      'harga_jual',
      'Harga Jual Buku',
      'required|numeric',
      array(
        'required' => '{field} wajib diisi',
        'numeric' => '{field} tidak valid',
      )
    );

    $this->form_validation->set_rules(
      'kategori_id',
      'Kategori Buku',
      'required|numeric',
      array(
        'required' => '{field} wajib diisi',
        'numeric' => '{field} tidak valid',
      )
    );

    return $this->form_validation->run();
  }
}
