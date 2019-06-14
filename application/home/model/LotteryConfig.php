<?php
namespace app\home\model;

use think\Model;

class LotteryConfig extends Model
{
  protected $pk = 'type';

  /**
   * 保存字符串配置
   * @param string $config 要保存的配置，可以是任意字符串
   * @return boolean
   */
	public function saveConfig(string $config)
	{
		$this->basic_config = $config;
		return $this->save();
  }

  /**
   * 保存数组配置
   * @param array $config 要保存的配置，必须是数组
   * @return boolean
   */
  public function setConfig(array $config)
  {
		$this->basic_config = json_encode($config);
		return $this->save();
  }

  /**
   * 获取配置
   * @return array
   */
  public function getConfig()
  {
    return json_decode($this->basic_config, true);
  }
}
