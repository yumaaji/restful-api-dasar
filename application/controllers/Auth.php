<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use chriskacerguis\RestServer\RestController;

class Auth extends RestController{

  private $key;
  function __construct(){ 
    parent::__construct();
    $this->load->model('M_auth', 'user');
    $this->key = (string)12345678; //convert int to string
  }

  public function index_post(){
    $date = new DateTime();
    $username = $this->post('username');
    $password = $this->post('password');
    $encryptPassword = hash('sha512', $password . $this->key); //hash password
    $dataUser = $this->user->doLogin($username, $encryptPassword); //try login

    if($dataUser){
      $payload = [
        'id' => $dataUser[0]->id,
        'name' => $dataUser[0]->name,
        'username' => $dataUser[0]->username,
        'iat' => $date->getTimestamp(),
        'exp' => $date->getTimestamp() + (60*3),
      ];

      $token = JWT::encode($payload, $this->key, 'HS256');
      var_dump($token);
      exit;
      if($token){
        $this->response([
          'status' => true,
          'message' => 'Login Success',
          'data' => $dataUser,
          'token' => $token
        ], self::HTTP_OK);
      }else{
        $this->response([
          'status' => false,
          'message' => 'Login Failed',
        ], self::HTTP_FORBIDDEN);
      }
    }
  }

  protected function cekToken(){
    $JWT = $this->input->get_request_header('Authorization');
    try{
      JWT::decode($JWT, new Key($this->key, 'HS256')); //check valid JWT
    }catch(Exception $e){
      $this->response([
        'status' => false,
        'message' => 'Invalid token',
      ], self::HTTP_UNAUTHORIZED);
    }
  }
}