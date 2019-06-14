<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/30
 * Time: 9:34
 */
namespace app\djycpgk\controller;
use think\facade\Request;
use think\Db;

class ForControl extends Rbac
{
    //设置单控
    public function set_dankong()
    {
//        dump(Request::param());die;
        $data = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
        if(Request::param('type_id'))
        {
            $where['lottery_control.type'] = Request::param('type_id');
        }
        else
        {
            $where = [];
        }
        //加入对应彩种的单控人员
        $dankong = Db::table('lottery_control')
            ->join('user','user.id=lottery_control.uid')
            ->join('lottery_config','lottery_config.type=lottery_control.type')
            ->where($where)
            ->field('lottery_control.*,user.username,lottery_config.name')
            ->paginate(25);
        $sicai = [5, 7, 6, 8, 9, 11, 36, 37, 38, 9, 40, 41, 42, 44, 45, 51,52,57,58];
        $lottery_config = Db::table('lottery_config')->where('type','in',$sicai)->field('type,name')->select();
        $this->assign('lottery_array', $lottery_config);
        $this->assign('dankong',$dankong);
        $this->assign('cate',Request::param('type_cate'));
        $this->assign('name',Request::param('type_id') ? $data['name'] : "");
        $this->assign('type', Request::param('type_id'));
        return view();
    }

    //添加被单控的会员页面
    public function addControlPage()
    {
        if(!Request::param('type_id') || !Request::param('type_cate'))
        {
            $this->assign('canShuCuoWu',1);
        }

        $sicai = [5, 7, 6, 8, 9, 11, 36, 37, 38, 9, 40, 41, 42, 44, 45, 51,52,57,58];
        if(!in_array(Request::param('type_id'),$sicai))
        {
            $this->assign('canControl',1);
        }
        //获取所有被单控的会员的id
        $this->assign('name', Request::param('type_cate'));
        $this->assign('type_id', Request::param('type_id'));
        return view();
    }

    //专门获取可加入单控的用户
    public function canJoinDanKong()
    {
        $userIdArr = Db::table('lottery_control')->where('type',Request::param('type_id'))->column('uid');
        //获取所有可以加入单控的会员信息
        if(Request::param('keyword'))
        {
            $key = Request::param('keyword');
            $user = Db::table('user')->where('id','not in',$userIdArr)->where('type',0)->where('username','like',"%".$key."%")->field('username,id,username as onlyname')->paginate(12,true,[
                'query'=>Request::param()
            ])->each(function ($v) use ($key){
                $v['username'] = preg_replace('/'.$key.'/',"<span style='color: red;'>".$key."</span>",$v['username']);
                return $v;
            });
            $this->assign('keyword',$key);
        }
        else
        {
            $user = Db::table('user')->where('id','not in',$userIdArr)->where('type',0)->field('username,id,username as onlyname')->paginate(12,true);
        }
        $this->assign('user',$user);
        $this->assign('type_id',Request::param('type_id'));
        return view();
    }
    //写入数据库
    public function addAction()
    {
        if(Request::isPost())
        {
            $post = Request::param();
            if(!$post['uid'])
            {
                return '请选择需要加入单控的会员！';
            }
            $sicai = [5, 7, 6, 8, 9, 11, 36, 37, 38, 9, 40, 41, 42, 44, 45, 51,52,57,58];
            if(!in_array($post['type_id'],$sicai))
            {
                return '抱歉，您选中的彩票目前不支持设置单控！';
            }

            $uidArr = explode(',',$post['uid']);
            $insert = [];
            foreach ($uidArr as $k=>$u)
            {
                $insert[$k]['type'] = $post['type_id'];
                $insert[$k]['uid'] = $u;
                $insert[$k]['create_time'] = time();
                $insert[$k]['money'] = $post['money'] ?? 0;
            }

            $add = Db::table('lottery_control')->insertAll($insert);
            if($add)
            {
                return 1;
            }
            else
            {
                return '系统繁忙，请稍后重试！';
            }
        }
        else
        {
            return '未知错误，请稍后重试！';
        }
    }

    //更改用户的状态
    public function changeStatus()
    {
        if(Request::isPost())
        {
            $statu = Request::param('status');
            $id = Request::param('id');
            if($statu)
            {
                $update = ['status'=>1];
            }
            else
            {
                $update = ['status'=>0];
            }
            $change = Db::table('lottery_control')->where('id',$id)->update($update);
            if($change)
            {
                return 1;
            }
            else
            {
                return 2;
            }
        }
        else
        {
            return 2;
        }
    }
    //更改用户的单控阈值
    public function editMoney()
    {
        if(Request::isPost())
        {
            $id = Request::param('id');
            $money = Request::param('money');
            if(is_numeric($money) && $money >= 0)
            {
                $change = Db::table('lottery_control')->where('id',$id)->update([
                    'money'=>$money
                ]);
                if($change)
                {
                    return 1;
                }
                else
                {
                    return '系统繁忙，请稍后重试！';
                }
            }
            else
            {
                return '单控阈值是一个大于等于0的数字！';
            }
        }
        else
        {
            return '系统繁忙，请稍后重试！';
        }
    }

    //删除用户数据
    public function delthiscontrol()
    {
        if(Request::isPost())
        {
            $id = Request::param('id');
            $del = Db::table('lottery_control')->where('id',$id)->delete();
            if($del)
            {
                return 1;
            }
            else
            {
                return '系统繁忙，请稍后重试！';
            }
        }
        else
        {
            return '系统繁忙，请稍后重试！';
        }
    }
}