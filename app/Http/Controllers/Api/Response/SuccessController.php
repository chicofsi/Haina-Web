<?php

/*
author  : adisurizal
email   : adisurizal.cyber@outlook.com
*/

namespace App\Http\Controllers\Api\Response;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuccessController extends Controller {

  // get
  public function success_200($message, $data) {
    return [
      'success' => [
          'code' => 200,
          'detail' => 'ok',
          'message' => $message,
          'data' => $data
      ]
    ];
  }

  // create, update, delete
  public function success_201($message, $data) {
    return [
      'success' => [
          'code' => 201,
          'detail' => 'ok',
          'message' => $message,
          'data' => $data
      ]
    ];
  }

  // permintaan telah berhasil masuk ke server tapi server butuh waktu lama untuk memproses
  public function success_202($message, $data) {
    return [
      'success' => [
          'code' => 202,
          'detail' => 'accepted',
          'message' => $message,
          'data' => $data
      ]
    ];
  }

  // server berhasil memproses permintaan tapi konten tidak tersedia di sumber
  public function success_204($message, $data) {
    return [
      'success' => [
          'code' => 204,
          'detail' => 'no_content',
          'message' => $message,
          'data' => $data
      ]
    ];
  }

}
