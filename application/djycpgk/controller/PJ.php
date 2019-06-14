<?php
 /** 
 *　　　　　　　　┏┓　　　┏┓+ + 
 *　　　　　　　┏┛┻━━━┛┻┓ + + 
 *　　　　　　　┃　　　　　　　┃ 　 
 *　　　　　　　┃　　　━　　　┃ ++ + + + 
 *　　　　　　 ████━████ ┃+ 
 *　　　　　　　┃　　　　　　　┃ + 
 *　　　　　　　┃　　　┻　　　┃ 
 *　　　　　　　┃　　　　　　　┃ + + 
 *　　　　　　　┗━┓　　　┏━┛ 
 *　　　　　　　　　┃　　　┃　　　　　　　　　　　 
 *　　　　　　　　　┃　　　┃ + + + + 
 *　　　　　　　　　┃　　　┃　　　　Code is far away from bug with the animal protecting　　　　　　　 
 *　　　　　　　　　┃　　　┃ + 　　　　神兽保佑,代码无bug　　 
 *　　　　　　　　　┃　　　┃ 
 *　　　　　　　　　┃　　　┃　　+　　　　　　　　　 
 *　　　　　　　　　┃　 　　┗━━━┓ + + 
 *　　　　　　　　　┃ 　　　　　　　┣┓ 
 *　　　　　　　　　┃ 　　　　　　　┏┛ 
 *　　　　　　　　　┗┓┓┏━┳┓┏┛ + + + + 
 *　　　　　　　　　　┃┫┫　┃┫┫ 
 *　　　　　　　　　　┗┻┛　┗┻┛+ + + + 
 */ 
//                            _ooOoo_  
//                           o8888888o  
//                           88" . "88  
//                           (| -_- |)  
//                            O\ = /O  
//                        ____/`---'\____  
//                       .   ' \\| |// `.  
//                        / \\||| : |||// \  
//                       / _||||| -:- |||||- \  
//                         | | \\\ - /// | |  
//                        | \_| ''\---/'' | |  
//                        \ .-\__ `-` ___/-. /  
//                      ___`. .' /--.--\ `. . __ 
//                   ."" '< `.___\_<|>_/___.' >'"".  
//                  | | : `- \`.;`\ _ /`;.`/ - ` : | |  
//                  \ \ `-. \_ __\ /__ _/ .-` / /  
//          ======`-.____`-.___\_____/___.-`____.-'======  
//                              `=---='  
//                       佛祖保佑       永无BUG
//          .............................................  
//                  佛祖镇楼                  BUG辟易  
//                  佛曰:  
//                  写字楼里写字间，写字间里程序员；  
//                  程序人员写程序，又拿程序换酒钱。  
//                  酒醒只在网上坐，酒醉还来网下眠；  
//                  酒醉酒醒日复日，网上网下年复年。  
//                  但愿老死电脑间，不愿鞠躬老板前；  
//                  奔驰宝马贵者趣，公交自行程序员。  
//                  别人笑我忒疯癫，我笑自己命太贱；  
//                  不见满街漂亮妹，哪个归得程序员？
/********************************* */
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Request;

function pr($var)
{
    $template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
    printf($template, print_r($var, true));
}
/********************************* */


/********************************* */
class PJ extends  Controller
{
    //开奖数据
    public $post_data;
    public function index(){
        $tai = db::table('api_config')->field('id,name,switch,sort')->select();
        $this->assign('tai',$tai);
        return $this->fetch();
    }
	public function youxin($id){
		 $game = DB::table('api_game')->where('api_id',$id)->select();
		 
		  $this->assign('tai',$game);
		 return $this->fetch();
	}
    public function cuai(){
        $data = Request::post('switch');
        $k = substr($data,0,1);//1
        $s = substr($data,2,1);//id

        if ($k == '1') {
            $switch = 0;
            $kio = ['switch'=>$switch];
            $rs = DB::table('api_config')->where('id',$s)->update($kio);
            if ($rs) {
                return json(['error' => 0, '修改成功']);
            } else {
                return json(['error' => 1, '修改失败']);
            }
        }else{
            $switch = 1;
            $kio = ['switch'=>$switch];
            $rs = DB::table('api_config')->where('id',$s)->update($kio);
            if ($rs) {
                return json(['error' => 0, '修改成功']);
            } else {
                return json(['error' => 1, '修改失败']);
            }
        }
    }
    public function cuais(){
        $data = Request::post('switch');
        $k = substr($data,0,1);//1
        $s = substr($data,2,1);//id

        if ($k == '1') {
            $switch = 0;
            $kio = ['switch'=>$switch];
            $rs = DB::table('api_game')->where('id',$s)->update($kio);
            if ($rs) {
                return json(['error' => 0, '修改成功']);
            } else {
                return json(['error' => 1, '修改失败']);
            }
        }else{
            $switch = 1;
            $kio = ['switch'=>$switch];
            $rs = DB::table('api_game')->where('id',$s)->update($kio);
            if ($rs) {
                return json(['error' => 0, '修改成功']);
            } else {
                return json(['error' => 1, '修改失败']);
            }
        }
    }
	public function yx_edit($id){
		if (Request::method() == 'GET') {
            $game = DB::table('api_game')->where('id',$id)->find();
		 
		  $this->assign('data',$game);
		 return $this->fetch();
        }else{
            $data = Request::post();
			
            if ($data) {
                $rs = db::table('api_game')->update($data);
                if ($rs) {
                    $this->success('修改成功', url('djycpgk/PJ/index'));
                } else {
                    $this->error('修改失败');
                }
            }
        }
		 
	}
    public function edit(){
        if (Request::method() == 'GET') {
            $data = DB::table('api_config')->where('id=' . Request::param('id'))->find();
           
            $this->assign('data', $data);
			
            return $this->fetch();
        }else{
            $data = Request::post();
            if ($data) {
                $rs = db::table('api_config')->update($data);
                if ($rs) {
                    $this->success('修改成功', url('djycpgk/PJ/index'));
                } else {
                    $this->error('修改失败');
                }
            }
        }
    }
    public function add() 
    {

        if (Request::method() == 'GET') {
            return $this->fetch();

        } else {
            $data = Request::post();
            $rs = DB::name('api_config')->insert($data);
            if($rs){
                $this->success('添加成功', url('djycpgk/PJ/index'));
            }else{
                $this->error('数据异常');
            }

        }
    }
    public function delete(){//删除

        $rs = DB::table('api_config')->where('id', 'in', Request::post('id/a'))->delete();
        if ($rs) {
            return json(['error' => 0, '删除成功']);
        } else {
            return json(['error' => 1, '删除失败']);
        }
    }
    
} 