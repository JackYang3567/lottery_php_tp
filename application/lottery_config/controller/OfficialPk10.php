<?php
namespace app\lottery_config\controller;

class OfficialPk10
{
  public function index(){
    $data = [
        'official_dwd' => [
            'name' => '定位胆',
            'switch' => 1,
            'items' => [
                'official_dwd_fs' => [
                    'name' => '复式',
                    'odds' => 10,
                    'switch' => 1
                ]
            ]
        ],
        'official_cqs' => [
            'name' => '猜前三',
            'switch' => 1,
            'items' => [
                'official_cqs_fs' => [
                    'name' => '复式',
                    'odds' => 10,
                    'switch' => 1
                ],
                'official_cqs_ds' => [
                    'name' => '单式',
                    'odds' => 10,
                    'switch' => 1
                ]
            ]
        ],
        'official_cqe' => [
            'name' => '猜前二',
            'switch' => 1,
            'items' => [
                'official_cqe_fs' => [
                    'name' => '复式',
                    'odds' => 10,
                    'switch' => 1
                ],
                'official_cqe_ds' => [
                    'name' => '单式',
                    'odds' => 10,
                    'switch' => 1
                ]
            ]
        ],
        'official_cgj' => [
            'name' => '猜冠军',
            'switch' => 1,
            'items' => [
                'official_cgj_fs' => [
                    'name' => '复式',
                    'odds' => 10,
                    'switch' => 1
                ]
            ]
        ],
        'official_gyh' => [
            'name' => '冠亚和',
            'switch' => 1,
            'items' => [
                'official_gyh_dxds' => [
                    'name' => '大小单双',
                    'odds' => 10,
                    'switch' => 1
                ],
                'official_gyh_h' => [
                    'name' => '和',
                    'odds' => 10,
                    'switch' => 1
                ]
            ]
        ],
        'official_lhd' => [
            'name' => '龙虎斗',
            'switch' => 1,
            'items' => [
                'official_lhd_gj' => [
                    'name' => '冠军',
                    'odds' => 10,
                    'switch' => 1
                ],
                'official_lhd_yj' => [
                    'name' => '亚军',
                    'odds' => 10,
                    'switch' => 1
                ],
                'official_lhd_jj' => [
                    'name' => '季军',
                    'odds' => 10,
                    'switch' => 1
                ]
            ]
        ]
    ];
    //$data = [
      //[1,2,3,4,5,6,7,8,9,10],
      //[1,2,3,4,5,6,7,8,9]
    //];
    print_r($this->abc(15));die;
    print_r(json_encode($data));
  }

  public function abc($code)
  {
    $arr = [];
    for($i=1;$i<11;$i++){
      $arr[] = $i;
    }

    $arr1 = [];
    for($i=3;$i<20;$i++){
      $arr1[] = $i;
    }

    $arr2 = $this->arrangement($arr,2);
    $zhushu = 0;
    foreach($arr1 as $value2){
      foreach($arr2 as $value){
        if(array_sum($value) == $value2){
          $zhushu ++;
        }
      }
    }
    return $zhushu;
  }

  /* 算出组合数，并列举出来
  * $a [array] 数列
  * $m [int] 组合个数
 */
 public function arrangement($a, $m) {
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
