<?php
namespace app\prize\controller;
use think\Controller;

class Common extends Controller
{
  public function initialize()
  {
  	if(method_exists($this,'_initialize')){
  	  $this->_initialize();
  	}
  }

  /**
   * 根据给出的数列，得到组合集（无顺序，如 123和321为一组）
   * @param array   $a 数列
   * @param int     $m 组合个数
   * @return array  组合集
   */
  public function combination ($a, $m)
  {
    $r = array();
    $n = count($a);
    if ($m <= 0 || $m > $n) {
      return $r;
    }
    for ($i=0; $i<$n; $i++) {
      $t = array($a[$i]);
      if ($m == 1) {
        $r[] = $t;
      } else {
        $b = array_slice($a, $i+1);
        $c = $this->combination($b, $m-1);
        foreach ($c as $v) {
            $r[] = array_merge($t, $v);
        }
      }
    }
    return $r;
  }

  /**
   * 根据给出的数列，得到排列集（有顺序，如 123和321为两组）
   * @param array   $a 数列
   * @param int     $m 排列个数
   * @return array  排列集
   */
 public function arrangement($a, $m)
 {
      $r = array();
      $n = count($a);
      if ($m <= 0 || $m > $n) {
          return $r;
      }
      for ($i=0; $i<$n; $i++) {
          $b = $a;
          $t = array_splice($b, $i, 1);
          if ($m == 1) {
              $r[] = $t;
          } else {
              $c = $this->arrangement($b, $m-1);
              foreach ($c as $v) {
                  $r[] = array_merge($t, $v);
              }
          }
      }
      return $r;
  }

}
