<?php

/*
author  : adisurizal
email   : adisurizal.cyber@outlook.com
*/

namespace App\Http\Controllers\Api\Response;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ErrorController extends Controller {

  // client mengirimkan permintaan yang salah, misal parameter yang tidak sesuai
  public function error_400($message) {
    return [
      'error' => [
          'code' => 400,
          'detail' => 'bad_request',
          'message' => $message,
      ]
    ];
  }

  // client mengakses jalur/konten yang membutuhkan otentikasi
  public function error_401($message) {
    return [
      'error' => [
          'code' => 401,
          'detail' => 'unauthorized',
          'message' => $message,
      ]
    ];
  }

  // client mengakses jalur/konten yang dilarang
  public function error_403($message) {
    return [
      'error' => [
          'code' => 403,
          'detail' => 'forbidden',
          'message' => $message,
      ]
    ];
  }

  // client mengakses jalur/konten yang tidak pernah ada sebelumnya
  public function error_404($message) {
    return [
      'error' => [
          'code' => 404,
          'detail' => 'not_found',
          'message' => $message,
      ]
    ];
  }

  // client menggunakan metode yang tidak diizinkan
  public function error_405($message) {
    return [
      'error' => [
          'code' => 405,
          'detail' => 'method_not_allowed',
          'message' => $message,
      ]
    ];
  }

  // terdapat kesalahan pada sisi server
  public function error_500($message) {
    return [
      'error' => [
          'code' => 500,
          'detail' => 'internal_server_error',
          'message' => $message,
      ]
    ];
  }

  // server tidak dapat dijangkau
  public function error_503($message) {
    return [
      'error' => [
          'code' => 503,
          'detail' => 'service_unavailable',
          'message' => $message,
      ]
    ];
  }

}
