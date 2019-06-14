<?php
namespace app\home\controller;
use think\Controller;
use think\Db;

class Error extends Controller
{
  public function index(){
    echo $this->fetch('index');
  }
}
