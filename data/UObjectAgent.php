<?php
/**
 * UobjectAgent
 *
 * @package ko
 * @subpackage data
 * @author zhangchu
 */

/**
 * 封装创建 UObject 的接口
 */
interface IKo_Data_UObjectAgent
{
	public static function OInstance($sKind, $sSplitField, $sKeyField, $sUoName='');
}

/**
 * 封装创建 UObject 的实现
 */
class Ko_Data_UObjectAgent implements IKo_Data_UObjectAgent
{
	public static function OInstance($sKind, $sSplitField, $sKeyField, $sUoName='')
	{
		switch (KO_DB_ENGINE)
		{
		case 'kproxy':
			return Ko_Data_UObjectMan::OInstance($sKind, $sSplitField, $sKeyField, $sUoName);
		case 'mysql':
			return Ko_Data_UObjectMysql::OInstance($sKind, $sSplitField, $sKeyField, $sUoName);
		default:
			assert(0);
		}
	}
}

?>