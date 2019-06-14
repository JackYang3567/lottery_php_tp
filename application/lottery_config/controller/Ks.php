<?php
namespace app\lottery_config\controller;

class Ks
{
  public function index(){
    $data = [
        'xt' => [
        'name' => '形态',
        'number' => 0,
        'switch' => 1,
        'items' => [
          'bz' => [
            'name' => '豹子',
            'odds' => 20,
            'switch' => 1
          ],
          'sz' => [
            'name' => '顺子',
            'odds' => 6.9,
            'switch' => 1
          ],
          'dz' => [
            'name' => '对子',
            'odds' => 1.75,
            'switch' => 1
          ],
          'bs' => [
            'name' => '半顺',
            'odds' => 2.8,
            'switch' => 1
          ],
          'zl' => [
            'name' => '杂6',
            'odds' => 10,
            'switch' => 1
          ],
        ]
      ],
      'zh' => [
        'name' => '总和',
        'number' => 0,
        'switch' => 1,
        'items' => [
          'code_3' => [
            'name' => '3',
            'odds' => 110,
            'switch' => 1
          ],
          'code_4' => [
            'name' => '4',
            'odds' => 48,
            'switch' => 1
          ],
          'code_5' => [
            'name' => '5',
            'odds' => 23,
            'switch' => 1
          ],
          'code_6' => [
            'name' => '6',
            'odds' => 15,
            'switch' => 1
          ],
          'code_7' => [
            'name' => '7',
            'odds' => 12,
            'switch' => 1
          ],
          'code_8' => [
            'name' => '8',
            'odds' => 9,
            'switch' => 1
          ],
          'code_9' => [
            'name' => '9',
            'odds' => 7.5,
            'switch' => 1
          ],
          'code_10' => [
            'name' => '10',
            'odds' => 7.5,
            'switch' => 1
          ],
          'code_11' => [
            'name' => '11',
            'odds' => 7.5,
            'switch' => 1
          ],
          'code_12' => [
            'name' => '12',
            'odds' => 7.5,
            'switch' => 1
          ],
          'code_13' => [
            'name' => '13',
            'odds' => 9,
            'switch' => 1
          ],
          'code_14' => [
            'name' => '14',
            'odds' => 12,
            'switch' => 1
          ],
          'code_15' => [
            'name' => '15',
            'odds' => 9.7,
            'switch' => 1
          ],
           'code_16' => [
            'name' => '16',
            'odds' => 27,
            'switch' => 1
          ],
          'code_17' => [
            'name' => '17',
            'odds' => 50,
            'switch' => 1
          ],
           'code_18' => [
            'name' => '18',
            'odds' => 110,
            'switch' => 1
          ],
          'da' => [
            'name' => '大',
            'odds' => 1.5,
            'switch' => 1
          ],
          'x' => [
            'name' => '小',
            'odds' => 1.5,
            'switch' => 1
          ],
          'dan' => [
            'name' => '单',
            'odds' => 1.5,
            'switch' => 1
          ],
          's' => [
            'name' => '双',
            'odds' => 1.5,
            'switch' => 1
          ]
        ]
      ],

    ];
    print_r(json_encode($data));
  }
}
