<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

// use shopack\base\common\base\BaseEnum;

class enuColumnInfo //extends BaseEnum
{
	const type				= 'type';
	const validator		= 'validator';
	const default			= 'default';
	const required		= 'required';
	const selectable	= 'selectable';
	const beFilter		= 'beFilter';			//remove column from result in backend
	const virtual			= 'virtual';
	const adhoc				= 'adhoc';				//just used for export column to client
	const search			= 'search';
	const isStatus		= 'isStatus';
	const jsonSchema	= 'jsonSchema';
};
