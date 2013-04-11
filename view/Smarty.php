<?php
/**
 * Smarty
 *
 * @package ko
 * @subpackage view
 * @author zhangchu
 */

if (!class_exists('Smarty'))
{
	require_once(KO_DIR.'vendor/Smarty-3.1.4/libs/Smarty.class.php');
}

/**
 * Smarty操作封装接口
 */
interface IKo_View_Smarty
{
	/**
	 * 单行文本 input编辑/显示 或 多行文本 textarea编辑
	 */
	public function vAssignHtml($vTplVar, $vValue=null, $aExclude=array());
	/**
	 * 单行文本 简单的作为JS变量
	 */
	public function vAssignSlashes($vTplVar, $sValue=null, $aExclude=array());
	/**
	 * 单行文本 作为JS变量，并最终输出到页面显示
	 */
	public function vAssignSlashesHtml($vTplVar, $sValue=null, $aExclude=array());
	/**
	 * 多行文本 显示
	 */
	public function vAssignMultiline($vTplVar, $sValue=null, $aExclude=array());
	/**
	 * JSON，不支持批量设置
	 */
	public function vAssignJson($sName, $vValue);
	/**
	 * 将HTML作为普通文本设置到编辑器中，不支持批量设置 或 html文本 编辑器编辑
	 */
	public function vAssignEditor($sName, $sValue, $sTextType='html');
	/**
	 * assign raw，不推荐使用 或 html文本 显示
	 */
	public function vAssignRaw($vTplVar, $vValue=null);
	public function vClearAssign($vVar);
	public function vClearAllAssign();

	/**
	 * 输出模板
	 */
	public function vDisplay($sFilePath, $sCacheId = null);
	/**
	 * 返回模板替换后内容
	 *
	 * @return string
	 */
	public function sFetch($sFilePath, $sCacheId = null);
	/**
	 * @return boolean
	 */
	public function bIsCached($sFilePath, $sCacheId = null);
	public function vClearCache($sFilePath, $sCacheId = null);
	public function vClearAllCache();

	public function vSetCaching($iCaching);
	public function vSetCachingType($sType);
	public function vSetCacheLifeTime($iLifeTime);

	/**
	 * 注册模版自动分析处理类
	 */
	public function vRegAutoInfoClass($sName, $sClass);
	/**
	 * 设置模版自动分析函数的参数
	 */
	public function vRegAutoInfoFunc($sName, $sFunc, $aPara);
}

/**
 * Smarty操作封装实现
 */
class Ko_View_Smarty implements IKo_View_Smarty
{
	private $_oSmarty;
	private $_aAutoInfo = array();

	public function __construct($aParam = array())
	{
		$this->_oSmarty = new Smarty();
		$this->_oSmarty->setTemplateDir(KO_TEMPLATE_DIR);
		$this->_oSmarty->setCompileDir(KO_TEMPLATE_C_DIR);
		foreach ($aParam as $key => $val)
		{
			$this->_oSmarty->$key = $val;
		}
	}

	public function vAssignHtml($vTplVar, $vValue=null, $aExclude=array())
	{
		$this->_vAssignEscape($vTplVar, $vValue, array('Ko_View_Escape', 'VEscapeHtml'), $aExclude);
	}

	public function vAssignSlashes($vTplVar, $vValue=null, $aExclude=array())
	{
		$this->_vAssignEscape($vTplVar, $vValue, array('Ko_View_Escape', 'VEscapeSlashes'), $aExclude);
	}

	public function vAssignSlashesHtml($vTplVar, $vValue=null, $aExclude=array())
	{
		$this->_vAssignEscape($vTplVar, $vValue, array('Ko_View_Escape', 'VEscapeSlashesHtml'), $aExclude);
	}

	public function vAssignMultiline($vTplVar, $vValue=null, $aExclude=array())
	{
		$this->_vAssignEscape($vTplVar, $vValue, array('Ko_View_Escape', 'VEscapeMultiline'), $aExclude);
	}

	public function vAssignJson($sName, $vValue)
	{
		$this->_oSmarty->assign($sName, Ko_View_Escape::SEscapeJson($vValue));
	}

	public function vAssignEditor($sName, $sValue, $sTextType='html')
	{
		$this->_oSmarty->assign($sName, Ko_View_Escape::SEscapeEditor($sValue, $sTextType));
	}

	public function vAssignRaw($vTplVar, $vValue=null)
	{
		$this->_oSmarty->assign($vTplVar, $vValue);
	}

	public function vClearAssign($vVar)
	{
		$this->_oSmarty->clearAssign($vVar);
	}
	
	public function vClearAllAssign()
	{
		$this->_oSmarty->clearAllAssign();
	}

	public function vDisplay($sFilePath, $sCacheId = null)
	{
		echo $this->sFetch($sFilePath, $sCacheId);
	}

	/**
	 * @return string
	 */
	public function sFetch($sFilePath, $sCacheId = null)
	{
		try
		{
			$this->_vPreAutoFetch($sFilePath);
			$sRet = $this->_oSmarty->fetch($sFilePath, $sCacheId);
		}
		catch(Exception $e)
		{
			$sRet = $e->getMessage();
		}
		return $sRet;
	}

	/**
	 * @return boolean
	 */
	public function bIsCached($sFilePath, $sCacheId = null)
	{
		return $this->_oSmarty->isCached($sFilePath, $sCacheId);
	}
	
	public function vClearCache($sFilePath, $sCacheId = null)
	{
		$this->_oSmarty->clearCache($sFilePath, $sCacheId);
	}
	
	public function vClearAllCache()
	{
		$this->_oSmarty->clearAllCache();
	}
	
	public function vSetCaching($iCaching)
	{
		$this->_oSmarty->caching = $iCaching;
	}
	
	public function vSetCachingType($sType)
	{
		$this->_oSmarty->caching_type = $sType;
	}
	
	public function vSetCacheLifeTime($iLifeTime)
	{
		$this->_oSmarty->cache_lifetime = $iLifeTime;
	}
	
	public function vRegAutoInfoClass($sName, $sClass)
	{
		$this->_aAutoInfo[$sName]['class'] = $sClass;
	}

	public function vRegAutoInfoFunc($sName, $sFunc, $aPara)
	{
		$this->_aAutoInfo[$sName]['func'][$sFunc] = $aPara;
	}

	private function _vPreAutoFetch($sFilePath)
	{
		if (empty($this->_aAutoInfo))
		{
			return;
		}
		
		$aArr = $this->_aGetAutoArr($sFilePath);
		foreach ($aArr as $k => $v)
		{
			if ('L' === substr($k, -1))
			{
				continue;
			}
			list($sRegName, $sFuncName, $aViewPara) = Ko_View_Str::AParseAutoStr($k);
			if (!class_exists($this->_aAutoInfo[$sRegName]['class']))
			{
				continue;
			}
			$viewobj = Ko_Tool_Singleton::OInstance($this->_aAutoInfo[$sRegName]['class']);
			$funcname = 'vAuto_'.$sFuncName;
			if (!method_exists($viewobj, $funcname))
			{
				continue;
			}
			$realk = Ko_View_Str::SAssembleAutoStr($sRegName, $sFuncName, $aViewPara);
			$aRegPara = isset($this->_aAutoInfo[$sRegName]['func'][$sFuncName]) ? $this->_aAutoInfo[$sRegName]['func'][$sFuncName] : null;
			$viewobj->$funcname($this, $k, isset($aArr[$realk]) ? $aArr[$realk] : array(), $aRegPara, $aViewPara);
		}
	}
	
	private function _aGetAutoArr($sFilePath)
	{
		$templateDir = $this->_sGetTemplateDir();
		$sFullname = Ko_View_Str::SGetAbsoluteFile($sFilePath, $templateDir);
		if (!is_file($sFullname))
		{
			return array();
		}
		$stime = filemtime($sFullname);

		$sHashFile = $this->_oSmarty->getCompileDir().'/'.KO_VIEW_AUTOTAG.'_'.md5($sFullname);
		$htime = is_file($sHashFile) ? filemtime($sHashFile) : 0;
		if ($htime >= $stime)
		{
			$arr = Ko_Tool_Enc::ADecode(file_get_contents($sHashFile));
			if (false !== $arr)
			{
				$rebuild = false;
				foreach ($arr[0] as $file)
				{
					$itime = is_file($file) ? filemtime($file) : time();
					if ($htime < $itime)
					{
						$rebuild = true;
						break;
					}
				}
				if (!$rebuild)
				{
					return $arr[1];
				}
			}
		}

		$aFilelist = array();
		$sContent = file_get_contents($sFullname);
		$str = new Ko_View_Str($sContent);
		$aArr = $str->aParseArr($this->_oSmarty->left_delimiter, $this->_oSmarty->right_delimiter, $templateDir, $aFilelist);
		file_put_contents($sHashFile, Ko_Tool_Enc::SEncode(array($aFilelist, $aArr)));
		return $aArr;
	}

	private function _vAssignEscape($vTplVar, $vValue, $fnEscape, $aExclude)
	{
		if (is_array($vTplVar))
		{
			$vTplVar = call_user_func($fnEscape, $vTplVar, $aExclude);
		}
		else
		{
			$vValue = call_user_func($fnEscape, $vValue, $aExclude);
		}
		$this->_oSmarty->assign($vTplVar, $vValue);
	}
	
	private function _sGetTemplateDir()
	{
		$dirs = $this->_oSmarty->getTemplateDir();
		return is_array($dirs) ? $dirs[0] : $dirs;
	}
}
