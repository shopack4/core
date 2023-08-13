<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\models;

trait UploadedFilesTrait
{

	public function getUploadedFilesData()
	{
		if (empty($_FILES))
			return [];

    $files = [];

		foreach ($_FILES as $imageSetKey => $imageSet) {
			if (is_array($imageSet['name'])) {
				foreach ($imageSet['name'] as $fieldName => $name) {
					$full_path = $imageSet['full_path'][$fieldName];
					$type      = $imageSet['type'][$fieldName];
					$tmp_name  = $imageSet['tmp_name'][$fieldName];
					$error     = $imageSet['error'][$fieldName];
					$size      = $imageSet['size'][$fieldName];

					$files[$fieldName] = [
						'tempFileName' => $tmp_name,
						'fileName' => $name,
					];
				}
			} else {
				$name      = $imageSet['name'];
				$full_path = $imageSet['full_path'];
				$type      = $imageSet['type'];
				$tmp_name  = $imageSet['tmp_name'];
				$error     = $imageSet['error'];
				$size      = $imageSet['size'];

				$files[$imageSetKey] = [
					'tempFileName' => $tmp_name,
					'fileName' => $name,
				];
			}
		}

		return $files;
	}

}
