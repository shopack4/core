<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUserEducationLevel extends BaseEnum
{
  const Student						= 'T'; //دانش آموز
  const UnderDiploma			= 'U'; //زیر دیپلم
  const Diploma						= 'D'; //دیپلم
  const UniversityStudent	= 'V'; //دانشجو  ?? Collegian
  const Associate					= 'A'; //فوق دیپلم
  const Bachelor					= 'B'; //لیسانس
  const Master						= 'S'; //فوق لیسانس
  const PhD								= 'P'; //دکترای علمی
  const PostDoctoral			= 'O'; //فوق دکترای علمی
  const MD								= 'M'; //پزشک

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Student						=> 'Student', //دانش آموز
		self::UnderDiploma			=> 'Under Diploma', //زیر دیپلم
		self::Diploma						=> 'Diploma', //دیپلم
		self::UniversityStudent	=> 'University Student', //دانشجو
		self::Associate					=> 'Associate', //فوق دیپلم
		self::Bachelor					=> 'Bachelor', //لیسانس
		self::Master						=> 'Master', //فوق لیسانس
		self::PhD								=> 'PhD', //دکترای علمی
		self::PostDoctoral			=> 'Post Doctoral', //فوق دکترای علمی
		self::MD								=> 'MD', //پزشک
	];

};
