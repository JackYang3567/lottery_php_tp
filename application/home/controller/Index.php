<?php
namespace app\home\controller;
use app\home\model\Betting;
use think\Controller;
use think\Db;
class Index extends Controller
{
    public function index()
    {       
        $a = Db::query('select * from (select * from work_caiji.code order by id desc) as a group by a.type');
        dump($a);
       $key = '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC4/mBubPe7Dp/Euz/VwpoWaE5ObazF/Vk7Q/RxGrYWqiICuRvu
IHj+kDXQ7rkp4C2mbbn6+9kHobHxQ8updrcuNGORbWW4bUmmTYEQzh3puFrLXdg8
Y96QMSdFnnW+KJqkKxBnymPKPAFxQVCMjiVrCsRct5Dl+jtPQiozGrxi2wIDAQAB
AoGAcl2wuBPdw2LzGui4OiqooBmz74CWQ4Cw3ZbRU+szjyd3Bz/xKHIi2x3EZ3pu
NplFH5LOW3+/WJx6KbHEAuxFqdkS1z3UP6f2/1Pm07kJ4HLlsYdwQQuL3lsGuyIp
UCeC3tShTUEL5z5AAfxevF6PZCLqb4w9HrAkepW2tuJge4ECQQDq/XUmmTzk4pB7
u4S9Fmqr55thGo7yEvnmiPWmd2Ef7HZFq0g3FjVT80f8QXzQRUWAqPLjhxdsy1PH
xvPZGSuxAkEAyYiVCAV9wW3jbhpRgfF5J6gO0hes0F1a9IdGLpuaeD7vCvoAGC+u
RJaJvPQ3QxIlk1DaLe7/eZPNt+V0bQl2SwJBANQYkHSWOvAbzmzfg59nbEBce1HZ
tsundQcu9wmZFoDJ3LZlMnkGAnwTSRXVxeH1pBXMZ+4VMH9xxdy7Jbz9iwECQQCD
Yx31+s5/moqZL2NQGgNojTIMWg76UMKJhN+GZz+PgUgKme4R1pQAdzwZCgY1HdGN
dzqmk5fOxUNqzpbWt0J9AkAPTtCZ10bvsJHO+vnxwfQO6OTkjmK4jW+epZkz3j1D
YzHXbdJglMcPiLIGIcI2gbJg1zG4uUMmZpQUA35gw8i3
-----END RSA PRIVATE KEY-----';
       $p_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC4/mBubPe7Dp/Euz/VwpoWaE5O
bazF/Vk7Q/RxGrYWqiICuRvuIHj+kDXQ7rkp4C2mbbn6+9kHobHxQ8updrcuNGOR
bWW4bUmmTYEQzh3puFrLXdg8Y96QMSdFnnW+KJqkKxBnymPKPAFxQVCMjiVrCsRc
t5Dl+jtPQiozGrxi2wIDAQAB
-----END PUBLIC KEY-----';

        $content = '您是傻逼';
//       openssl_private_encrypt($content,$encrypted,$key);
       openssl_public_encrypt($content,$encrypted,$p_key);
       dump($encrypted);
        $encrypted = base64_encode($encrypted);
       dump($encrypted);


//        openssl_public_decrypt(base64_decode($encrypted),$decrypted,$p_key);
        openssl_private_decrypt(base64_decode($encrypted),$decrypted,$key);
        dump($decrypted);

        echo 'error';
    }

    public function test(){
        $p_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC4/mBubPe7Dp/Euz/VwpoWaE5O
bazF/Vk7Q/RxGrYWqiICuRvuIHj+kDXQ7rkp4C2mbbn6+9kHobHxQ8updrcuNGOR
bWW4bUmmTYEQzh3puFrLXdg8Y96QMSdFnnW+KJqkKxBnymPKPAFxQVCMjiVrCsRc
t5Dl+jtPQiozGrxi2wIDAQAB
-----END PUBLIC KEY-----';
        $this->assign('pk',$p_key);
        return view();
    }
    public function jiemi(){
        $key = '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC4/mBubPe7Dp/Euz/VwpoWaE5ObazF/Vk7Q/RxGrYWqiICuRvu
IHj+kDXQ7rkp4C2mbbn6+9kHobHxQ8updrcuNGORbWW4bUmmTYEQzh3puFrLXdg8
Y96QMSdFnnW+KJqkKxBnymPKPAFxQVCMjiVrCsRct5Dl+jtPQiozGrxi2wIDAQAB
AoGAcl2wuBPdw2LzGui4OiqooBmz74CWQ4Cw3ZbRU+szjyd3Bz/xKHIi2x3EZ3pu
NplFH5LOW3+/WJx6KbHEAuxFqdkS1z3UP6f2/1Pm07kJ4HLlsYdwQQuL3lsGuyIp
UCeC3tShTUEL5z5AAfxevF6PZCLqb4w9HrAkepW2tuJge4ECQQDq/XUmmTzk4pB7
u4S9Fmqr55thGo7yEvnmiPWmd2Ef7HZFq0g3FjVT80f8QXzQRUWAqPLjhxdsy1PH
xvPZGSuxAkEAyYiVCAV9wW3jbhpRgfF5J6gO0hes0F1a9IdGLpuaeD7vCvoAGC+u
RJaJvPQ3QxIlk1DaLe7/eZPNt+V0bQl2SwJBANQYkHSWOvAbzmzfg59nbEBce1HZ
tsundQcu9wmZFoDJ3LZlMnkGAnwTSRXVxeH1pBXMZ+4VMH9xxdy7Jbz9iwECQQCD
Yx31+s5/moqZL2NQGgNojTIMWg76UMKJhN+GZz+PgUgKme4R1pQAdzwZCgY1HdGN
dzqmk5fOxUNqzpbWt0J9AkAPTtCZ10bvsJHO+vnxwfQO6OTkjmK4jW+epZkz3j1D
YzHXbdJglMcPiLIGIcI2gbJg1zG4uUMmZpQUA35gw8i3
-----END RSA PRIVATE KEY-----';
        $p_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC4/mBubPe7Dp/Euz/VwpoWaE5O
bazF/Vk7Q/RxGrYWqiICuRvuIHj+kDXQ7rkp4C2mbbn6+9kHobHxQ8updrcuNGOR
bWW4bUmmTYEQzh3puFrLXdg8Y96QMSdFnnW+KJqkKxBnymPKPAFxQVCMjiVrCsRc
t5Dl+jtPQiozGrxi2wIDAQAB
-----END PUBLIC KEY-----';

        $data = input('post.password');

//       openssl_private_encrypt('123456789',$encrypted,$key);
//        openssl_public_encrypt($content,$encrypted,$p_key);
//        dump($encrypted);
//        $encrypted = base64_encode($encrypted);
//        dump($encrypted);

//$data = 'kd1FxVAwiToAG8q2ev0ByhUC0ITtsilEfaJNsCdDEpjON0QD+rBRLu75D6Miy0i9AJBviOp/PwflSf4TSjn3VE3/uTByRXKvTIhco8P3U7sL+gUWLGu0TTNAz9kxKl408+Oik5xcdziU/qfhriRH/uhvzwZCoBakw0GID+CKRbI=';
//        openssl_public_decrypt(base64_decode($encrypted),$decrypted,$p_key);
        openssl_private_decrypt(base64_decode($data),$decrypted,$key);
        dump($decrypted);
    }



}
