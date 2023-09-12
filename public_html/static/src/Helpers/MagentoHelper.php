<?php /** @noinspection PhpUndefinedClassInspection */


namespace Disual\StaticProducts\Helpers;

use Mage;

class MagentoHelper {


  public static function getStoreCodeByLang(string $languageCode = '') {

    $storeCollection = Mage::getModel('core/store')->getCollection();
    foreach ($storeCollection as $store) {
      $localeCode = Mage::getStoreConfig('general/locale/code', $store->getId());
      $localeParts = explode('_', $localeCode);
      if ($localeParts[0] === $languageCode) {
        return $store->getCode();
      }
    }
    return 1;
  }

  public static function getAllAttributesNames(string $lang = '') {

    $cachePath = CacheHelper::getCachePath("/products/attributes");
    $cacheFile =  $cachePath."/all_frontend_attributes_".$lang.".json";
    if (file_exists($cacheFile)) {
      unlink($cacheFile);
      //return json_decode(file_get_contents($cacheFile), true);
    }


    $storeCode = self::getStoreCodeByLang($lang);
    Mage::app()->setCurrentStore($storeCode);
    //\Mage::getSingleton('core/locale')->setLocaleCode($localeCode); // it_IT

    $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
      ->addFieldToFilter('is_visible_on_front', 1);

    $attributeNames = [];
    foreach ($attributes as $attribute) {
      $attributeCode = $attribute->getAttributeCode();
      $attributeLabel = $attribute->getStoreLabel();
      $attributeType = $attribute->getFrontendInput();
      $attributeNames[$attributeCode] = ['label' => $attributeLabel, 'type' => $attributeType];
    }
    file_put_contents($cacheFile, json_encode($attributeNames));
    return $attributeNames;
  }

  public static function getAttributeNameByLang(string $attrCode, string $lang = '') {
    $allAttributes = self::getAllAttributesNames($lang);
    if (isset($allAttributes[$attrCode])) {
      return $allAttributes[$attrCode];
    }
    return null;
  }

}
