<?php
namespace app\lottery_config\controller;

class Game
{
  public function index(){

    // 百家乐
    $data = [
      'odds' => [
        'xian' => [
          'name' => '闲',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'zhuang' => [
          'name' => '庄',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'xd' => [
          'name' => '闲对',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'wmdz' => [
          'name' => '完美对子',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'rydz' => [
          'name' => '任意对子',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'zd' => [
          'name' => '庄对',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'xiao' => [
          'name' => '小',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'he' => [
          'name' => '和',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'da' => [
          'name' => '大',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ]
      ],
      'room' => [
          'room1' => [
            'name' => '房间一',
            'min' => 10,
            'max' => 100
          ],
          'room2' => [
            'name' => '房间二',
            'min' => 100,
            'max' => 1000
          ]
      ]
    ];

    // 龙虎斗
    $data1 = [
      'odds' => [
        'l' => [
          'name' => '龙',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'h' => [
          'name' => '虎',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'ld' => [
          'name' => '龙单',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'ls' => [
          'name' => '龙双',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'lhei' => [
          'name' => '龙黑',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'lhong' => [
          'name' => '龙红',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'hs' => [
          'name' => '虎双',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'hd' => [
          'name' => '虎单',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'hhei' => [
          'name' => '虎黑',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'hhong' => [
          'name' => '虎红',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ],
        'he' => [
          'name' => '和',
          'num' => [
            'room1' => 1,
            'room2' => 2
          ]
        ]
      ],
      'room' => [
          'room1' => [
            'name' => '房间一',
            'min' => 10,
            'max' => 100
          ],
          'room2' => [
            'name' => '房间二',
            'min' => 100,
            'max' => 1000
          ]
      ]
    ];
    print_r(json_encode($data1));
  }
}
